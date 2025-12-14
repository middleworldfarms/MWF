<?php
/**
 * Controls for Error Message.
 */

$wp_customize->add_section(
	'sfwf_form_id_error_message',
	array(
		'title' => 'Error Message',
		'panel' => 'sfwf_panel',
	)
);

// Label width.
$wp_customize->add_control(
	new Sfwf_Label_Only(
		$wp_customize, // WP_Customize_Manager.
		'sfwf_form_id_' . $current_form_id . '[error-message][max-width-label-only]', // Setting id.
		array( // Args, including any custom ones.
			'label'    => __( 'Width' ),
			'section'  => 'sfwf_form_id_error_message',
			'settings' => array(),
		)
	)
);

$wp_customize->add_setting(
	'sfwf_form_id_' . $current_form_id . '[error-message][max-width]',
	array(
		'default'   => '',
		'transport' => 'refresh',
		'type'      => 'option',
	)
);

$wp_customize->add_control(
	new Sfwf_Desktop_Text_Input_Option(
		$wp_customize,
		'sfwf_form_id_' . $current_form_id . '[error-message][max-width]',
		array(
			'type'        => 'text',
			'priority'    => 10, // Within the section.
			'section'     => 'sfwf_form_id_error_message', // Required, core or custom.
			'label'       => '',
			'input_attrs' => array(
				'placeholder' => 'Ex.40px',
			),
		)
	)
);

// Tablet.
$wp_customize->add_setting(
	'sfwf_form_id_' . $current_form_id . '[error-message][max-width-tab]',
	array(
		'default'   => '',
		'transport' => 'refresh',
		'type'      => 'option',
	)
);

$wp_customize->add_control(
	new Sfwf_Tab_Text_Input_Option(
		$wp_customize,
		'sfwf_form_id_' . $current_form_id . '[error-message][max-width-tab]',
		array(
			'type'        => 'text',
			'priority'    => 10, // Within the section.
			'section'     => 'sfwf_form_id_error_message', // Required, core or custom.
			'label'       => '',
			'input_attrs' => array(
				'placeholder' => 'Ex.40px',
			),
		)
	)
);

// Mobile.
$wp_customize->add_setting(
	'sfwf_form_id_' . $current_form_id . '[error-message][max-width-phone]',
	array(
		'default'   => '',
		'transport' => 'refresh',
		'type'      => 'option',
	)
);

$wp_customize->add_control(
	new Sfwf_Mobile_Text_Input_Option(
		$wp_customize,
		'sfwf_form_id_' . $current_form_id . '[error-message][max-width-phone]',
		array(
			'type'        => 'text',
			'priority'    => 10, // Within the section.
			'section'     => 'sfwf_form_id_error_message', // Required, core or custom.
			'label'       => '',
			'input_attrs' => array(
				'placeholder' => 'Ex.40px',
			),
		)
	)
);

// font align style buttons.
$wp_customize->add_setting(
	'sfwf_form_id_' . $current_form_id . '[error-message][text-align]',
	array(
		'default'   => '',
		'transport' => 'postMessage',
		'type'      => 'option',
	)
);

$wp_customize->add_control(
	new Sfwf_Text_Alignment_Option(
		$wp_customize,
		'sfwf_form_id_' . $current_form_id . '[error-message][text-align]',
		array(
			'label'   => 'Font Alignment',
			'section' => 'sfwf_form_id_error_message',
			'type'    => 'text_alignment',
			'choices' => $align_pos,
		)
	)
);

$wp_customize->add_setting(
	'sfwf_form_id_' . $current_form_id . '[error-message][background-color]',
	array(
		'default'   => '',
		'transport' => 'postMessage',
		'type'      => 'option',
	)
);

$wp_customize->add_control(
	new WP_Customize_Color_Control(
		$wp_customize, // WP_Customize_Manager.
		'sfwf_form_id_' . $current_form_id . '[error-message][background-color]', // Setting id.
		array( // Args, including any custom ones.
			'label'   => __( 'Background Color' ),
			'section' => 'sfwf_form_id_error_message',
		)
	)
);
$wp_customize->add_control(
	new WP_Customize_Label_Only(
		$wp_customize, // WP_Customize_Manager.
		'sfwf_form_id_' . $current_form_id . '[error-message][padding-label-only]', // Setting id.
		array( // Args, including any custom ones.
			'label'    => __( 'Padding' ),
			'section'  => 'sfwf_form_id_error_message',
			'settings' => array(),
		)
	)
);

sfwf_margin_padding_controls( $wp_customize, $current_form_id, 'sfwf_form_id_error_message', 'error-message', 'padding' );
