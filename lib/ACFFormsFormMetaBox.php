<?php

class ACFFormsFormMetaBox
{
    public function __construct()
    {
        add_action('save_post', array($this, 'save'));

        add_meta_box(
            'acf-forms-form-field-group',
            'Form Settings',
            array($this, 'show'),
            'acf-form',
            'normal',
            'high'
        );
    }

    public function show($post, $metabox)
    {
        $field_groups = array();
        $field_groups = apply_filters('acf/get_field_groups', $field_groups);
        // remove acf-form field group
        foreach ($field_groups as $k=>$field_group) {
            if ($field_group['id'] == 'acf_form-settings') unset($field_groups[$k]);
        }

        include(dirname(__DIR__) . '/views/form_meta_box.php');
    }

    public function save($post_id)
    {
        if (empty($_POST['meta_box_ids'])) return;
        foreach ($_POST['meta_box_ids'] as $metabox_id) {
            if (!wp_verify_nonce($_POST[$metabox_id . '_nonce'], 'save_' . $metabox_id)) continue;
            if (count($_POST[$metabox_id . '_fields']) < 1) continue;
            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) continue;

            if ($metabox_id == 'acf-forms-form-field-group') {
                update_post_meta($post_id, 'form_field_group', $_POST['form_field_group']);
            }
        }

        return $post_id;
    }
}
