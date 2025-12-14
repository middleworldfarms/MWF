<?php

/**
 * Performing Ajax actions from new ulimate addon dashboard.
 */
class Sfwf_Wpforms_Styler_Fetch {


	/**
	 * Instance of class.
	 *
	 * @var Sfwf_Wpforms_Styler_Fetch
	 */
	private static $instance;

	/**
	 * Constructor function
	 */
	public function __construct() {

		add_action( 'wp_ajax_sfwf_get_all_form_names', array( $this, 'sfwf_get_all_form_names' ) );
		add_action( 'wp_ajax_sfwf_get_styler_data', array( $this, 'sfwf_get_styler_data' ) );
		add_action( 'wp_ajax_sfwf_get_global_data', array( $this, 'sfwf_get_global_data' ) );

		add_action( 'wp_ajax_sfwf_wpforms_form_html', array( $this, 'sfwf_wpforms_form_html' ) );
		add_action( 'wp_ajax_sfwf_save_styler_settings', array( $this, 'sfwf_save_styler_settings' ) );
		add_action( 'wp_ajax_sfwf_get_page_count', array( $this, 'sfwf_get_page_count' ) );
		add_action( 'wp_ajax_sfwf_confirmation_html', array( $this, 'sfwf_confirmation_html' ) );
		add_action( 'wp_ajax_sfwf_get_forms_with_styling', array( $this, 'sfwf_get_forms_with_styling' ) );
		add_action( 'wp_ajax_sfwf_delete_forms_styles', array( $this, 'sfwf_delete_forms_styles' ) );
		add_action( 'wp_ajax_sfwf_save_ultimate_settings', array( $this, 'sfwf_save_ultimate_settings' ) );
	}

	/**
	 * Main Plugin Instance
	 *
	 * Insures that only one instance of a plugin class exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 * @since 5.0
	 * @return Sfwf_Wpforms_Styler_Fetch Highlander Instance
	 */
	public static function instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Sfwf_Wpforms_Styler_Fetch ) ) {
			self::$instance = new Sfwf_Wpforms_Styler_Fetch();
		}

		return self::$instance;
	}


	/**
	 * Get all WPForms.
	 *
	 * @return void
	 */
	public function sfwf_get_all_form_names() {

		// Verify nonce.
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';

		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'sfwf_wpforms_ultimate_nonce' ) ) {
			wp_send_json_error( 'Invalid nonce' );
		}

		$form_data = array();
		if ( class_exists( 'wpforms' ) || function_exists( 'wpforms' ) ) {
			// Get all forms.
			$forms = wpforms()->form->get();

			if ( ! empty( $forms ) ) {
				foreach ( $forms as $form ) {
					// Get the form ID.
					$form_id    = isset( $form->ID ) ? $form->ID : '';
					$form_title = isset( $form->post_title ) ? $form->post_title : '';

					$form_data[] = array(
						'id'    => $form_id,
						'title' => $form_title,
					);
				}
			}
		}

		wp_send_json_success( $form_data );
	}

	/**
	 * Get list of forms which has styles.
	 *
	 * @return void
	 */
	public function sfwf_get_forms_with_styling() {

		// Verify nonce.
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'sfwf_wpforms_ultimate_nonce' ) ) {
			wp_send_json_error( 'Invalid nonce' );
		}

		$styled_forms = $this->get_forms_with_styles();
		wp_send_json_success( $styled_forms );
	}

	/**
	 * Delete styles for a particular form.
	 *
	 * @return void
	 */
	public function sfwf_delete_forms_styles() {

		// Verify nonce.
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'sfwf_wpforms_ultimate_nonce' ) ) {
			wp_send_json_error( 'Invalid nonce' );
		}

		$form_id         = isset( $_POST['formId'] ) ? (int) sanitize_text_field( wp_unslash( $_POST['formId'] ) ) : 0;
		$deletion_status = delete_option( 'sfwf_form_id_' . $form_id );
		$styled_forms    = $this->get_forms_with_styles();

		wp_send_json_success( $styled_forms );
	}

	/**
	 * Get list of all forms which have styles.
	 *
	 * @return array
	 */
	public function get_forms_with_styles() {

		$styled_forms = array();

		$wpforms_hander = wpforms()->form;
		$forms          = $wpforms_hander->get();
		$styled_forms[] = array(
			'label' => '---Select form --',
			'value' => '-1',
		);

		if ( $forms ) {
			foreach ( $forms as $form ) {
				$style_current_form = get_option( 'sfwf_form_id_' . $form->ID );

				if ( ! empty( $style_current_form ) ) {

					$styled_forms[] = array(
						'label' => $form->post_title,
						'value' => $form->ID,
					);
				}
			}
		}

		return $styled_forms;
	}

	/**
	 * Save Ultimate Kit settings.
	 *
	 * @return void
	 */
	public function sfwf_save_ultimate_settings() {

		// Verify nonce.
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'sfwf_wpforms_ultimate_nonce' ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Invalid nonce', 'wpforms-styler' ),
					'status'  => 'error',
				)
			);
		}

		$ultimate_settings = isset( $_POST['ultimateSettings'] ) ? sanitize_textarea_field( wp_unslash( $_POST['ultimateSettings'] ) ) : '';
		if ( empty( $ultimate_settings ) ) {
			wp_send_json_error( 'Ultimate Settings are empty' );
		}

		$ultimate_settings = json_decode( $ultimate_settings, true );

		$has_saved = update_option( 'sfwf_ultimate_settings', $ultimate_settings );
		if ( ! $has_saved ) {
			wp_send_json_error(
				array(
					'message' => __( 'Failed to save settings.', 'wpforms-styler' ),
					'status'  => 'error',
				)
			);
		}

		$ultimate_settings = $this->sfwf_ultimate_settings();
		wp_send_json_success( $ultimate_settings );
	}


	/**
	 * Returns the global data for the plugin.
	 *
	 * This function verifies the nonce and then retrieves the ultimate settings for the plugin.
	 * The ultimate settings are then returned as part of the response.
	 *
	 * @return void
	 */
	public function sfwf_get_global_data() {

		// Verify nonce.
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'sfwf_wpforms_ultimate_nonce' ) ) {
			wp_send_json_error( 'Invalid nonce' );
		}

		$ultimate_settings = $this->sfwf_ultimate_settings();

		$settings = array(
			'ultimateSettings' => $ultimate_settings,
		);

		wp_send_json_success( $settings );
	}

	/**
	 * Returns the styler settings
	 *
	 * @return void
	 */
	public function sfwf_get_styler_data() {

		// Verify nonce.
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'sfwf_wpforms_ultimate_nonce' ) ) {
			wp_send_json_error( 'Invalid nonce' );
		}

		$form_id = isset( $_POST['formId'] ) ? (int) sanitize_text_field( wp_unslash( $_POST['formId'] ) ) : '';
		// if ( empty( $form_id ) ) {
		// wp_send_json_error( 'Form id not selected' );
		// }

		$styler_settings  = $this->sfwf_styler_settings( $form_id );
		$general_settings = $this->sfwf_general_settings( $form_id );
		$field_labels     = $this->sfwf_form_fields_labels( $form_id );

		$settings = array(
			'stylerSettings'  => $styler_settings,
			'generalSettings' => $general_settings,
			'labels'          => $field_labels,
		);

		wp_send_json_success( $settings );
	}

	/**
	 * Form field labels
	 *
	 * @param int $form_id Id of the form for which labels have to be fetched.
	 * @return array
	 */
	public function sfwf_form_fields_labels( $form_id ) {

		$form_id = intval( $form_id );
		if ( empty( $form_id ) ) {
			return array();
		}

		$complex_fields = array( 'name', 'address', 'email', 'date-time', 'password' );
		$choices_fields = array( 'radio', 'checkbox', 'payment-multiple', 'payment-checkbox' );

		// get form data.
		$form_data = wpforms()->form->get(
			$form_id,
			array( 'content_only' => true )
		);

		$field_labels = array();

		foreach ( $form_data['fields'] as $field ) {

			$choices = array();

			// for fields which have choices.
			if ( in_array( $field['type'], $choices_fields, true ) ) {
				foreach ( $field['choices'] as $choice_index => $choice ) {
					$choices[ $choice_index ] = $choice['label'];
				}
			}

			// for complex fields.
			if ( in_array( $field['type'], $complex_fields, true ) ) {

				// for complex fiels need to create the settings of every subfield.
				switch ( $field['type'] ) {
					case 'address':
						$sub_fields = array( 'Address Line 1', 'Address Line 2', 'City', 'State', 'Zip Code', 'Country' );
						break;
					case 'email':
						$sub_fields = ! empty( $field['confirmation'] ) ? array( 'Email', 'Confirm Email' ) : array();
						break;

					case 'date-time':
						$sub_fields = array( 'Date', 'Time' );
						break;

					case 'password':
						$sub_fields = ! empty( $field['confirmation'] ) ? array( 'Password', 'Confirm Password' ) : array();
						break;
					case 'name':
						if ( isset( $field['format'] ) && 'first-middle-last' === $field['format'] ) {
							$sub_fields = array( 'First Name', 'Middle Name', 'Last Name' );
						} elseif ( isset( $field['format'] ) && 'first-last' === $field['format'] ) {
							$sub_fields = array( 'First Name', 'Last Name' );
						} else {
							// Incase the name field format is set to simple.
							$sub_fields = array();
						}
						break;

					default:
						$sub_fields = array();
				}

				// Checking for sub fields which can be disabled.
				foreach ( $sub_fields as $sub_field ) {
					$sub_field_visible = true;

					switch ( $sub_field ) {
						case 'Address Line 2':
							if ( ! empty( $field['address2_hide'] ) ) {
								$sub_field_visible = false;
							}
							break;
						case 'Zip Code':
							if ( ! empty( $field['postal_hide'] ) ) {
								$sub_field_visible = false;
							}
							break;

						case 'Country':
							if ( ! empty( $field['country_hide'] ) || ( isset( $field['scheme'] ) && 'us' === $field['scheme'] ) ) {
								$sub_field_visible = false;
							}
							break;
					}

					// push sub-fields of complex fields.
					if ( $sub_field_visible ) {
						$id_value = str_replace( ' ', '_', $sub_field );
						$id_value = strtolower( $id_value );

						$choices[ $id_value ] = $sub_field;
					}
				}
			}

			$field_label = isset( $field['label'] ) ? $field['label'] : '';

			// for page break.
			if ( 'pagebreak' === $field['type'] ) {
				$field_label = ! empty( $field['title'] ) ? $field['title'] : 'pagebreak (' . $field['id'] . ')';
			} elseif ( 'html' === $field['type'] ) {
				$field_label = ! empty( $field['name'] ) ? $field['name'] : 'HTML (' . $field['id'] . ')';
			}

			$field_data = array(
				'id'      => $field['id'],
				'label'   => $field_label,
				'type'    => $field['type'],
				'choices' => $choices,
			);

			// pass the date type for date field.
			$date_type = '';
			if ( 'date-time' === $field['type'] && ! empty( $field['date_type'] ) ) {
				$date_type = $field['date_type'];
			}
			$field_data['date_type'] = $date_type;

			// pass the position of pagebreaks.
			$position = '';
			if ( 'pagebreak' === $field['type'] ) {
				$position = $field['position'];
			}

			$child_field_ids = array();
			if ( 'layout' === $field['type'] && isset( $field['columns'] ) ) {

				foreach ( $field['columns'] as $column ) {
					if ( ! empty( $column['fields'] ) && is_array( $column['fields'] ) ) {
						$child_field_ids = array_merge( $child_field_ids, $column['fields'] );
					}
				}
			}

			$field_data['position']        = $position;
			$field_data['child_field_ids'] = $child_field_ids;
			$field_data['description']     = ! empty( $field['description'] ) ? $field['description'] : '';
			$field_data['size']            = ! empty( $field['size'] ) ? $field['size'] : 'medium';
			$field_data['required']        = ! empty( $field['required'] ) ? $field['required'] : false;
			$field_data['placeholder']     = ! empty( $field['placeholder'] ) ? $field['placeholder'] : '';
			$field_data['css']             = ! empty( $field['css'] ) ? $field['css'] : '';
			$field_data['label_hide']      = ! empty( $field['label_hide'] ) ? $field['label_hide'] : false;

			$field_labels[] = $field_data;
		}

		return $field_labels;
	}

	/**
	 * Returns the styler settings
	 *
	 * @param int $form_id id of the form for which styles have to fetched.
	 * @return array
	 */
	public function sfwf_styler_settings( $form_id ) {

		$form_id = intval( $form_id );
		if ( empty( $form_id ) ) {
			return array();
		}

		$settings = get_option( 'sfwf_form_id_' . $form_id );
		$settings = empty( $settings ) ? array() : $settings;

		return $settings;
	}


	/**
	 * Returns the styler general settings
	 *
	 * @param int $form_id id of the form for which styles have to fetched.
	 * @return array
	 */
	public function sfwf_general_settings( $form_id ) {
		$form_id = intval( $form_id );
		if ( empty( $form_id ) ) {
			return array();
		}

		$settings = get_option( 'sfwf_general_settings' . $form_id );
		$settings = empty( $settings ) ? array() : $settings;

		return $settings;
	}


	/**
	 * Returns the styler Ultimate Kit settings
	 *
	 * @return array
	 */
	public function sfwf_ultimate_settings() {

		$ultimate_settings = get_option( 'sfwf_ultimate_settings' );

		$sfwf_license      = get_option( 'sfwf_licenses' );
		$sfwf_license      = empty( $sfwf_license ) ? array() : $sfwf_license;
		$ultimate_settings = empty( $ultimate_settings ) ? array() : $ultimate_settings;

		$license_status_keys = array( 'bootstrap_addon_license_status', 'sfwf_tooltips_addon_license_status', 'field_icons_addon_license_status', 'blacklist_addon_license_status', 'power_ups_addon_license_status', 'file_renamer_addon_license_status', 'bulk_actions_addon_license_status' );
		$license_status      = array();

		foreach ( $license_status_keys as $license_status_key ) {
			$license_status[ $license_status_key ] = get_option( $license_status_key );
		}

		$licenses = array(
			'licenses' => array(
				'keys'   => $sfwf_license,
				'status' => $license_status,
				'notice' => array(),
			),
		);

		$settings = array_merge( $licenses, $ultimate_settings );

		return $settings;
	}

	/**
	 * Save all the styler settings on save button.
	 *
	 * @return void
	 */
	public function sfwf_save_styler_settings() {
		// Verify nonce.
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'sfwf_wpforms_ultimate_nonce' ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Invalid nonce', 'wpforms-styler' ),
					'status'  => 'error',
				)
			);
		}

		$form_id         = isset( $_POST['formId'] ) ? (int) sanitize_text_field( wp_unslash( $_POST['formId'] ) ) : 0;
		$styler_settings = isset( $_POST['stylerSettings'] ) ? sanitize_text_field( wp_unslash( $_POST['stylerSettings'] ) ) : 0;
		$styler_settings = json_decode( $styler_settings, true );

		$general_settings = isset( $_POST['generalSettings'] ) ? sanitize_text_field( wp_unslash( $_POST['generalSettings'] ) ) : '';
		$general_settings = json_decode( $general_settings, true );

		$has_styler_settings_saved = update_option( 'sfwf_form_id_' . $form_id, $styler_settings );

		$has_general_settings_saved = update_option( 'sfwf_general_settings' . $form_id, $general_settings );

		if ( ! $has_styler_settings_saved && ! $has_general_settings_saved ) {
			wp_send_json_error(
				array(
					'message' => __( 'Failed to save settings.', 'wpforms-styler' ),
					'status'  => 'error',
				)
			);
		}

		$settings = array(
			'stylerSettings'  => $styler_settings,
			'generalSettings' => $general_settings,
		);

		wp_send_json_success( $settings );
	}

	/**
	 * Get the total number of pages in form
	 *
	 * @return void
	 */
	public function sfwf_get_page_count() {
		// Verify nonce.
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'sfwf_wpforms_ultimate_nonce' ) ) {
			wp_send_json_error( 'Invalid nonce' );
		}

		if ( ! check_ajax_referer( 'sfwf_wpforms_ultimate_nonce', 'nonce', false ) ) {
			wp_send_json_error( 'Invalid nonce' );
		}

		$form_id = isset( $_POST['formId'] ) ? (int) sanitize_text_field( wp_unslash( $_POST['formId'] ) ) : 0;

		// get form data.
		$form_data = wpforms()->form->get(
			$form_id,
			array( 'content_only' => true )
		);

		// get page breaks data.
		$page_breaks = wpforms_get_pagebreak_details( $form_data );
		$total       = 1;
		if ( ! empty( $page_breaks ) ) {
			$total = isset( $page_breaks['total'] ) ? $page_breaks['total'] : $total;
		}

		wp_send_json_success( $total );
	}

	/**
	 * Get Confirmation Email.
	 *
	 * @return void
	 */
	public function sfwf_confirmation_html() {

		// Verify nonce.
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'sfwf_wpforms_ultimate_nonce' ) ) {
			wp_send_json_error( 'Invalid nonce' );
		}

		if ( ! check_ajax_referer( 'sfwf_wpforms_ultimate_nonce', 'nonce', false ) ) {
			wp_send_json_error( 'Invalid nonce' );
		}

		$form_id = isset( $_POST['formId'] ) ? (int) sanitize_text_field( wp_unslash( $_POST['formId'] ) ) : 0;

		// Get the form object.
		$form      = wpforms()->form->get( $form_id );
		$form_data = wpforms()->form->get( $form_id, array( 'content_only' => true ) );

		$message = 'Thanks for contacting us! We will be in touch with you shortly.';
		foreach ( $form_data['settings']['confirmations'] as $confirmation ) {
			if ( 'message' === $confirmation['type'] ) {
				$message = $confirmation['message'];
				break;
			}
		}
		wp_send_json_success( $message );
	}



	/**
	 * Returns the html for wpforms form
	 *
	 * @return void
	 */
	public function sfwf_wpforms_form_html() {

		// Verify nonce.
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'sfwf_wpforms_ultimate_nonce' ) ) {
			wp_send_json_error( 'Invalid nonce' );
		}

		$form_id = isset( $_POST['formId'] ) ? (int) sanitize_text_field( wp_unslash( $_POST['formId'] ) ) : 0;

		echo do_shortcode( '[wpforms id="' . $form_id . '" title="true" description="true"  ]' );

		// Make sure to exit after outputting the HTML.
		wp_die();
	}
}


/**
 * The main function responsible for returning The Highlander Plugin
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * @since 3.0
 * @return {class} Highlander Instance
 */
function sfwf_wpforms_initialize_styler_fetch() {
	return Sfwf_Wpforms_Styler_Fetch::instance();
}

sfwf_wpforms_initialize_styler_fetch();
