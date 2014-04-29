<?php
/*
Plugin Name: ACF Forms
Plugin URI: https://github.com/tamagokun/acf-forms
Description: Create forms using Advanced Custom Fields.
Version: 0.1
Author: Mike Kruk
Author URI: http://ripeworks.com/
*/

if ( ! defined( 'WPINC' ) ) die;

if (!class_exists('WP_List_Table')) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

require_once __DIR__ . '/lib/ACFFormsEntryTable.php';
require_once __DIR__ . '/lib/ACFFormsFormMetaBox.php';

class ACFForms
{
    public $post_type = 'acf-form';
    public $entry_post_type = 'acf-form-entry';
    public $shortcode = 'acf-form';

    public function __construct()
    {
        add_action('init', array($this, 'init'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_head', array($this, 'head'));
        add_action('admin_menu', array($this, 'menus'));

        add_filter('acf/pre_save_post', array($this, 'save_form_entry'));
        add_filter('post_row_actions', array($this, 'form_row_actions'), 10, 1);

        add_shortcode($this->shortcode, array($this, 'shortcode_form'));
    }

    public function init()
    {
        // register custom post type to store forms
        register_post_type($this->post_type, array(
            'label' => 'Forms',
            'labels' => $this->generate_labels("Form", "Forms"),
            'description' => '',
            'public' => false,
            'exclude_from_search' => true,
            'publicly_queryable' => false,
            'show_ui' => true,
            'show_in_admin_bar' => false,
            'supports' => array('title'),
            'has_archive' => false,
            'menu_position' => 25,
            'menu_icon' => 'dashicons-clipboard'
        ));

        register_post_type($this->entry_post_type, array(
            'label' => 'Entries',
            'labels' => $this->generate_labels("Entry", "Entries"),
            'description' => '',
            'public' => false,
            'exclude_from_search' => true,
            'publicly_queryable' => false,
            'show_ui' => false,
            'show_in_admin_bar' => false,
            'supports' => array('title'),
            'has_archive' => false
        ));

        $metabox = new ACFFormsFormMetaBox();
    }

    public function head()
    {
        remove_meta_box('submitdiv', $this->post_type, 'side');
        add_meta_box('submitdiv', __('Actions'), array($this, 'publish_meta_box'), $this->post_type, 'side');
    }

    public function menus()
    {
        add_submenu_page(
            null,
            "Entries",
            "Entries",
            "edit_plugins",
            "acf-forms-view-entries",
            function() {
                $form = get_post($_GET['form_id']);
                include_once(__DIR__ . '/views/entries_list.php');
            }
        );
    }

    public function enqueue_scripts()
    {
        wp_enqueue_style('acf-forms-css', plugin_dir_url( __FILE__ ) . '/acf-forms.css', false);
    }

    public function form_row_actions($actions)
    {
        if (get_post_type() !== $this->post_type) return $actions;

        global $post;
        unset($actions['inline hide-if-no-js']); // Remove "Quick Edit"
        $pre = array_splice($actions, 0, 1);

        return array_merge($pre, array(
            'submissions' => '<a href="' . $this->entries_url($post) . '">View Submissions</a>'
        ), $actions);
    }

    public function publish_meta_box($post)
    {
        global $action;

        $post_type = $post->post_type;
        $post_type_object = get_post_type_object($post_type);
        $can_publish = current_user_can($post_type_object->cap->publish_posts);
        $entries_url = $this->entries_url($post);

        return include __DIR__.'/views/publish_meta_box.php';
    }

    public function save_form_entry($post_id)
    {
        if (strpos($post_id, $this->entry_post_type . '_for_form_') === false) return $post_id;

        list($post_type, $post_parent) = explode('_for_form_', $post_id, 2);

		$post = array(
			'post_status' => 'publish',
			'post_title' => reset($_POST['fields']),
            'post_type' => $this->entry_post_type,
            'post_parent' => $post_parent
		);
		$post_id = wp_insert_post( $post );

        if ($post_id > 0) {

            $form = get_post($post_parent);
            if (!$form) return $post_id;

            // handle notifications
            $this->handle_notification($form, get_post($post_id));
        }

		return $post_id;
    }

    public function shortcode_form($atts)
    {
        $form = false;

        if (isset($atts['form'])) {
            $form = get_page_by_path($atts['form'], OBJECT, $this->post_type);
        }

        if (isset($atts['id'])) {
            $form = get_post($atts['id']);
        }

        if (!$form) return;

        $options = array(
            'post_id' => $this->entry_post_type . '_for_form_' . $form->ID,
            'post_title' => false,
            'post_content' => false,
            'field_groups' => array(get_post_meta($form->ID, 'form_field_group', true)),
            'submit_value' => $this->value_or_default(get_field('submit_value', $form->ID), __('Submit')),
            // ACF 5
            /* 'label_placement' => $this->value_or_default(get_field('label_placement', $form->ID), 'top'), */
            /* 'instruction_placement' => $this->value_or_default(get_field('instruction_placement', $form->ID), 'label'), */
            /* 'field_el' => $this->value_or_default(get_field('field_el', $form->ID), 'div') */
        );

        if ($header = get_field('form_header', $form->ID)) {
            $options['html_before_fields'] = $header;
        }

        if ($footer = get_field('form_footer', $form->ID)) {
            $options['html_after_fields'] = $footer;
        }

        $success_action = get_field('success_action', $form->ID);
        if ($success_action == 'message') {
            $msg = get_field('success_message', $form->ID);
            if ($msg) $options['updated_message'] = $msg;
        } elseif ($success_action = 'redirect') {
            $return = get_field('redirect', $form->ID);
            if ($return) $options['return'] = $return;
        }

        ob_start();
        acf_form($options);

        return ob_get_clean();
    }

    public function handle_notification($form, $entry)
    {
        $recipient = get_field('notification_recipient', $form->ID);
        if (!$recipient) return;

        $headers = array();

        $from = get_field('from_address', $form->ID);
        if ($from) $headers[] = "From: $from";

        $content_type = function() { return 'text/html'; };
        add_filter('wp_mail_content_type', $content_type);
        wp_mail(
            array($recipient),
            $this->replace_field_shortcodes(get_field('notification_subject', $form->ID), $entry),
            $this->replace_field_shortcodes(get_field('notification_body', $form->ID), $entry),
            implode('\r\n', $headers)
        );
        remove_filter('wp_mail_content_type', $content_type);
    }

// private
    protected function entries_url($form)
    {
        return "edit.php?page=acf-forms-view-entries&post_type=" . $this->post_type . "&form_id=" . $form->ID;
    }

    protected function replace_field_shortcodes($string, $entry)
    {
        return $string;

        // TODO: How can we get the fields for the entry?
        $fields = get_fields($entry->ID);

        $m = array();
        preg_match_all('/\[(\w+)\]/', $string, $m);
    }

    protected function generate_labels($singular, $plural)
    {
        return array(
            'name'               => __($plural),
            'singular_name'      => __($singular),
            'add_new'            => __('Add New'),
            'add_new_item'       => __('Add New ' . $singular),
            'edit_item'          => __('Edit ' . $singular),
            'new_item'           => __('New ' . $singular),
            'view_item'          => __('View ' . $singular),
            'search_items'       => __('Search ' . $plural),
            'not_found'          => __('No ' . $plural . ' found'),
            'not_found_in_trash' => __('No ' . $plural . ' found in Trash')
        );
    }

    protected function value_or_default($value, $default)
    {
        return $value ? $value : $default;
    }
}

$instance = new ACFForms();
