<?php
if ( class_exists( 'WP_Customize_Control' ) ) :
	class Themes_Pack_Custom_Control extends WP_Customize_Control {

		/**
		 * Render the control's content.
		 * Allows the content to be overriden without having to rewrite the wrapper.
		 */
		public function render_content() {
			?>
		<label>
			<h2><?php echo esc_html( $this->label ); ?></h2>
			<a href="https://wpmonks.com/downloads/theme-pack?utm_source=dashboard&utm_medium=customizer&utm_campaign=styles_layout_plugin" target="_blank"><img src="<?php echo esc_url( SFWF_URL ); ?>/css/images/theme-pack.jpg"></a>
			<h3>Get pack of beautifully crafted themes and design forms instantly</h3>
			<hr>
			</textarea>
		</label>
			<?php
		}
	}


	class Field_Icons_Custom_Control extends WP_Customize_Control {

		/**
		 * Render the control's content.
		 * Allows the content to be overriden without having to rewrite the wrapper.
		 */
		public function render_content() {
			?>
	<label>
		<h2><?php echo esc_html( $this->label ); ?></h2>
		<a href="https://wpmonks.com/downloads/field-icons?utm_source=dashboard&utm_medium=customizer&utm_campaign=styles_layout_plugin" target="_blank"><img src="<?php echo esc_url( SFWF_URL ); ?>/css/images/field-icons.jpg"></a>
		<h3>Add icons inside form fields</h3>
		<hr>
		</textarea>
	</label>
			<?php
		}
	}

	class Custom_Themes_Custom_Control extends WP_Customize_Control {

		/**
		 * Render the control's content.
		 * Allows the content to be overriden without having to rewrite the wrapper.
		 */
		public function render_content() {
			?>
	<label>
		<h2><?php echo esc_html( $this->label ); ?></h2>
		<a href="https://wpmonks.com/downloads/custom-themes/?utm_source=dashboard&utm_medium=customizer&utm_campaign=styles_layout_plugin" target="_blank"><img src="<?php echo esc_url( SFWF_URL ); ?>/css/images/custom-themes.jpg"></a>
		<h3>Save you current form style as theme and apply it on other forms</h3>
		<hr>
		</textarea>
	</label>
			<?php
		}
	}



	class Addon_Bundle_Custom_Control extends WP_Customize_Control {

		/**
		 * Render the control's content.
		 * Allows the content to be overriden without having to rewrite the wrapper.
		 */
		public function render_content() {
			?>
	<label>
		<h2><?php echo esc_html( $this->label ); ?></h2>
		<a href="https://wpmonks.com/downloads/addon-bundle/?utm_source=dashboard&utm_medium=customizer&utm_campaign=styles_layout_plugin" target="_blank"><img src="<?php echo esc_url( SFWF_URL ); ?>/css/images/addon-bundle.jpg"></a>
		<h3>Get all the addons at a special discounted price</h3>
		<hr>
		</textarea>
	</label>
			<?php
		}
	}

	class More_Addons_Custom_Control extends WP_Customize_Control {

		/**
		 * Render the control's content.
		 * Allows the content to be overriden without having to rewrite the wrapper.
		 */
		public function render_content() {
			?>
	<label>
		<h2><?php echo esc_html( $this->label ); ?></h2>
		<a href="https://wpmonks.com/gravity-forms-add-ons/?utm_source=dashboard&utm_medium=customizer&utm_campaign=styles_layout_plugin" target="_blank"><img src="<?php echo esc_url( SFWF_URL ); ?>/css/images/more-addons.jpg"></a>
		<h3>Checkout more addons</h3>
		<hr>
		</textarea>
	</label>
			<?php
		}
	}

	class Material_Design_Custom_Control extends WP_Customize_Control {

		/**
		 * Render the control's content.
		 * Allows the content to be overriden without having to rewrite the wrapper.
		 */
		public function render_content() {
			?>
	<label>
		<h2><?php echo esc_html( $this->label ); ?></h2>
		<a href="https://wpmonks.com/downloads/material-design/?utm_source=dashboard&utm_medium=customizer&utm_campaign=styles_layout_plugin" target="_blank"><img src="<?php echo esc_url( SFWF_URL ); ?>/css/images/material-design.jpg"></a>
		<h3>Apply material design on Gravity Forms with single click</h3>
		<hr>
		</textarea>
	</label>
			<?php
		}
	}

	class Tooltips_Custom_Control extends WP_Customize_Control {

		/**
		 * Render the control's content.
		 * Allows the content to be overriden without having to rewrite the wrapper.
		 */
		public function render_content() {
			?>
	<label>
		<h2><?php echo esc_html( $this->label ); ?></h2>
		<a href="https://wpmonks.com/downloads/tooltips/?utm_source=dashboard&utm_medium=customizer&utm_campaign=styles_layout_plugin" target="_blank"><img src="<?php echo esc_url( SFWF_URL ); ?>/css/images/tooltips.jpg"></a>
		<h3>Show tooltips inside Gravity Form fields</h3>
		<hr>
		</textarea>
	</label>
			<?php
		}
	}

	class Customization_Support_Custom_Control extends WP_Customize_Control {

		/**
		 * Render the control's content.
		 * Allows the content to be overriden without having to rewrite the wrapper.
		 */
		public function render_content() {
			?>
	<label>
		<h2><?php echo esc_html( $this->label ); ?></h2>
		<a href="https://wpmonks.com/contact-us/?utm_source=dashboard&utm_medium=customizer&utm_campaign=styles_layout_plugin" target="_blank"><img src="<?php echo esc_url( SFWF_URL ); ?>/css/images/support.jpg"></a>
		<h3>Contact us for custom Gravity Forms work or for any support questions</h3>
		<hr>
		</textarea>
	</label>
			<?php
		}
	}


	class WP_Customize_Label_Only extends WP_Customize_Control {
		public $type = 'label_only';

		public function render_content() {
			?>
		<label>
		<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>  
		</label>
			<?php
		}
	}
endif;
