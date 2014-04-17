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

class ACFForms
{
    public $post_type = 'acf-form';
    public $shortcode = 'acf-form';

    public function __construct()
    {
        add_action('init', array($this, 'init'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_head', array($this, 'head'));

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

        $this->register_entry_types();
    }

    public function head()
    {
        remove_meta_box('submitdiv', $this->post_type, 'side');
        add_meta_box('submitdiv', __('Actions'), array($this, 'publish_meta_box'), $this->post_type, 'side');
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
            'submissions' => '<a href="edit.php?post_type=' . $this->entry_post_type($post) . '">View Submissions</a>'
        ), $actions);
    }

    public function publish_meta_box($post)
    {
        global $action;

        $post_type = $post->post_type;
        $post_type_object = get_post_type_object($post_type);
        $can_publish = current_user_can($post_type_object->cap->publish_posts);

        return include __DIR__.'/views/publish_meta_box.php';
    }

    public function save_form_entry($post_id)
    {
        if (strpos($post_id, 'new-form-') === false) return $post_id;

		$post = array(
			'post_status' => 'publish',
			'post_title' => '',
			'post_type' => str_replace('new-', '', $post_id)
		);
		$post_id = wp_insert_post( $post );

		return $post_id;
    }

    public function register_entry_types()
    {
        $forms = $this->all_forms();
        foreach ($forms as $form) {
            $label_prefix = $form->post_title . ':';
            register_post_type($this->entry_post_type($form), array(
                'label' => "$label_prefix Entries",
                'labels' => $this->generate_labels("$label_prefix Entry", "Entries"),
                'description' => '',
                'public' => false,
                'exclude_from_search' => true,
                'publicly_queryable' => false,
                'show_ui' => false,
                'show_in_admin_bar' => false,
                'supports' => array('title'),
                'has_archive' => false
            ));
        }
    }

    public function shortcode_form($atts)
    {
        if (isset($atts['form'])) {
            // find by slug
        }

        if (isset($atts['id'])) {
            // find by id
        }

        $post_type = $this->entry_post_type($atts['form']);

        $field_groups = array();
        $field_groups = apply_filters( 'acf/location/match_field_groups', $field_groups, array('post_type' => $post_type) );

        ob_start();

        acf_form(array(
            'post_id' => 'new-' . $post_type,
            'post_title' => false,
            'post_content' => false,
            'field_groups' => $field_groups
        ));

        return ob_get_clean();
    }

    public function all_forms()
    {
        $query = new WP_Query(array(
            'post_type' => 'acf-form',
            'posts_per_page' => -1
        ));

        return $query->get_posts();
    }

// private
    protected function entry_post_type($form_or_slug)
    {
        $slug = is_object($form_or_slug) ? $form_or_slug->post_name : $form_or_slug;
        // TODO: Shorten post_type if too long

        return 'form-' . $slug;
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
}

$instance = new ACFForms();
