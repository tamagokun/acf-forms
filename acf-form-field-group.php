<?php

if(function_exists("register_field_group"))
{
	register_field_group(array (
		'id' => 'acf_form-settings',
		'title' => 'Form Settings',
		'fields' => array (
			array (
				'key' => 'field_534fb86ebcdc3',
				'label' => 'General Settings',
				'name' => '',
				'type' => 'tab',
			),
			array (
				'key' => 'field_5350063d81014',
				'label' => 'Submit button label',
				'name' => 'submit_value',
				'type' => 'text',
				'instructions' => 'The text used for the form submission button.',
				'default_value' => 'Submit',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
				'formatting' => 'html',
				'maxlength' => '',
			),
            // ACF v5
			/* array ( */
			/* 	'key' => 'field_53500687f4610', */
			/* 	'label' => 'Label placement', */
			/* 	'name' => 'label_placement', */
			/* 	'type' => 'select', */
			/* 	'choices' => array ( */
			/* 		'top' => 'Top', */
			/* 		'left' => 'Left', */
			/* 	), */
			/* 	'default_value' => 'top', */
			/* 	'allow_null' => 0, */
			/* 	'multiple' => 0, */
			/* ), */
			/* array ( */
			/* 	'key' => 'field_535006aaf4611', */
			/* 	'label' => 'Instruction placement', */
			/* 	'name' => 'instruction_placement', */
			/* 	'type' => 'select', */
			/* 	'instructions' => 'Control where field instructions are placed, if any.', */
			/* 	'choices' => array ( */
			/* 		'label' => 'Below label', */
			/* 		'field' => 'Below field', */
			/* 	), */
			/* 	'default_value' => '', */
			/* 	'allow_null' => 0, */
			/* 	'multiple' => 0, */
			/* ), */
			array (
				'key' => 'field_5350061c81012',
				'label' => 'Form header',
				'name' => 'form_header',
				'type' => 'wysiwyg',
				'instructions' => 'Content placed above the form.',
				'default_value' => '',
				'toolbar' => 'full',
				'media_upload' => 'yes',
			),
			array (
				'key' => 'field_5350062f81013',
				'label' => 'Form footer',
				'name' => 'form_footer',
				'type' => 'wysiwyg',
				'instructions' => 'Content placed below the form, but above the submit button.',
				'default_value' => '',
				'toolbar' => 'full',
				'media_upload' => 'yes',
			),
			array (
				'key' => 'field_535006f1f4612',
				'label' => 'Submission Settings',
				'name' => '',
				'type' => 'tab',
			),
			array (
				'key' => 'field_5350070b9f713',
				'label' => 'Success action',
				'name' => 'success_action',
				'type' => 'select',
				'instructions' => 'Occurs after user successfully submits form.',
				'choices' => array (
					'message' => 'Display message',
					'redirect' => 'Redirect to page',
				),
				'default_value' => 'message',
				'allow_null' => 0,
				'multiple' => 0,
			),
			array (
				'key' => 'field_535007469f714',
				'label' => 'Success message',
				'name' => 'success_message',
				'type' => 'wysiwyg',
				'conditional_logic' => array (
					'status' => 1,
					'rules' => array (
						array (
							'field' => 'field_5350070b9f713',
							'operator' => '==',
							'value' => 'message',
						),
					),
					'allorany' => 'all',
				),
				'default_value' => '',
				'toolbar' => 'full',
				'media_upload' => 'yes',
			),
			array (
				'key' => 'field_535007799f715',
				'label' => 'Success page',
				'name' => 'redirect',
				'type' => 'page_link',
				'conditional_logic' => array (
					'status' => 1,
					'rules' => array (
						array (
							'field' => 'field_5350070b9f713',
							'operator' => '==',
							'value' => 'redirect',
						),
					),
					'allorany' => 'all',
				),
				'post_type' => array (
					0 => 'all',
				),
				'allow_null' => 0,
				'multiple' => 0,
			),
			array (
				'key' => 'field_534fb880bcdc4',
				'label' => 'User Notifications',
				'name' => '',
				'type' => 'tab',
			),
			array (
				'key' => 'field_53500805ef0d0',
				'label' => 'Notification recipient',
				'name' => 'notification_recipient',
				'type' => 'email',
				'default_value' => '',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
			),
			array (
				'key' => 'field_53512436e3ce7',
				'label' => 'From address',
				'name' => 'from_address',
				'type' => 'email',
				'instructions' => 'Defaults to how Wordpress sends email if blank.',
				'default_value' => '',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
			),
			array (
				'key' => 'field_53500854ef0d1',
				'label' => 'Notification subject',
				'name' => 'notification_subject',
				'type' => 'text',
				'default_value' => '',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
				'formatting' => 'html',
				'maxlength' => '',
			),
			array (
				'key' => 'field_5350085cef0d2',
				'label' => 'Notification body',
				'name' => 'notification_body',
				'type' => 'wysiwyg',
				'instructions' => 'Stuff about template tags [email]',
				'default_value' => '',
				'toolbar' => 'full',
				'media_upload' => 'no',
			),
			array (
				'key' => 'field_535fff2904be0',
				'label' => 'Admin Notifications',
				'name' => '',
				'type' => 'tab',
			),
			array (
				'key' => 'field_535ffdc2ff487',
				'label' => 'Admin email',
				'name' => 'admin_email',
				'type' => 'email',
				'instructions' => 'If you want a submission notification to be sent to an administrator, enter a valid email address',
				'default_value' => '',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
			),
			array (
				'key' => 'field_535ffea3ff488',
				'label' => 'Admin email subject',
				'name' => 'admin_email_subject',
				'type' => 'text',
				'instructions' => 'Subject line of admin submission notification',
				'default_value' => '',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
				'formatting' => 'html',
				'maxlength' => '',
			),
			array (
				'key' => 'field_535ffec2ff489',
				'label' => 'Admin email body',
				'name' => 'admin_email_body',
				'type' => 'wysiwyg',
				'instructions' => 'Body of admin email notification',
				'default_value' => '',
				'toolbar' => 'full',
				'media_upload' => 'yes',
			),
		),
		'location' => array (
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'acf-form',
					'order_no' => 0,
					'group_no' => 0,
				),
			),
		),
		'options' => array (
			'position' => 'normal',
			'layout' => 'no_box',
			'hide_on_screen' => array (
			),
		),
		'menu_order' => 0,
	));
}
