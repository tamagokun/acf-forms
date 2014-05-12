<?php
/*
Plugin Name: ACF Forms
Plugin URI: https://github.com/tamagokun/acf-forms
Description: Create forms using Advanced Custom Fields.
Version: 0.2.1
Author: Mike Kruk
Author URI: http://ripeworks.com/
*/

if ( ! defined( 'WPINC' ) ) die;

if (!class_exists('WP_List_Table')) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

require_once __DIR__ . '/lib/ACFFormsEntryTable.php';
require_once __DIR__ . '/lib/ACFFormsFormMetaBox.php';
require_once __DIR__ . '/lib/ACFFormsPublishMetaBox.php';

class ACFForms
{
    public $post_type = 'acf-form';
    public $entry_post_type = 'acf-form-entry';
    public $shortcode = 'acf-form';

    public function __construct()
    {
        add_action('init', array($this, 'init'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_init', array($this, 'admin_init'));
        add_action('admin_menu', array($this, 'menus'));
        add_action('admin_notices', array($this, 'notice_check_acf_installed'));
        add_action('wp', array($this, 'process_submission'));

        add_filter('post_row_actions', array($this, 'form_row_actions'), 10, 1);

        add_action('acf/save_post', array($this, 'handle_notification'), 20);
        add_filter('acf/pre_save_post', array($this, 'save_form_entry'));

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

        // register custom post type to store entries
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

        // register ACF fields for forms
        include_once 'acf-form-field-group.php';
    }

    public function admin_init()
    {
        // set up meta boxes for forms
        $form_metabox = new ACFFormsFormMetaBox();
        $publish_metabox = new ACFFormsPublishMetaBox();
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

		return wp_insert_post( $post );
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

    public function handle_notification($post_id)
    {
        $entry = get_post($post_id);
        if ($entry->post_type !== $this->entry_post_type) return $post_id;

        $form = get_post($entry->post_parent);
        if (!$form) return $post_id;

        $fields = get_fields($entry);

        do_action('acf-forms/before_notification', $entry, $form);

        $headers = array();
        $content_type = function() { return 'text/html'; };

        add_filter('wp_mail_content_type', $content_type);

        $email_field = get_field('notification_field_name', $form->ID);
        $recipients = array($fields[$email_field]);

        if (count($recipients)) {
            $from = get_field('from_address', $form->ID);
            if ($from) $headers[] = "From: $from";

            wp_mail(
                $recipients,
                $this->replace_field_tags(get_field('notification_subject', $form->ID), $fields),
                $this->replace_field_tags(get_field('notification_body', $form->ID), $fields),
                implode('\r\n', $headers)
            );
        }

        $recipients = array(get_field('admin_email', $form->ID));
        $recipients = apply_filters('acf-forms/notification_recipients', $recipients, $entry, $form);
        if (count($recipients)) {
            wp_mail(
                $recipients,
                $this->replace_field_tags(get_field('admin_email_subject', $form->ID), $fields),
                $this->replace_field_tags(get_field('admin_email_body', $form->ID), $fields)
            );
        }

        remove_filter('wp_mail_content_type', $content_type);
        do_action('acf-forms/after_notification', $entry, $form);
    }

    /*
     * ACF acf_form_head()
     */
    public function process_submission()
    {
        // global vars
        global $post_id;


        // verify nonce
        if( isset($_POST['acf_nonce']) && wp_verify_nonce($_POST['acf_nonce'], 'input') )
        {
            // $post_id to save against
            $post_id = $_POST['post_id'];


            // allow for custom save
            $post_id = apply_filters('acf/pre_save_post', $post_id);


            // save the data
            do_action('acf/save_post', $post_id);	


            // redirect
            if(isset($_POST['return']))
            {
                wp_redirect($_POST['return']);
                exit;
            }
        }

        // actions
        do_action('acf/input/admin_enqueue_scripts');

        add_action('wp_head', 'acf_form_wp_head');
    }

    public function notice_check_acf_installed()
    {
        $screen = get_current_screen();
        if ($screen->post_type !== $this->post_type) return;

        if (!function_exists('register_field_group')) {
            $msg = <<<EOT
<a href="http://wordpress.org/plugins/advanced-custom-fields/" target="_blank">Advanced Custom Fields</a> is required to use this plugin.
EOT;
            echo '<div class="error"><p>' . $msg . '</p></div>';
        }
    }

// private
    protected function entries_url($form)
    {
        return "edit.php?page=acf-forms-view-entries&post_type=" . $this->post_type . "&form_id=" . $form->ID;
    }

    protected function replace_field_tags($string, $fields)
    {
        $m = array();
        preg_match_all('/\[\w+\]/', $string, $m, PREG_OFFSET_CAPTURE);
        if (!isset($m[0])) return $string;

        foreach ($m[0] as $match) {
            if (!isset($match[0])) continue;

            $field = str_replace(array("[","]"), "", $match[0]);
            $replace = $fields[$field];
            $string = str_replace($match[0], $replace, $string);
        }

        return $string;
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
