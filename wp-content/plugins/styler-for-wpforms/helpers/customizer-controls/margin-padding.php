<?php
/**
 * Create controls for marine and padding.
 *
 * @param object $wp_customize WordPress customizer object.
 * @param int    $current_form_id form id for which the controls should be.
 * @param string $section section under which the controls should be added.
 * @param string $setting_type the type of render control.
 * @param string $setting_name the name of newly created control.
 * @return void
 */
function sfwf_margin_padding_controls( $wp_customize, $current_form_id, $section, $setting_type, $setting_name ) {

	$wp_customize->add_setting(
		'sfwf_form_id_' . $current_form_id . '[' . $setting_type . '][' . $setting_name . '-top]',
		array(
			'default'   => '',
			'transport' => 'postMessage',
			'type'      => 'option',
		)
	);

	$wp_customize->add_control(
		'sfwf_form_id_' . $current_form_id . '[' . $setting_type . '][' . $setting_name . '-top]',
		array(
			'type'     => 'text',
			'priority' => 10, // Within the section.
			'section'  => $section, // Required, core or custom.
			'label'    => __( 'Top' ),
		)
	);

	$wp_customize->add_setting(
		'sfwf_form_id_' . $current_form_id . '[' . $setting_type . '][' . $setting_name . '-bottom]',
		array(
			'default'   => '',
			'transport' => 'postMessage',
			'type'      => 'option',
		)
	);

	$wp_customize->add_control(
		'sfwf_form_id_' . $current_form_id . '[' . $setting_type . '][' . $setting_name . '-bottom]',
		array(
			'type'     => 'text',
			'priority' => 10, // Within the section.
			'section'  => $section, // Required, core or custom.
			'label'    => __( 'Bottom' ),
		)
	);

	$wp_customize->add_setting(
		'sfwf_form_id_' . $current_form_id . '[' . $setting_type . '][' . $setting_name . '-left]',
		array(
			'default'   => '',
			'transport' => 'postMessage',
			'type'      => 'option',
		)
	);

	$wp_customize->add_control(
		'sfwf_form_id_' . $current_form_id . '[' . $setting_type . '][' . $setting_name . '-left]',
		array(
			'type'     => 'text',
			'priority' => 10, // Within the section.
			'section'  => $section, // Required, core or custom.
			'label'    => __( 'Left' ),
		)
	);

	$wp_customize->add_setting(
		'sfwf_form_id_' . $current_form_id . '[' . $setting_type . '][' . $setting_name . '-right]',
		array(
			'default'   => '',
			'transport' => 'postMessage',
			'type'      => 'option',
		)
	);

	$wp_customize->add_control(
		'sfwf_form_id_' . $current_form_id . '[' . $setting_type . '][' . $setting_name . '-right]',
		array(
			'type'     => 'text',
			'priority' => 10, // Within the section.
			'section'  => $section, // Required, core or custom.
			'label'    => __( 'Right' ),
		)
	);
}
