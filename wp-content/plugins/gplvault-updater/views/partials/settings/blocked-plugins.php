<?php
defined( 'ABSPATH' ) || exit;
/**
 * @var array   $blocked_plugins
 */

// Get all plugins with their data
$all_plugins = GPLVault_Helper::all_plugins( false );
?>
<div class="gv-material-column-card">
	<div class="gv-material-column-header">
		<h3 class="gv-material-column-title">
			<span class="dashicons dashicons-admin-plugins"></span>
			<?php esc_html_e( 'Plugins', 'gplvault' ); ?>
		</h3>
	</div>
	<div class="gv-material-column-body">
		<div class="gv-fields__container">
			<div class="gv-material-field-label">
				<span><?php esc_html_e( 'Select Plugins to Exclude', 'gplvault' ); ?></span>
				<span class="gv-material-help-tip gv-has-tooltip"
						data-tippy-placement="top-start"
						data-tippy-content="<?php esc_attr_e( 'Select plugins that should not receive automatic updates from GPL Vault.', 'gplvault' ); ?>"></span>
			</div>
			
			<div class="gv-checkbox-list-container" id="gv-plugins-list">
				<div class="gv-checkbox-list-header">
					<div class="gv-search-box">
						<input type="text" class="gv-search-input" placeholder="<?php esc_attr_e( 'Search plugins...', 'gplvault' ); ?>">
						<span class="dashicons dashicons-search"></span>
					</div>
					<div class="gv-bulk-actions">
						<button type="button" class="gv-select-all"><?php esc_html_e( 'Select All', 'gplvault' ); ?></button>
						<button type="button" class="gv-select-none"><?php esc_html_e( 'Select None', 'gplvault' ); ?></button>
					</div>
				</div>
				
				<div class="gv-checkbox-list">
					<?php
					foreach ( $all_plugins as $plugin_file => $plugin_data ) :
						$is_checked  = in_array( $plugin_file, $blocked_plugins, true );
						$plugin_slug = dirname( $plugin_file );
						if ( $plugin_slug === '.' ) {
							$plugin_slug = basename( $plugin_file, '.php' );
						}

						?>
						<div class="gv-checkbox-item <?php echo $is_checked ? 'selected' : ''; ?>">
							<label class="gv-checkbox-wrapper">
								<input type="checkbox" 
										class="gv-checkbox-input" 
										value="<?php echo esc_attr( $plugin_file ); ?>"
										<?php checked( $is_checked ); ?>>
								<span class="gv-checkbox-custom"></span>
							</label>
							
							<div class="gv-item-icon">
								<span class="dashicons dashicons-admin-plugins"></span>
							</div>
							
							<div class="gv-item-details">
								<div class="gv-item-name"><?php echo esc_html( $plugin_data['Name'] ); ?></div>
								<?php if ( ! empty( $plugin_data['Author'] ) ) : ?>
									<div class="gv-item-meta">
										<?php
										/* translators: %s: plugin author name */
										echo esc_html( sprintf( __( 'by %s', 'gplvault' ), wp_strip_all_tags( $plugin_data['Author'] ) ) );
										?>
									</div>
								<?php endif; ?>
							</div>
							
							<div class="gv-item-version">
								<?php
								/* translators: %s: plugin version number */
								echo esc_html( sprintf( __( 'v%s', 'gplvault' ), $plugin_data['Version'] ) );
								?>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
				
				<div class="gv-checkbox-list-footer">
					<?php
					$total    = count( $all_plugins );
					$selected = count( $blocked_plugins );
					/* translators: 1: number of selected items, 2: total number of items */
					echo esc_html( sprintf( __( '%1$d of %2$d selected', 'gplvault' ), $selected, $total ) );
					?>
				</div>
			</div>
			
			<!-- Hidden input to store selected values for form submission -->
			<input type="hidden" 
					name="gv_blocked_plugins[]" 
					id="gv_blocked_plugins_hidden" 
					value="<?php echo esc_attr( implode( ',', $blocked_plugins ) ); ?>">
			
			<div class="gv-fields__actions" style="margin-top: 20px;">
				<button
					class="gv-material-button gv-material-button-primary"
					id="plugins_exclusion_btn"
					type="button"
					data-context="plugins_exclusion"
					disabled
				>
					<span class="dashicons dashicons-saved"></span>
					<?php esc_html_e( 'Save Changes', 'gplvault' ); ?>
				</button>
			</div>
		</div>
	</div>
</div>
