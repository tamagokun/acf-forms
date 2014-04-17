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
    public function __construct()
    {
        add_action('init', array($this, 'init'));

        add_filter('acf/pre_save_post' , array($this, 'save_form_entry'));

        add_shortcode('acf-form', array($this, 'shortcode_form'));
    }

    public function init()
    {
        $labels = array(
		    'name' => __('Forms'),
			'singular_name' => __('Form'),
		    'add_new' => __('Add New'),
		    'add_new_item' => __('Add New Form'),
		    'edit_item' =>  __('Edit Form'),
		    'new_item' => __('New Form'),
		    'view_item' => __('View Form'),
		    'search_items' => __('Search Forms'),
		    'not_found' =>  __('No Forms found'),
		    'not_found_in_trash' => __('No Forms found in Trash')
        );

        // register custom post type to store forms
        register_post_type('acf-form', array(
            'label' => 'Forms',
            'labels' => $labels,
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
            register_post_type('form-' . $form->post_name, array(
                'label' => "$label_prefix Entries",
                'labels' => array(
                    'singular_name' => "$label_prefix Entry"
                ),
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

        $post_type = 'form-' . $atts['form'];

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
}

$instance = new ACFForms();
