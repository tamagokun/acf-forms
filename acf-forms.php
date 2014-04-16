<?php
/*
Plugin Name: ACF Forms
Plugin URI: https://github.com/tamagokun/acf-forms
Description: Create forms using Advanced Custom Fields.
Version: 0.1
Author: Mike Kruk
Author URI: http://ripeworks.com/
*/

class ACFForms
{
    public function __construct()
    {
        add_action('init', array($this, 'init'));
    }

    public function init()
    {
        // register custom post type to store entries
        register_post_type('acf-form', array(
            'label' => 'Forms',
            'labels' => array(
                'singular_name' => 'Form'
            ),
            'description' => '',
            'public' => false,
            'exclude_from_search' => true,
            'publicly_queryable' => false,
            'show_ui' => true,
            'show_in_admin_bar' => false,
            'supports' => false, // check this
            'has_archive' => false
        ));

    }
}

$plugin = new ACFForms();
