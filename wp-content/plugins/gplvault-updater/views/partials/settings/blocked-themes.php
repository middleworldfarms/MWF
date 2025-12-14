<?php
defined( 'ABSPATH' ) || exit;
/**
 * @var array   $blocked_themes
 */

// Get all themes with their data
$all_themes = GPLVault_Helper::all_themes( false );
?>
<div class="gv-material-column-card">
	<div class="gv-material-column-header">
		<h3 class="gv-material-column-title">
			<span class="dashicons dashicons-admin-appearance"></span>
			<?php esc_html_e( 'Themes', 'gplvault' ); ?>
		</h3>
	</div>
	<div class="gv-material-column-body">
		<div class="gv-fields__container">
			<div class="gv-material-field-label">
				<span><?php esc_html_e( 'Select Themes to Exclude', 'gplvault' ); ?></span>
				<span class="gv-material-help-tip gv-has-tooltip"
						data-tippy-placement="top-start"
						data-tippy-content="<?php esc_attr_e( 'Select themes that should not receive automatic updates from GPL Vault.', 'gplvault' ); ?>"></span>
			</div>
			
			<div class="gv-checkbox-list-container" id="gv-themes-list">
				<div class="gv-checkbox-list-header">
					<div class="gv-search-box">
						<input type="text" class="gv-search-input" placeholder="<?php esc_attr_e( 'Search themes...', 'gplvault' ); ?>">
						<span class="dashicons dashicons-search"></span>
					</div>
					<div class="gv-bulk-actions">
						<button type="button" class="gv-select-all"><?php esc_html_e( 'Select All', 'gplvault' ); ?></button>
						<button type="button" class="gv-select-none"><?php esc_html_e( 'Select None', 'gplvault' ); ?></button>
					</div>
				</div>
				
				<div class="gv-checkbox-list">
					<?php
					foreach ( $all_themes as $theme_dir => $theme_data ) :
						$is_checked = in_array( $theme_dir, $blocked_themes, true );

						?>
						<div class="gv-checkbox-item <?php echo $is_checked ? 'selected' : ''; ?>">
							<label class="gv-checkbox-wrapper">
								<input type="checkbox" 
										class="gv-checkbox-input" 
										value="<?php echo esc_attr( $theme_dir ); ?>"
										<?php checked( $is_checked ); ?>>
								<span class="gv-checkbox-custom"></span>
							</label>
							
							<div class="gv-item-icon">
								<span class="dashicons dashicons-admin-appearance"></span>
							</div>
							
							<div class="gv-item-details">
								<div class="gv-item-name"><?php echo esc_html( $theme_data->get( 'Name' ) ); ?></div>
								<?php if ( $theme_data->get( 'Author' ) ) : ?>
									<div class="gv-item-meta">
										<?php
										/* translators: %s: theme author name */
										echo esc_html( sprintf( __( 'by %s', 'gplvault' ), wp_strip_all_tags( $theme_data->get( 'Author' ) ) ) );
										?>
									</div>
								<?php endif; ?>
							</div>
							
							<div class="gv-item-version">
								<?php
								/* translators: %s: theme version number */
								echo esc_html( sprintf( __( 'v%s', 'gplvault' ), $theme_data->get( 'Version' ) ) );
								?>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
				
				<div class="gv-checkbox-list-footer">
					<?php
					$total    = count( $all_themes );
					$selected = count( $blocked_themes );
					/* translators: 1: number of selected items, 2: total number of items */
					echo esc_html( sprintf( __( '%1$d of %2$d selected', 'gplvault' ), $selected, $total ) );
					?>
				</div>
			</div>
			
			<!-- Hidden input to store selected values for form submission -->
			<input type="hidden" 
					name="gv_blocked_themes[]" 
					id="gv_blocked_themes_hidden" 
					value="<?php echo esc_attr( implode( ',', $blocked_themes ) ); ?>">
			
			<div class="gv-fields__actions" style="margin-top: 20px;">
				<button
					class="gv-material-button gv-material-button-primary"
					id="themes_exclusion_btn"
					type="button"
					data-context="themes_exclusion"
					disabled
				>
					<span class="dashicons dashicons-saved"></span>
					<?php esc_html_e( 'Save Changes', 'gplvault' ); ?>
				</button>
			</div>
		</div>
	</div>
</div>
