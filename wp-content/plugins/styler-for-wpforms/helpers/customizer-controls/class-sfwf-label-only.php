<?php
/**
 * Show custom label in customizer.
 */
class Sfwf_Label_Only extends WP_Customize_Control {
	/**
	 * The type of render component.
	 *
	 * @var string
	 */
	public $type = 'label_only';

	/**
	 * Render custom label html.
	 *
	 * @return void
	 */
	public function render_content() {
		?>
	<label>
		<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>  
	</label>
		<?php
	}
}
