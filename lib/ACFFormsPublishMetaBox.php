<?php

class ACFFormsPublishMetaBox
{
    public function __construct()
    {
        remove_meta_box('submitdiv', 'acf-form', 'side');
        add_action('add_meta_boxes', array($this, 'add_meta_box'));
    }

    public function add_meta_box()
    {
        add_meta_box(
            'submitdiv',
            __('Actions'),
            array($this, 'show'),
            'acf-form',
            'side'
        );
    }

    public function show($post, $metabox)
    {
        global $action;

        $post_type = $post->post_type;
        $post_type_object = get_post_type_object($post_type);
        $can_publish = current_user_can($post_type_object->cap->publish_posts);
        $entries_url = $this->entries_url($post);

        return include dirname(__DIR__).'/views/publish_meta_box.php';
    }

//private
    protected function entries_url($form)
    {
        return "edit.php?page=acf-forms-view-entries&post_type=acf-form&form_id=" . $form->ID;
    }
}
