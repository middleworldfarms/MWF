<?php
/**
 * Show desktop input control in customizer.
 */
class Sfwf_Font_Style_Option extends WP_Customize_Control {
	/**
	 * The type of render control.
	 *
	 * @var string
	 */
	public $type = 'font_style';

	/**
	 * Render font style html.
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
			<?php
			$current_values = explode( '|', $this->value() );
			if ( empty( $this->choices ) ) {
				return;
			}
			foreach ( $this->choices as $value => $label ) :
				$checked_class = in_array( $value, $current_values ) ? ' sfwf_font_style_checked' : '';
				?>
					<span class="sfwf_font_style sfwf_font_value_
					<?php
					echo esc_attr( $value );
					echo esc_html( $checked_class );
					?>
					">
						<input type="checkbox" class="sfwf_font_style_checkbox" value="<?php echo esc_attr( $value ); ?>" <?php checked( in_array( $value, $current_values ) ); ?> />
					</span>
				<?php
			endforeach;
			?>
			<input type="hidden" class="sfwf_font_styles" <?php $this->input_attrs(); ?> value="<?php echo esc_attr( $this->value() ); ?>" <?php $this->link(); ?> />
			<?php
	}
}
