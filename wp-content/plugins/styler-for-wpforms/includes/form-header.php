<?php
/**
 * Controls to desgin the form header in customizer.
 */

$wp_customize->add_section(
	'sfwf_form_id_form_header',
	array(
		'title' => 'Form Header',
		'panel' => 'sfwf_panel',
	)
);

$wp_customize->add_setting(
	'sfwf_form_id_' . $current_form_id . '[form-header][background-color]',
	array(
		'default'   => '',
		'transport' => 'postMessage',
		'type'      => 'option',
	)
);

$wp_customize->add_control(
	new WP_Customize_Color_Control(
		$wp_customize, // WP_Customize_Manager.
		'sfwf_form_id_' . $current_form_id . '[form-header][background-color]', // Setting id.
		array( // Args, including any custom ones.
			'label'   => __( 'Header Background Color' ),
			'section' => 'sfwf_form_id_form_header',
		)
	)
);

// Border Label.
$wp_customize->add_control(
	new Sfwf_Label_Only(
		$wp_customize, // WP_Customize_Manager.
		'sfwf_form_id_' . $current_form_id . '[form-header][border-label-only]', // Setting id.
		array( // Args, including any custom ones.
			'label'    => __( 'Header Border' ),
			'section'  => 'sfwf_form_id_form_header',
			'settings' => array(),
		)
	)
);
$wp_customize->add_setting(
	'sfwf_form_id_' . $current_form_id . '[form-header][border-size]',
	array(
		'default'   => '',
		'transport' => 'postMessage',
		'type'      => 'option',
	)
);

$wp_customize->add_control(
	'sfwf_form_id_' . $current_form_id . '[form-header][border-size]',
	array(
		'type'        => 'text',
		'priority'    => 10, // Within the section.
		'section'     => 'sfwf_form_id_form_header', // Required, core or custom.
		'label'       => __( 'Size' ),
		'input_attrs' => array(
			'placeholder' => 'Example: 4px or 10%',
		),
	)
);

$wp_customize->add_setting(
	'sfwf_form_id_' . $current_form_id . '[form-header][border-type]',
	array(
		'default'   => 'solid',
		'transport' => 'postMessage',
		'type'      => 'option',
	)
);

$wp_customize->add_control(
	'sfwf_form_id_' . $current_form_id . '[form-header][border-type]',
	array(
		'type'     => 'select',
		'priority' => 10, // Within the section.
		'section'  => 'sfwf_form_id_form_header', // Required, core or custom.
		'label'    => __( 'Type' ),
		'choices'  => $border_types,
	)
);

$wp_customize->add_setting(
	'sfwf_form_id_' . $current_form_id . '[form-header][border-radius]',
	array(
		'default'   => '',
		'transport' => 'postMessage',
		'type'      => 'option',
	)
);

$wp_customize->add_control(
	'sfwf_form_id_' . $current_form_id . '[form-header][border-radius]',
	array(
		'type'        => 'text',
		'priority'    => 10, // Within the section.
		'section'     => 'sfwf_form_id_form_header', // Required, core or custom.
		'label'       => __( 'Radius' ),
		'input_attrs' => array(
			'placeholder' => 'Ex.4px',
		),
	)
);

$wp_customize->add_setting(
	'sfwf_form_id_' . $current_form_id . '[form-header][border-color]',
	array(
		'transport' => 'postMessage',
		'type'      => 'option',
	)
);

$wp_customize->add_control(
	new WP_Customize_Color_Control(
		$wp_customize, // WP_Customize_Manager.
		'sfwf_form_id_' . $current_form_id . '[form-header][border-color]', // Setting id.
		array( // Args, including any custom ones.
			'label'   => __( 'Header Border Color' ),
			'section' => 'sfwf_form_id_form_header',
		)
	)
);

// Start of Section
// Label.
$wp_customize->add_control(
	new WP_Customize_Label_Only(
		$wp_customize, // WP_Customize_Manager.
		'sfwf_form_id_' . $current_form_id . '[form-header][margin-label-only]', // Setting id.
		array( // Args, including any custom ones.
			'label'    => __( 'Margin' ),
			'section'  => 'sfwf_form_id_form_header',
			'settings' => array(),
		)
	)
);

sfwf_margin_padding_controls( $wp_customize, $current_form_id, 'sfwf_form_id_form_header', 'form-header', 'margin' );


$wp_customize->add_control(
	new WP_Customize_Label_Only(
		$wp_customize, // WP_Customize_Manager.
		'sfwf_form_id_' . $current_form_id . '[form-header][padding-label-only]', // Setting id.
		array( // Args, including any custom ones.
			'label'    => __( 'Padding' ),
			'section'  => 'sfwf_form_id_form_header',
			'settings' => array(),
		)
	)
);

sfwf_margin_padding_controls( $wp_customize, $current_form_id, 'sfwf_form_id_form_header', 'form-header', 'padding' );
