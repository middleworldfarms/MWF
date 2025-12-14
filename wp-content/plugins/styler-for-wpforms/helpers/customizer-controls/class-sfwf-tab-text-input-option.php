<?php
/**
 * Show tab input control in customizer.
 */
class Sfwf_Tab_Text_Input_Option extends WP_Customize_Control {
	/**
	 * Type of render component.
	 *
	 * @var string
	 */
	public $type = 'text';

	/**
	 * Render Tab input html.
	 *
	 * @return void
	 */
	public function render_content() {
		?>
			<label>
			<?php if ( ! empty( $this->label ) ) : ?>
					<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
				<?php
				endif;
			if ( ! empty( $this->description ) ) :
				?>
					<span class="description customize-control-description"><?php echo esc_html( $this->description ); ?></span>
				<?php endif; ?>
			</label>
			<span class="sfwf_tab_text_input sfwf_responsive_text_input"></span>		
			<input type="text" class="sfwf_tab_text_input_control" <?php $this->input_attrs(); ?> value="<?php echo esc_attr( $this->value() ); ?>" <?php $this->link(); ?> />
			<?php
	}
}
