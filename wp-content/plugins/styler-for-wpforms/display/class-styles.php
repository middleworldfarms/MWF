<?php
/**
 * Render Form Styles on frontend.
 */

$get_form_options     = get_option( 'sfwf_form_id_' . $css_form_id );
$get_general_settings = get_option( 'sfwf_general_settings' . $css_form_id );
$important            = ! empty( $get_general_settings['force-style'] ) ? '!important' : '';

ob_start();
?>

<style type="text/css">
<?php
if ( isset( $get_form_options['form-wrapper']['font'] ) ) {
	$font_name = $get_form_options['form-wrapper']['font'];
	$font_name = str_replace( ' ', '+', $font_name );
	if ( 'Default' !== $font_name ) {
		echo "@import url('https://fonts.googleapis.com/css?family=" . esc_attr( $font_name ) . "');";
	}
}
?>


<?php
if ( isset( $get_form_options['form-wrapper'] ) ) {
	?>

body #wpforms-<?php echo esc_html( $css_form_id ); ?> {
		<?php
		echo esc_html(
			$main_class_object->swfw_get_saved_styles( $css_form_id, 'form-wrapper', $important )
		);
		?>

	<?php echo empty( $get_form_options['form-wrapper']['background-image'] ) ? '' : 'background-image:url("' . esc_url( $get_form_options['form-wrapper']['background-image'] ) . '");'; ?>
		<?php
		if ( ! empty( $get_form_options['form-wrapper']['font'] ) ) {
			if ( 'Default' === $get_form_options['form-wrapper']['font'] ) {
				echo 'font-family:inherit;';
			} else {
				echo 'font-family:' . esc_html( $get_form_options['form-wrapper']['font'] ) . ';';
			}
		}

		// when border type is empty and border size is set then print the default border style solid.
		if ( empty( $get_form_options['form-wrapper']['border-type'] ) && ! empty( $get_form_options['form-wrapper']['border-size'] ) ) {
			echo 'border-style: solid;';
		}

		?>
}

	<?php } ?>

<?php if ( isset( $get_form_options['form-header'] ) ) { ?>

	body #wpforms-<?php echo esc_html( $css_form_id ); ?> .wpforms-head-container {
	<?php
	echo esc_html(
		$main_class_object->swfw_get_saved_styles( $css_form_id, 'form-header', $important )
	);
	?>
	<?php
	if ( empty( $get_form_options['form-header']['border-size'] ) ) {
		echo 'border-width: 0px;';
	}
	?>
}
<?php } ?>

<?php if ( isset( $get_form_options['form-title'] ) ) { ?>
	body #wpforms-<?php echo esc_html( $css_form_id ); ?> .wpforms-head-container .wpforms-title {
	<?php echo esc_html( $main_class_object->swfw_get_saved_styles( $css_form_id, 'form-title', $important ) ); ?>
	}
<?php } ?>

<?php if ( isset( $get_form_options['form-description'] ) ) { ?>
	body #wpforms-<?php echo esc_html( $css_form_id ); ?> .wpforms-head-container .wpforms-description {
		<?php echo esc_html( $main_class_object->swfw_get_saved_styles( $css_form_id, 'form-description', $important ) ); ?>
		display:block;
	}
<?php } ?>


<?php if ( isset( $get_form_options['submit-button'] ) ) { ?>
	body #wpforms-<?php echo esc_html( $css_form_id ); ?> .wpforms-submit-container .wpforms-submit, body #wpforms-<?php echo esc_html( $css_form_id ); ?> .wpforms-field-pagebreak button.wpforms-page-button {
		<?php echo esc_html( $main_class_object->swfw_get_saved_styles( $css_form_id, 'submit-button', $important ) ); ?>
		<?php
		if ( empty( $get_form_options['submit-button']['border-size'] ) ) {
			echo 'border-width: 0px;';
		}
		?>
	}

	body #wpforms-<?php echo esc_html( $css_form_id ); ?> .wpforms-submit-container .wpforms-submit:hover, body #wpforms-<?php echo esc_html( $css_form_id ); ?> .wpforms-field-pagebreak button.wpforms-page-button:hover {
	<?php echo isset( $get_form_options['submit-button']['hover-color'] ) ? 'background-color:' . esc_html( $get_form_options['submit-button']['hover-color'] ) . ';' : ''; ?>
	<?php echo isset( $get_form_options['submit-button']['font-hover-color'] ) ? 'color:' . esc_html( $get_form_options['submit-button']['font-hover-color'] ) . ';' : ''; ?>
	}

	body #wpforms-<?php echo esc_html( $css_form_id ); ?> .wpforms-submit-container,
	body #wpforms-<?php echo esc_html( $css_form_id ); ?> .wpforms-field-pagebreak .wpforms-pagebreak-left{
	<?php echo empty( $get_form_options['submit-button']['text-align'] ) ? '' : 'text-align:' . esc_html( $get_form_options['submit-button']['text-align'] ) . ';'; ?>
	}
<?php } ?>


<?php if ( ! empty( $get_form_options['text-fields'] ) ) { ?>
	body #wpforms-<?php echo esc_html( $css_form_id ); ?> .wpforms-form .wpforms-field input[type=text],
	body #wpforms-<?php echo esc_html( $css_form_id ); ?> .wpforms-form .wpforms-field input[type=email],
	body #wpforms-<?php echo esc_html( $css_form_id ); ?> .wpforms-form .wpforms-field input[type=tel],
	body #wpforms-<?php echo esc_html( $css_form_id ); ?> .wpforms-form .wpforms-field input[type=url],
	body #wpforms-<?php echo esc_html( $css_form_id ); ?> .wpforms-form .wpforms-field input[type=password],
	body #wpforms-<?php echo esc_html( $css_form_id ); ?> .wpforms-form .wpforms-field input[type=number]
	{
		<?php
		echo esc_html(
			$main_class_object->swfw_get_saved_styles( $css_form_id, 'text-fields', $important )
		);
		?>
		<?php
		if ( ! empty( $get_form_options['text-fields']['max-width'] ) ) {
			echo 'max-width:100% ' . esc_html( $important ) . ';';
		}

		if ( empty( $get_form_options['text-fields']['border-size'] ) ) {
			echo 'border-width: 1px;';
		}
		?>
	}

	body #wpforms-<?php echo esc_html( $css_form_id ); ?> .wpforms-form .wpforms-field .wpforms-field-row
	{
		<?php echo ! empty( $get_form_options['text-fields']['max-width'] ) ? 'width:' . esc_html( $get_form_options['text-fields']['max-width'] ) . esc_html( $main_class_object->sfwf_add_px_to_value( $get_form_options['text-fields']['max-width'] ) ) . '; max-width:100%; ' : ''; ?>
	}

	body #wpforms-<?php echo esc_html( $css_form_id ); ?> .wpforms-form .wpforms-field-layout.wpforms-field input[type=text],
	body #wpforms-<?php echo esc_html( $css_form_id ); ?> .wpforms-form .wpforms-field-layout.wpforms-field input[type=email],
	body #wpforms-<?php echo esc_html( $css_form_id ); ?> .wpforms-form .wpforms-field-layout.wpforms-field input[type=tel],
	body #wpforms-<?php echo esc_html( $css_form_id ); ?> .wpforms-form .wpforms-field-layout.wpforms-field input[type=url],
	body #wpforms-<?php echo esc_html( $css_form_id ); ?> .wpforms-form .wpforms-field-layout.wpforms-field input[type=password],
	body #wpforms-<?php echo esc_html( $css_form_id ); ?> .wpforms-form .wpforms-field-layout.wpforms-field input[type=number]{
		width: 100%;
	}

	body #wpforms-<?php echo esc_html( $css_form_id ); ?> .wpforms-form .wpforms-field .wpforms-field-row input[type=text]{
		width: 100%;
	}

<?php } ?>


<?php
if ( ! empty( $get_form_options['layout-fields'] ) ) {
	?>
	#wpforms-<?php echo esc_html( $css_form_id ); ?> .wpforms-form .wpforms-field-layout-rows,
	#wpforms-<?php echo esc_html( $css_form_id ); ?> .wpforms-form .wpforms-field-layout-columns{
		<?php echo esc_html( $main_class_object->swfw_get_saved_styles( $css_form_id, 'layout-fields', $important ) ); ?>
	}

<?php } ?>


<?php if ( ! empty( $get_form_options['paragraph-textarea'] ) ) { ?>
	body #wpforms-<?php echo esc_html( $css_form_id ); ?> .wpforms-form .wpforms-field textarea {
		<?php echo esc_html( $main_class_object->swfw_get_saved_styles( $css_form_id, 'paragraph-textarea', $important ) ); ?>
			<?php
			if ( empty( $get_form_options['paragraph-textarea']['border-size'] ) ) {
				echo 'border-width: 1px;';
			}
			?>
	}
<?php } ?>

<?php if ( ! empty( $get_form_options['dropdown-fields'] ) ) { ?> 

	body #wpforms-<?php echo esc_html( $css_form_id ); ?> .wpforms-form .wpforms-field select {

		<?php echo esc_html( $main_class_object->swfw_get_saved_styles( $css_form_id, 'dropdown-fields', $important ) ); ?>

		<?php
		if ( ! empty( $get_form_options['dropdown-fields']['max-width'] ) ) {
			echo 'max-width:100% ' . esc_html( $important ) . ';';
		}
		if ( empty( $get_form_options['dropdown-fields']['border-size'] ) ) {
			echo 'border-width: 1px;';
		}
		?>
	}

	/* dropdown styles on modern background */
	body #wpforms-<?php echo esc_html( $css_form_id ); ?> .wpforms-form .wpforms-field-select-style-modern .choices__inner{
		<?php echo esc_html( $main_class_object->swfw_get_saved_styles( $css_form_id, 'dropdown-fields', $important ) ); ?>
		
	}

	/* background color on modern dropdown */
	<?php if ( ! empty( $get_form_options['dropdown-fields']['background-color'] ) ) { ?>

		body #wpforms-<?php echo esc_html( $css_form_id ); ?> .wpforms-form .wpforms-field-select-style-modern .choices__inner{

			background-color: <?php echo esc_html( $get_form_options['dropdown-fields']['background-color'] ); ?>;

		}

		body #wpforms-<?php echo esc_html( $css_form_id ); ?> .wpforms-form .wpforms-field-select-style-modern .choices__item {
			background-color: transparent;
		}

		<?php
	}

	if ( ! empty( $get_form_options['dropdown-fields']['font-color'] ) ) {
		?>
		body #wpforms-<?php echo esc_html( $css_form_id ); ?> .wpforms-form .wpforms-field-select-style-modern .choices__item{
			color: <?php echo esc_html( $get_form_options['dropdown-fields']['font-color'] ); ?>;
		}
		
		<?php
	}

	/* font color on modern dropdown */

}
?>

<?php if ( ! empty( $get_form_options['radio-inputs'] ) ) { ?> 
	body #wpforms-<?php echo esc_html( $css_form_id ); ?> .wpforms-form .wpforms-field-radio li label,
	body #wpforms-<?php echo esc_html( $css_form_id ); ?> .wpforms-form .wpforms-field-payment-multiple li label {
		<?php echo esc_html( $main_class_object->swfw_get_saved_styles( $css_form_id, 'radio-inputs', $important ) ); ?>
	}
<?php } ?>
<?php if ( ! empty( $get_form_options['checkbox-inputs'] ) ) { ?> 
	body #wpforms-<?php echo esc_html( $css_form_id ); ?> .wpforms-form .wpforms-field-checkbox li label {
		<?php echo esc_html( $main_class_object->swfw_get_saved_styles( $css_form_id, 'checkbox-inputs', $important ) ); ?>
	}
<?php } ?>
<?php if ( ! empty( $get_form_options['field-descriptions'] ) ) { ?> 
	body #wpforms-<?php echo esc_html( $css_form_id ); ?> .wpforms-form .wpforms-field .wpforms-field-description {

		<?php echo esc_html( $main_class_object->swfw_get_saved_styles( $css_form_id, 'field-descriptions', $important ) ); ?>
	}
<?php } ?>

<?php if ( ! empty( $get_form_options['field-labels'] ) ) { ?> 
	body #wpforms-<?php echo esc_html( $css_form_id ); ?> .wpforms-form .wpforms-field label.wpforms-field-label,
	body #wpforms-<?php echo esc_html( $css_form_id ); ?> .wpforms-form .wpforms-field .wpforms-field-label {
		<?php echo esc_html( $main_class_object->swfw_get_saved_styles( $css_form_id, 'field-labels', $important ) ); ?>
	}
<?php } ?>

<?php if ( ! empty( $get_form_options['section-break-title'] ) ) { ?> 
	body #wpforms-<?php echo esc_html( $css_form_id ); ?> .wpforms-form .gform_fields .gsection .gsection_title {
		<?php echo esc_html( $main_class_object->swfw_get_saved_styles( $css_form_id, 'section-break-title', $important ) ); ?>
	}
<?php } ?>
<?php if ( ! empty( $get_form_options['section-break-description'] ) ) { ?> 
	body #wpforms-<?php echo esc_html( $css_form_id ); ?> .wpforms-form .gform_fields .gsection .gsection_description {
		<?php echo esc_html( $main_class_object->swfw_get_saved_styles( $css_form_id, 'section-break-description', $important ) ); ?>
	}
<?php } ?>

<?php if ( ! empty( $get_form_options['confirmation-message'] ) ) { ?> 
	body #wpforms-confirmation-<?php echo esc_html( $css_form_id ); ?>  {

	<?php echo esc_html( $main_class_object->swfw_get_saved_styles( $css_form_id, 'confirmation-message', $important ) ); ?>
			<?php
			if ( empty( $get_form_options['confirmation-message']['border-size'] ) ) {
				echo 'border-width: 1px;';
			}
			?>
	}

	body #wpforms-confirmation-<?php echo esc_html( $css_form_id ); ?> p{
		text-transform: uppercase;
		text-decoration: underline;
		color: inherit;
	}

<?php } ?>
<?php if ( ! empty( $get_form_options['error-message'] ) ) { ?> 
	body #wpforms-<?php echo esc_html( $css_form_id ); ?> label.wpforms-error,
	body #wpforms-<?php echo esc_html( $css_form_id ); ?> .wpforms-error[role="alert"] {
		<?php echo esc_html( $main_class_object->swfw_get_saved_styles( $css_form_id, 'error-message', $important ) ); ?>

				<?php
				if ( empty( $get_form_options['error-message']['border-size'] ) ) {
					echo 'border-width: 1px;';
				}
				?>
	}
<?php } ?>
/* Styling for Tablets */
@media only screen and (max-width: 800px) and (min-width:481px) {
	<?php if ( sfwf_isset_checker( $get_form_options, 'form-wrapper', array( 'max-width-tab' ) ) ) { ?>
		body #wpforms-<?php echo esc_html( $css_form_id ); ?> {
		<?php echo esc_html( $main_class_object->swfw_get_saved_styles_tab( $css_form_id, 'form-wrapper', $important ) ); ?>
		}       
<?php } ?>

<?php if ( sfwf_isset_checker( $get_form_options, 'form-title', array( 'font-size-tab', 'line-height-tab' ) ) ) { ?>
		body #wpforms-<?php echo esc_html( $css_form_id ); ?> .wpforms-head-container .wpforms-title {
		<?php echo esc_html( $main_class_object->swfw_get_saved_styles_tab( $css_form_id, 'form-title', $important ) ); ?>
		}       
<?php } ?>


<?php if ( sfwf_isset_checker( $get_form_options, 'form-description', array( 'font-size-tab', 'line-height-tab' ) ) ) { ?>
		body #wpforms-<?php echo esc_html( $css_form_id ); ?> .wpforms-head-container .wpforms-description{
		<?php echo esc_html( $main_class_object->swfw_get_saved_styles_tab( $css_form_id, 'form-description', $important ) ); ?>
		}       
<?php } ?>
<?php if ( sfwf_isset_checker( $get_form_options, 'submit-button', array( 'font-size-tab', 'max-width-tab', 'height-tab', 'line-height-tab' ) ) ) { ?>
		body #wpforms-<?php echo esc_html( $css_form_id ); ?> .wpforms-submit-container .wpforms-submit,body #wpforms-<?php echo esc_html( $css_form_id ); ?> .wpforms-field-pagebreak button.wpforms-page-button {
		<?php echo esc_html( $main_class_object->swfw_get_saved_styles_tab( $css_form_id, 'submit-button', $important ) ); ?>
		}     
<?php } ?>
<?php if ( sfwf_isset_checker( $get_form_options, 'text-fields', array( 'font-size-tab', 'max-width-tab', 'line-height-tab' ) ) ) { ?>

	body #wpforms-<?php echo esc_html( $css_form_id ); ?> .wpforms-form .wpforms-field input[type=text],
		body #wpforms-<?php echo esc_html( $css_form_id ); ?> .wpforms-form .wpforms-field input[type=email],
		body #wpforms-<?php echo esc_html( $css_form_id ); ?> .wpforms-form .wpforms-field input[type=tel],
		body #wpforms-<?php echo esc_html( $css_form_id ); ?> .wpforms-form .wpforms-field input[type=url],
		body #wpforms-<?php echo esc_html( $css_form_id ); ?> .wpforms-form .wpforms-field input[type=password],
		body #wpforms-<?php echo esc_html( $css_form_id ); ?> .wpforms-form .wpforms-field input[type=number]{
			<?php echo esc_html( $main_class_object->swfw_get_saved_styles_tab( $css_form_id, 'text-fields', $important ) ); ?>
		<?php
		if ( ! empty( $get_form_options['text-fields']['max-width'] ) ) {
			echo 'max-width:100% ' . esc_html( $important ) . ';';
		}
		?>
	}
	body #wpforms-<?php echo esc_html( $css_form_id ); ?> .wpforms-form .wpforms-field .wpforms-field-row{
		<?php echo ! empty( $get_form_options['text-fields']['max-width-tab'] ) ? 'width:' . esc_html( $get_form_options['text-fields']['max-width-tab'] ) . esc_html( $main_class_object->sfwf_add_px_to_value( $get_form_options['text-fields']['max-width-tab'] ) ) . '; max-width:100% ' . esc_html( $important ) . '; ' : ''; ?>	
	}	

	body #wpforms-<?php echo esc_html( $css_form_id ); ?> .wpforms-form .wpforms-field .wpforms-field-row input{
		width: 100%;
	}
		
<?php } ?>
<?php if ( sfwf_isset_checker( $get_form_options, 'paragraph-textarea', array( 'max-width-tab' ) ) ) { ?>
		body #wpforms-<?php echo esc_html( $css_form_id ); ?> .wpforms-form .wpforms-field textarea{

		<?php echo ! empty( $get_form_options['text-fields']['max-width-tab'] ) ? 'width:' . esc_html( $get_form_options['text-fields']['max-width-tab'] ) . esc_html( $main_class_object->sfwf_add_px_to_value( $get_form_options['text-fields']['max-width-tab'] ) ) . '; max-width:100% ' . esc_html( $important ) . '; ' : ''; ?>

		<?php echo esc_html( $main_class_object->swfw_get_saved_styles_tab( $css_form_id, 'paragraph-textarea', $important ) ); ?>
		}       
<?php } ?>
<?php if ( sfwf_isset_checker( $get_form_options, 'dropdown-fields', array( 'font-size-tab', 'max-width-tab', 'line-height-tab' ) ) ) { ?>
		body #wpforms-<?php echo esc_html( $css_form_id ); ?> .wpforms-form .wpforms-field select{
		<?php echo esc_html( $main_class_object->swfw_get_saved_styles_tab( $css_form_id, 'dropdown-fields', $important ) ); ?>
		<?php echo ! empty( $get_form_options['dropdown-fields']['max-width-tab'] ) ? 'max-width:100% ' . esc_html( $important ) . '; ' : ''; ?>
		}       

<?php } ?>
<?php if ( sfwf_isset_checker( $get_form_options, 'radio-inputs', array( 'font-size-tab', 'max-width-tab', 'line-height-tab' ) ) ) { ?>
		body #wpforms-<?php echo esc_html( $css_form_id ); ?> .wpforms-form .wpforms-field-radio li label,
body #wpforms-<?php echo esc_html( $css_form_id ); ?> .wpforms-form .wpforms-field-payment-multiple li label{
		<?php echo esc_html( $main_class_object->swfw_get_saved_styles_tab( $css_form_id, 'radio-inputs', $important ) ); ?>
		}       
<?php } ?>
<?php if ( sfwf_isset_checker( $get_form_options, 'checkbox-inputs', array( 'font-size-tab', 'max-width-tab', 'line-height-tab' ) ) ) { ?>
		body #wpforms-<?php echo esc_html( $css_form_id ); ?> .wpforms-form .wpforms-field-checkbox li label {
		<?php echo esc_html( $main_class_object->swfw_get_saved_styles_tab( $css_form_id, 'checkbox-inputs', $important ) ); ?>
		}       
<?php } ?>
<?php if ( sfwf_isset_checker( $get_form_options, 'field-labels', array( 'font-size-tab', 'line-height-tab' ) ) ) { ?>
		body #wpforms-<?php echo esc_html( $css_form_id ); ?> .wpforms-form .wpforms-field label.wpforms-field-label,
		body #wpforms-<?php echo esc_html( $css_form_id ); ?> .wpforms-form .wpforms-field .wpforms-field-label{
		<?php echo esc_html( $main_class_object->swfw_get_saved_styles_tab( $css_form_id, 'field-labels', $important ) ); ?>
		}       
<?php } ?>
<?php if ( sfwf_isset_checker( $get_form_options, 'field-descriptions', array( 'font-size-tab', 'line-height-tab' ) ) ) { ?>
		body #wpforms-<?php echo esc_html( $css_form_id ); ?> .wpforms-form .wpforms-field .wpforms-field-description {
		<?php echo esc_html( $main_class_object->swfw_get_saved_styles_tab( $css_form_id, 'field-descriptions', $important ) ); ?>
		}      

<?php } ?>

<?php if ( sfwf_isset_checker( $get_form_options, 'confirmation-message', array( 'font-size-tab', 'max-width-tab', 'line-height-tab' ) ) ) { ?>
	body #wpforms-confirmation-<?php echo esc_html( $css_form_id ); ?> {
		<?php echo esc_html( $main_class_object->swfw_get_saved_styles_tab( $css_form_id, 'confirmation-message', $important ) ); ?>
		}       
<?php } ?>
<?php if ( sfwf_isset_checker( $get_form_options, 'error-message', array( 'max-width-tab' ) ) ) { ?>
	body #wpforms-<?php echo esc_html( $css_form_id ); ?> label.wpforms-error{
			<?php echo esc_html( $main_class_object->swfw_get_saved_styles_tab( $css_form_id, 'error-message', $important ) ); ?>
		}
<?php } ?>
}

@media only screen and (max-width: 480px){
	<?php if ( sfwf_isset_checker( $get_form_options, 'form-wrapper', array( 'max-width-phone' ) ) ) { ?>
		body #wpforms-<?php echo esc_html( $css_form_id ); ?> {
		<?php echo esc_html( $main_class_object->swfw_get_saved_styles_phone( $css_form_id, 'form-wrapper', $important ) ); ?>
		}       
<?php } ?>

<?php if ( sfwf_isset_checker( $get_form_options, 'form-title', array( 'font-size-phone', 'line-height-phone' ) ) ) { ?>
		body #wpforms-<?php echo esc_html( $css_form_id ); ?> .wpforms-head-container .wpforms-title {
		<?php echo esc_html( $main_class_object->swfw_get_saved_styles_phone( $css_form_id, 'form-title', $important ) ); ?>
		}       
<?php } ?>


<?php if ( sfwf_isset_checker( $get_form_options, 'form-description', array( 'font-size-phone', 'line-height-phone' ) ) ) { ?>
	body #wpforms-<?php echo esc_html( $css_form_id ); ?> .wpforms-head-container .wpforms-description{
		<?php echo esc_html( $main_class_object->swfw_get_saved_styles_phone( $css_form_id, 'form-description', $important ) ); ?>
		}
<?php } ?>
<?php if ( sfwf_isset_checker( $get_form_options, 'submit-button', array( 'font-size-phone', 'max-width-phone', 'height-phone', 'line-height-phone' ) ) ) { ?>
	body #wpforms-<?php echo esc_html( $css_form_id ); ?> .wpforms-submit-container .wpforms-submit, body #wpforms-<?php echo esc_html( $css_form_id ); ?> .wpforms-field-pagebreak button.wpforms-page-button {
		<?php echo esc_html( $main_class_object->swfw_get_saved_styles_phone( $css_form_id, 'submit-button', $important ) ); ?>
		}
<?php } ?>
<?php if ( sfwf_isset_checker( $get_form_options, 'text-fields', array( 'font-size-phone', 'max-width-phone', 'line-height-phone' ) ) ) { ?>

	body #wpforms-<?php echo esc_html( $css_form_id ); ?> .wpforms-form .wpforms-field input[type=text],
		body #wpforms-<?php echo esc_html( $css_form_id ); ?> .wpforms-form .wpforms-field input[type=email],
		body #wpforms-<?php echo esc_html( $css_form_id ); ?> .wpforms-form .wpforms-field input[type=tel],
		body #wpforms-<?php echo esc_html( $css_form_id ); ?> .wpforms-form .wpforms-field input[type=url],
		body #wpforms-<?php echo esc_html( $css_form_id ); ?> .wpforms-form .wpforms-field input[type=password],
		body #wpforms-<?php echo esc_html( $css_form_id ); ?> .wpforms-form .wpforms-field input[type=number]{
			<?php echo esc_html( $main_class_object->swfw_get_saved_styles_phone( $css_form_id, 'text-fields', $important ) ); ?>
			<?php echo ! empty( $get_form_options['text-fields']['max-width-phone'] ) ? 'max-width:100% ' . esc_html( $important ) . '; ' : ''; ?>
		} 

		body #wpforms-<?php echo esc_html( $css_form_id ); ?> .wpforms-form .wpforms-field .wpforms-field-row{
			<?php echo ! empty( $get_form_options['text-fields']['max-width-phone'] ) ? 'width:' . esc_html( $get_form_options['text-fields']['max-width-phone'] ) . esc_html( $main_class_object->sfwf_add_px_to_value( $get_form_options['text-fields']['max-width-phone'] ) ) . '; max-width:100% ' . esc_html( $important ) . '; ' : ''; ?>
		}
<?php } ?>
<?php if ( sfwf_isset_checker( $get_form_options, 'paragraph-textarea', array( 'max-width-phone' ) ) ) { ?>

	body #wpforms-<?php echo esc_html( $css_form_id ); ?> .wpforms-form .wpforms-field textarea{
		<?php echo ! empty( $get_form_options['text-fields']['max-width-phone'] ) ? 'width:' . esc_html( $get_form_options['text-fields']['max-width-phone'] ) . esc_html( $main_class_object->sfwf_add_px_to_value( $get_form_options['text-fields']['max-width-phone'] ) ) . '; max-width:100% ' . esc_html( $important ) . '; ' : ''; ?>

		<?php echo esc_html( $main_class_object->swfw_get_saved_styles_phone( $css_form_id, 'paragraph-textarea', $important ) ); ?>
		}       
<?php } ?>
<?php if ( sfwf_isset_checker( $get_form_options, 'dropdown-fields', array( 'font-size-phone', 'max-width-phone', 'line-height-phone' ) ) ) { ?>
	body #wpforms-<?php echo esc_html( $css_form_id ); ?> .wpforms-form .wpforms-field select{
		<?php echo esc_html( $main_class_object->swfw_get_saved_styles_phone( $css_form_id, 'dropdown-fields', $important ) ); ?>
		<?php echo ! empty( $get_form_options['dropdown-fields']['max-width-phone'] ) ? 'max-width:100% ' . esc_html( $important ) . '; ' : ''; ?>
		}

<?php } ?>
<?php if ( sfwf_isset_checker( $get_form_options, 'radio-inputs', array( 'font-size-phone', 'max-width-phone', 'line-height-phone' ) ) ) { ?>
		body #wpforms-<?php echo esc_html( $css_form_id ); ?> .wpforms-form .wpforms-field-radio li label,
		body #wpforms-<?php echo esc_html( $css_form_id ); ?> .wpforms-form .wpforms-field-payment-multiple li label{
			<?php echo esc_html( $main_class_object->swfw_get_saved_styles_phone( $css_form_id, 'radio-inputs', $important ) ); ?>
		}
<?php } ?>
<?php if ( sfwf_isset_checker( $get_form_options, 'checkbox-inputs', array( 'font-size-phone', 'max-width-phone', 'line-height-phone' ) ) ) { ?>
	body #wpforms-<?php echo esc_html( $css_form_id ); ?> .wpforms-form .wpforms-field-checkbox li label {
		<?php echo esc_html( $main_class_object->swfw_get_saved_styles_phone( $css_form_id, 'checkbox-inputs', $important ) ); ?>
	}       
<?php } ?>
<?php if ( sfwf_isset_checker( $get_form_options, 'field-labels', array( 'font-size-phone', 'line-height-phone' ) ) ) { ?>
		body #wpforms-<?php echo esc_html( $css_form_id ); ?> .wpforms-form .wpforms-field label.wpforms-field-label,
		body #wpforms-<?php echo esc_html( $css_form_id ); ?> .wpforms-form .wpforms-field .wpforms-field-label{
		<?php echo esc_html( $main_class_object->swfw_get_saved_styles_phone( $css_form_id, 'field-labels', $important ) ); ?>
		}       
<?php } ?>
<?php if ( sfwf_isset_checker( $get_form_options, 'field-descriptions', array( 'font-size-phone', 'line-height-phone' ) ) ) { ?>
		body #wpforms-<?php echo esc_html( $css_form_id ); ?> .wpforms-form .wpforms-field .wpforms-field-description {
		<?php echo esc_html( $main_class_object->swfw_get_saved_styles_phone( $css_form_id, 'field-descriptions', $important ) ); ?>
		}       
<?php } ?>

<?php if ( sfwf_isset_checker( $get_form_options, 'confirmation-message', array( 'font-size-phone', 'max-width-phone', 'line-height-phone' ) ) ) { ?>
	body #wpforms-confirmation-<?php echo esc_html( $css_form_id ); ?> {
			<?php echo esc_html( $main_class_object->swfw_get_saved_styles_phone( $css_form_id, 'confirmation-message', $important ) ); ?>
		}       
<?php } ?>
<?php if ( sfwf_isset_checker( $get_form_options, 'error-message', array( 'max-width-phone' ) ) ) { ?>
		body #wpforms-<?php echo esc_html( $css_form_id ); ?> label.wpforms-error{
			<?php echo esc_html( $main_class_object->swfw_get_saved_styles_phone( $css_form_id, 'error-message', $important ) ); ?>
		}       
<?php } ?>
}
/*Option to add custom CSS */
<?php
if ( isset( $get_form_options['general-settings']['custom-css'] ) ) {
	echo $get_form_options['general-settings']['custom-css'];
}
?>



		</style>
		<?php
		$styles = ob_get_contents();
		ob_end_clean();
		echo $styles;
