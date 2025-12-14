<?php
defined( 'ABSPATH' ) || exit;
/** @var GPLVault_Settings_Manager $settings_manager */
/** @var $log_files */
$is_license_activated = $settings_manager->license_is_activated();
$settings_url         = GPLVault_Admin::admin_links( 'settings' );

// Get active tab
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Tab parameter is used for UI state only, no actions performed
$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'logs';

// Get environment information
function gv_get_environment_info() {
	global $wpdb;

	// WordPress Environment
	$wp_info = array(
		'WordPress Version' => get_bloginfo( 'version' ),
		'Site URL'          => get_site_url(),
		'Home URL'          => get_home_url(),
		'Is Multisite'      => is_multisite() ? 'Yes' : 'No',
		'WP Debug Mode'     => defined( 'WP_DEBUG' ) && WP_DEBUG ? 'Enabled' : 'Disabled',
		'WP Memory Limit'   => WP_MEMORY_LIMIT,
	);

	// Server Environment
	$server_info = array(
		'PHP Version'            => phpversion(),
		'MySQL Version'          => $wpdb->db_version(),
		'Web Server'             => isset( $_SERVER['SERVER_SOFTWARE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : '-',
		'PHP Memory Limit'       => ini_get( 'memory_limit' ),
		'PHP Max Execution Time' => ini_get( 'max_execution_time' ) . ' seconds',
		'PHP Post Max Size'      => ini_get( 'post_max_size' ),
		'PHP Upload Max Size'    => ini_get( 'upload_max_filesize' ),
		'PHP Max Input Vars'     => ini_get( 'max_input_vars' ),
		'PHP Time Zone'          => date_default_timezone_get(),
	);

	// Active Theme
	$active_theme = wp_get_theme();
	$theme_info   = array(
		'Name'            => $active_theme->get( 'Name' ),
		'Version'         => $active_theme->get( 'Version' ),
		'Author'          => $active_theme->get( 'Author' ),
		'Theme Directory' => $active_theme->get_stylesheet_directory(),
	);

	// Active Plugins
	$active_plugins = get_option( 'active_plugins' );
	$plugins_info   = array();

	if ( ! empty( $active_plugins ) ) {
		foreach ( $active_plugins as $plugin ) {
			$plugin_data    = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin );
			$plugins_info[] = $plugin_data['Name'] . ' ' . $plugin_data['Version'];
		}
	}

	// File Permissions
	$upload_dir       = wp_upload_dir();
	$permissions_info = array(
		'WordPress Root'       => substr( sprintf( '%o', fileperms( ABSPATH ) ), -4 ),
		'wp-content Directory' => substr( sprintf( '%o', fileperms( WP_CONTENT_DIR ) ), -4 ),
		'Plugins Directory'    => substr( sprintf( '%o', fileperms( WP_PLUGIN_DIR ) ), -4 ),
		'Uploads Directory'    => substr( sprintf( '%o', fileperms( $upload_dir['basedir'] ) ), -4 ),
	);

	// GPLVault Specific
	$gplvault_info = array(
		'Plugin Version'         => defined( 'GV_UPDATER_VERSION' ) ? GV_UPDATER_VERSION : '-',
		'Log Directory'          => defined( 'GV_UPDATER_LOG_DIR' ) ? GV_UPDATER_LOG_DIR : '-',
		'Log Directory Writable' => defined( 'GV_UPDATER_LOG_DIR' ) && is_writable( GV_UPDATER_LOG_DIR ) ? 'Yes' : 'No', // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_is_writable -- Used for environment info display only
	);

	// WP Cron Status
	$cron_status = 'Active';
	if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) {
		$cron_status = 'Disabled via DISABLE_WP_CRON constant';
	}

	// Check for missed cron events
	$cron        = _get_cron_array();
	$missed_cron = false;
	$now         = time();

	if ( is_array( $cron ) ) {
		foreach ( $cron as $timestamp => $hooks ) {
			if ( $timestamp < $now ) {
				$missed_cron = true;
				break;
			}
		}
	}

	if ( $missed_cron ) {
		$cron_status .= ' (Missed scheduled events detected)';
	}

	$wp_info['WP Cron'] = $cron_status;

	return array(
		'WordPress Environment' => $wp_info,
		'Server Environment'    => $server_info,
		'Active Theme'          => $theme_info,
		'Active Plugins'        => $plugins_info,
		'File Permissions'      => $permissions_info,
		'GPLVault Information'  => $gplvault_info,
	);
}

$environment_info = gv_get_environment_info();

// Function to check environment values for potential issues
function gv_check_environment_issues( $key, $value ) {
	$issues = array();

	// Check PHP Version
	if ( $key === 'PHP Version' ) {
		$php_version = floatval( $value );
		if ( $php_version < 7.4 ) {
			$issues[] = array(
				'level'   => 'error',
				'message' => __( 'PHP version is below 7.4. WordPress recommends PHP 7.4 or higher.', 'gplvault' ),
			);
		} elseif ( $php_version < 8.0 ) {
			$issues[] = array(
				'level'   => 'warning',
				'message' => __( 'Consider upgrading to PHP 8.0 or higher for better performance.', 'gplvault' ),
			);
		}
	}

	// Check Memory Limits
	if ( $key === 'WP Memory Limit' || $key === 'PHP Memory Limit' ) {
		$memory       = $value;
		$memory_in_mb = 0;

		// Convert to MB for comparison
		if ( preg_match( '/^(\d+)(M|G)?/i', $memory, $matches ) ) {
			$memory_in_mb = intval( $matches[1] );
			if ( isset( $matches[2] ) && strtoupper( $matches[2] ) === 'G' ) {
				$memory_in_mb *= 1024;
			}
		}

		if ( $memory_in_mb < 64 ) {
			$issues[] = array(
				'level'   => 'error',
				'message' => sprintf( __( 'Memory limit is below 64MB. WordPress recommends at least 64MB, but 256MB or higher is preferred.', 'gplvault' ) ),
			);
		} elseif ( $memory_in_mb < 256 ) {
			$issues[] = array(
				'level'   => 'warning',
				'message' => __( 'Consider increasing memory limit to 256MB or higher for optimal performance.', 'gplvault' ),
			);
		}
	}

	// Check Max Execution Time
	if ( $key === 'PHP Max Execution Time' ) {
		$time = intval( $value );
		if ( $time < 30 && $time > 0 ) {
			$issues[] = array(
				'level'   => 'warning',
				'message' => __( 'Max execution time is below 30 seconds. This may cause timeouts during updates.', 'gplvault' ),
			);
		}
	}

	// Check File Permissions
	if ( strpos( $key, 'Directory' ) !== false && strpos( $key, 'Directory Writable' ) === false ) {
		$perms = $value;
		if ( $perms === '0777' ) {
			$issues[] = array(
				'level'   => 'warning',
				'message' => __( 'Directory permissions are too permissive (777). Consider using 755 for better security.', 'gplvault' ),
			);
		}
	}

	// Check WP Cron
	if ( $key === 'WP Cron' ) {
		if ( strpos( $value, 'Disabled' ) !== false ) {
			$issues[] = array(
				'level'   => 'warning',
				'message' => __( 'WP Cron is disabled. Scheduled tasks may not run automatically.', 'gplvault' ),
			);
		}
		if ( strpos( $value, 'Missed scheduled events' ) !== false ) {
			$issues[] = array(
				'level'   => 'error',
				'message' => __( 'Missed cron events detected. This may indicate performance issues.', 'gplvault' ),
			);
		}
	}

	// Check Log Directory Writable
	if ( $key === 'Log Directory Writable' && $value === 'No' ) {
		$issues[] = array(
			'level'   => 'error',
			'message' => __( 'Log directory is not writable. Logging functionality will not work.', 'gplvault' ),
		);
	}

	// Check WP Debug Mode
	if ( $key === 'WP Debug Mode' && $value === 'Enabled' ) {
		$issues[] = array(
			'level'   => 'warning',
			'message' => __( 'Debug mode is enabled. This should be disabled on production sites.', 'gplvault' ),
		);
	}

	// Check PHP Max Input Vars
	if ( $key === 'PHP Max Input Vars' ) {
		$vars = intval( $value );
		if ( $vars < 1000 ) {
			$issues[] = array(
				'level'   => 'warning',
				'message' => __( 'Max input vars is below 1000. This may cause issues with large forms.', 'gplvault' ),
			);
		}
	}

	return $issues;
}

// Collect all issues for summary
$all_issues = array();
?>
<style>
/* Material Design inspired styles for logs page */
:root {
	--gv-material-primary: #1e88e5;
	--gv-material-primary-dark: #1565c0;
	--gv-material-shadow: 0 2px 5px 0 rgba(0,0,0,0.16), 0 2px 10px 0 rgba(0,0,0,0.12);
	--gv-material-radius: 8px;
}

/* Tab styles */
.gv-material-tabs {
	display: flex;
	margin-bottom: 24px;
	border-bottom: 1px solid #e0e0e0;
}

.gv-material-tab {
	padding: 12px 24px;
	font-size: 15px;
	font-weight: 500;
	color: #555;
	cursor: pointer;
	border-bottom: 2px solid transparent;
	transition: all 0.3s ease;
	text-decoration: none;
}

.gv-material-tab:hover {
	color: var(--gv-material-primary);
	background-color: rgba(0, 0, 0, 0.03);
}

.gv-material-tab.active {
	color: var(--gv-material-primary);
	border-bottom: 2px solid var(--gv-material-primary);
}

.gv-material-tab-content {
	display: none;
}

.gv-material-tab-content.active {
	display: block;
}

/* Environment info styles */
.gv-material-env-section {
	margin-bottom: 24px;
}

.gv-material-env-section h3 {
	font-size: 16px;
	font-weight: 600;
	margin-bottom: 12px;
	color: #333;
	padding-bottom: 8px;
	border-bottom: 1px solid #e0e0e0;
}

.gv-material-env-table {
	width: 100%;
	border-collapse: collapse;
	margin-bottom: 16px;
	font-size: 14px;
}

.gv-material-env-table th,
.gv-material-env-table td {
	padding: 8px 12px;
	text-align: left;
	border-bottom: 1px solid #f0f0f0;
}

.gv-material-env-table th {
	font-weight: 600;
	width: 30%;
}

.gv-material-env-table tr:nth-child(even) {
	background-color: #f9f9f9;
}

/* Issue highlighting styles */
.gv-material-env-table tr.gv-env-warning {
	background-color: #fff3cd !important;
}

.gv-material-env-table tr.gv-env-error {
	background-color: #f8d7da !important;
}

.gv-material-env-table tr.gv-env-warning td,
.gv-material-env-table tr.gv-env-error td {
	position: relative;
	padding-right: 30px;
}

.gv-env-indicator {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	width: 20px;
	height: 20px;
	border-radius: 50%;
	margin-left: 8px;
	font-size: 12px;
	font-weight: bold;
	cursor: help;
}

.gv-env-indicator.warning {
	background-color: #ffc107;
	color: #000;
}

.gv-env-indicator.error {
	background-color: #dc3545;
	color: #fff;
}

.gv-env-note {
	display: block;
	font-size: 12px;
	color: #666;
	margin-top: 4px;
	font-style: italic;
}

.gv-env-note.warning {
	color: #856404;
}

.gv-env-note.error {
	color: #721c24;
}

/* Summary box */
.gv-env-summary {
	background: #f8f9fa;
	border: 1px solid #dee2e6;
	border-radius: var(--gv-material-radius);
	padding: 16px;
	margin-bottom: 24px;
}

.gv-env-summary.has-issues {
	border-color: #ffc107;
	background: #fff3cd;
}

.gv-env-summary.has-errors {
	border-color: #dc3545;
	background: #f8d7da;
}

.gv-env-summary h4 {
	margin: 0 0 8px 0;
	font-size: 16px;
	font-weight: 600;
}

.gv-env-summary ul {
	margin: 0;
	padding-left: 20px;
}

.gv-env-summary li {
	margin-bottom: 4px;
}

.gv-material-env-content {
	background: #f8f8f8;
	border: 1px solid #e0e0e0;
	border-radius: var(--gv-material-radius);
	padding: 24px;
	margin: 0;
	width: 100%;
	height: 100%;
	font-family: monospace;
	font-size: 14px;
	line-height: 1.6;
	white-space: pre-wrap;
	overflow-x: auto;
	box-sizing: border-box;
	box-shadow: inset 0 0 5px rgba(0,0,0,0.05);
	color: #333;
}

.gv-material-card {
	background: #fff;
	border-radius: var(--gv-material-radius);
	box-shadow: var(--gv-material-shadow);
	padding: 32px;
	margin-bottom: 32px;
	transition: box-shadow 0.3s ease;
	border-top: 4px solid var(--gv-material-primary);
}

.gv-material-card:hover {
	box-shadow: 0 5px 11px 0 rgba(0,0,0,0.18), 0 4px 15px 0 rgba(0,0,0,0.15);
}

.gv-material-instruction {
	font-size: 16px;
	color: #444;
	margin-bottom: 24px;
}

.gv-material-select-container {
	display: flex;
	flex-wrap: wrap;
	gap: 16px;
	align-items: center;
	margin-bottom: 24px;
}

.gv-material-select-label {
	font-weight: 600;
	margin-right: 8px;
}

.gv-material-actions {
	display: flex;
	flex-wrap: wrap;
	gap: 12px;
	align-items: center;
	margin-bottom: 20px;
}

.gv-material-log-name {
	font-weight: 600;
	margin-right: 12px;
}

.gv-material-log-viewer {
	width: 100%;
	height: 70vh;
	max-width: 100%;
	max-height: 70vh;
	overflow: auto;
	box-sizing: border-box;
	margin-bottom: 24px;
}

.gv-material-log-content {
	background: #f8f8f8;
	background-image: linear-gradient(rgba(255, 255, 255, 0.7) 1px, transparent 1px);
	background-size: 100% 26px;
	border: 1px solid #e0e0e0;
	border-radius: var(--gv-material-radius);
	padding: 24px;
	margin: 0;
	width: 100%;
	height: 100%;
	font-family: monospace;
	font-size: 15px;
	line-height: 1.7;
	white-space: pre;
	overflow-x: auto;
	box-sizing: border-box;
	box-shadow: inset 0 0 5px rgba(0,0,0,0.05);
	color: #333;
}

.gv-material-button {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	height: 44px;
	padding: 0 20px;
	border-radius: 4px;
	font-weight: 500;
	text-decoration: none;
	cursor: pointer;
	transition: all 0.2s ease;
	box-shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24);
}

.gv-material-button .dashicons {
	margin-right: 8px;
}

.gv-material-button-primary {
	background-color: var(--gv-material-primary);
	color: white;
	border: none;
}

.gv-material-button-primary:hover, 
.gv-material-button-primary:focus {
	background-color: var(--gv-material-primary-dark);
	color: white;
}

.gv-material-button-outline {
	background-color: transparent;
	color: var(--gv-material-primary);
	border: 1px solid var(--gv-material-primary);
}

.gv-material-button-outline:hover,
.gv-material-button-outline:focus {
	background-color: rgba(30, 136, 229, 0.08);
	color: var(--gv-material-primary-dark);
	border-color: var(--gv-material-primary-dark);
}

.gv-material-button-danger {
	background-color: #f44336;
	color: white;
	border: none;
}

.gv-material-button-danger:hover,
.gv-material-button-danger:focus {
	background-color: #d32f2f;
	color: white;
}

.gv-material-copied {
	color: #4caf50;
	font-weight: 500;
	margin-left: 10px;
	display: none;
}

@media (max-width: 782px) {
	.gv-material-select-container {
		flex-direction: column;
		align-items: flex-start;
	}
	
	.gv-material-select-container select {
		width: 100%;
	}
	
	.gv-material-actions {
		flex-direction: column;
		align-items: flex-start;
	}
	
	.gv-material-button {
		width: 100%;
	}
}

/* Quick actions styles */
.gv-material-quick-actions {
	display: flex;
	gap: 16px;
	align-items: center;
	margin-bottom: 32px;
	padding: 20px;
	background: #f8f9fa;
	border-radius: var(--gv-material-radius);
	border: 1px solid #e0e0e0;
}

.gv-button-large {
	height: auto;
	padding: 12px 24px;
}

.gv-button-text {
	display: flex;
	flex-direction: column;
	align-items: flex-start;
	text-align: left;
}

.gv-button-text strong {
	font-size: 16px;
	margin-bottom: 2px;
}

.gv-button-text small {
	font-size: 12px;
	font-weight: normal;
	opacity: 0.9;
}

/* Divider styles */
.gv-material-divider {
	position: relative;
	text-align: center;
	margin: 32px 0 24px;
}

.gv-material-divider::before {
	content: '';
	position: absolute;
	top: 50%;
	left: 0;
	right: 0;
	height: 1px;
	background: #e0e0e0;
}

.gv-material-divider span {
	position: relative;
	display: inline-block;
	padding: 0 16px;
	background: #fff;
	color: #666;
	font-size: 14px;
	font-style: italic;
}

@media (max-width: 782px) {
	.gv-material-quick-actions {
		flex-direction: column;
		align-items: stretch;
	}
	
	.gv-material-quick-actions .gv-material-button {
		width: 100%;
	}
}
</style>

<div class="wrap gv-wrapper gv-wrapper-logs" id="gv_logs_wrapper" style="max-width:1400px;margin: 0 auto 40px auto;">
	<div class="gv-material-card">
		<div class="gv-material-tabs">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . GPLVault_Admin::SLUG_LOGS . '&tab=logs' ) ); ?>" class="gv-material-tab <?php echo $active_tab === 'logs' ? 'active' : ''; ?>">
				<span class="dashicons dashicons-media-text" style="margin-right:8px;"></span>
				<?php esc_html_e( 'Logs', 'gplvault' ); ?>
			</a>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . GPLVault_Admin::SLUG_LOGS . '&tab=environment' ) ); ?>" class="gv-material-tab <?php echo $active_tab === 'environment' ? 'active' : ''; ?>">
				<span class="dashicons dashicons-info" style="margin-right:8px;"></span>
				<?php esc_html_e( 'Environment', 'gplvault' ); ?>
			</a>
		</div>
		
		<div class="gv-material-tab-content <?php echo $active_tab === 'logs' ? 'active' : ''; ?>" id="tab-logs">
			<p class="gv-material-instruction">
				<?php esc_html_e( 'If support requests your log files, we recommend using the "Get All Logs" button to download all logs in a single file.', 'gplvault' ); ?>
			</p>
			<?php if ( $log_files ) : ?>
				<!-- Quick actions for all logs -->
				<div class="gv-material-quick-actions">
					<button type="button" class="gv-material-button gv-material-button-primary gv-button-large" id="gv-download-all-logs-btn">
						<span class="dashicons dashicons-download"></span>
						<span class="gv-button-text">
							<strong><?php esc_html_e( 'Get All Logs', 'gplvault' ); ?></strong>
							<small><?php esc_html_e( 'Download all logs in one file', 'gplvault' ); ?></small>
						</span>
					</button>
					<button type="button" class="gv-material-button gv-material-button-outline" id="gv-copy-all-logs-btn">
						<span class="dashicons dashicons-clipboard"></span>
						<?php esc_html_e( 'Copy All to Clipboard', 'gplvault' ); ?>
					</button>
					<span id="gv-copy-all-logs-success" class="gv-material-copied"><?php esc_html_e( 'Copied!', 'gplvault' ); ?></span>
				</div>
				
				<div class="gv-material-divider">
					<span><?php esc_html_e( 'Or view individual log files', 'gplvault' ); ?></span>
				</div>
				<form action="<?php echo esc_url( admin_url( 'admin.php?page=' . GPLVault_Admin::SLUG_LOGS . '&tab=logs' ) ); ?>" method="post" class="gv-material-select-container">
					<?php wp_nonce_field( 'gv_view_log', 'gv_log_nonce' ); ?>
				<label for="gv_log_file" class="gv-material-select-label"><?php esc_html_e( 'Select a log file', 'gplvault' ); ?></label>
				<select class="gv-select2" id="gv_log_file" name="gv_log_file" style="min-width:320px;max-width:40vw;">
					<?php foreach ( $log_files as $log_key => $log_file ) : ?>
						<?php
						$timestamp = filemtime( GV_UPDATER_LOG_DIR . $log_file );
						$date      = sprintf(
							/* translators: 1: last access date 2: last access time 3: last access timezone abbreviation */
							__( '%1$s at %2$s %3$s', 'gplvault' ),
							wp_date( gv_date_format(), $timestamp ),
							wp_date( gv_time_format(), $timestamp ),
							wp_date( 'T', $timestamp )
						);
						?>
						<option value="<?php echo esc_attr( $log_key ); ?>" <?php selected( sanitize_title( $active_log ), $log_key ); ?>>
							<?php echo esc_html( $log_file ); ?> (<?php echo esc_html( $date ); ?>)
						</option>
					<?php endforeach; ?>
				</select>
				<button type="submit" class="gv-material-button gv-material-button-primary" value="<?php esc_attr_e( 'View', 'gplvault' ); ?>">
					<span class="dashicons dashicons-visibility"></span>
					<?php esc_html_e( 'View', 'gplvault' ); ?>
				</button>
			</form>
				<?php if ( ! empty( $active_log ) ) : ?>
				<div class="gv-material-actions">
					<span class="gv-material-log-name"><?php echo esc_html( $active_log ); ?></span>
					<a class="gv-material-button gv-material-button-primary" href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'gv_log_download' => sanitize_title( $active_log ) ), admin_url( 'admin.php?page=' . GPLVault_Admin::SLUG_LOGS ) ), 'gv_download_log' ) ); ?>">
						<span class="dashicons dashicons-download"></span>
						<?php esc_html_e( 'Download log', 'gplvault' ); ?>
					</a>
					<button type="button" class="gv-material-button gv-material-button-outline" id="gv-copy-log-btn" data-log-target="gv-log-content">
						<span class="dashicons dashicons-clipboard"></span>
						<?php esc_html_e( 'Copy to clipboard', 'gplvault' ); ?>
					</button>
					<a class="gv-material-button gv-material-button-danger" href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'gv_log_remove' => sanitize_title( $active_log ) ), admin_url( 'admin.php?page=' . GPLVault_Admin::SLUG_LOGS ) ), 'gv_remove_log' ) ); ?>">
						<span class="dashicons dashicons-trash"></span>
						<?php esc_html_e( 'Delete log', 'gplvault' ); ?>
					</a>
					<span id="gv-copy-log-success" class="gv-material-copied"><?php esc_html_e( 'Copied!', 'gplvault' ); ?></span>
				</div>
				<div class="gv-material-log-viewer">
                    <pre id="gv-log-content" class="gv-material-log-content"><?php echo esc_html( file_get_contents( GV_UPDATER_LOG_DIR . $active_log ) ); // @phpcs:ignore ?></pre>
				</div>
				<script>
				(function(){
					var btn = document.getElementById('gv-copy-log-btn');
					var log = document.getElementById('gv-log-content');
					var success = document.getElementById('gv-copy-log-success');
					// phpcs:ignore WordPress.PHP.YodaConditions.NotYoda -- JavaScript code, not PHP
					if(btn && log && success){
						btn.addEventListener('click', function(){
							var text = log.innerText || log.textContent;
							// phpcs:ignore WordPress.PHP.YodaConditions.NotYoda -- JavaScript code, not PHP
							if(navigator.clipboard){
								navigator.clipboard.writeText(text).then(function(){
									success.style.display = 'inline';
									setTimeout(function(){ success.style.display = 'none'; }, 1800);
								});
							}else{
								// fallback for old browsers
								var range = document.createRange();
								range.selectNodeContents(log);
								var sel = window.getSelection();
								sel.removeAllRanges();
								sel.addRange(range);
								try {
									document.execCommand('copy');
									success.style.display = 'inline';
									setTimeout(function(){ success.style.display = 'none'; }, 1800);
								} catch(e){}
								sel.removeAllRanges();
							}
						});
					}
				})();
				</script>
				<?php endif; ?>
				
				<!-- Script for all logs functionality -->
				<script>
				(function(){
					var downloadAllBtn = document.getElementById('gv-download-all-logs-btn');
					var copyAllBtn = document.getElementById('gv-copy-all-logs-btn');
					var copyAllSuccess = document.getElementById('gv-copy-all-logs-success');
					
					// Store log files data from PHP
					var logFiles = <?php echo wp_json_encode( array_values( $log_files ) ); ?>;
					var logDir = <?php echo wp_json_encode( GV_UPDATER_LOG_DIR ); ?>;
					
					// Function to fetch all log contents via AJAX
					function fetchAllLogs(callback) {
						var allLogsContent = '';
						var logsProcessed = 0;
						
						logFiles.forEach(function(logFile, index) {
							// Read file content via AJAX
							var xhr = new XMLHttpRequest();
							xhr.open('GET', '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>?action=gv_read_log_file&log_file=' + encodeURIComponent(logFile) + '&_wpnonce=<?php echo esc_attr( wp_create_nonce( 'gv_read_log' ) ); ?>', true);
							xhr.onload = function() {
								if (xhr.status === 200) {
									var separator = '========================================\n';
									var header = 'LOG FILE: ' + logFile + '\n';
									allLogsContent += separator + header + separator + '\n' + xhr.responseText + '\n\n';
								}
								
								logsProcessed++;
								if (logsProcessed === logFiles.length) {
									callback(allLogsContent);
								}
							};
							xhr.send();
						});
					}
					
					// Download all logs
					if(downloadAllBtn) {
						downloadAllBtn.addEventListener('click', function() {
							// Create a form to submit for download
							var form = document.createElement('form');
							form.method = 'POST';
							form.action = '<?php echo esc_url( admin_url( 'admin.php?page=' . GPLVault_Admin::SLUG_LOGS ) ); ?>';
							
							var nonceField = document.createElement('input');
							nonceField.type = 'hidden';
							nonceField.name = '_wpnonce';
							nonceField.value = '<?php echo esc_attr( wp_create_nonce( 'gv_download_all_logs' ) ); ?>';
							form.appendChild(nonceField);
							
							var actionField = document.createElement('input');
							actionField.type = 'hidden';
							actionField.name = 'gv_download_all_logs';
							actionField.value = '1';
							form.appendChild(actionField);
							
							document.body.appendChild(form);
							form.submit();
							document.body.removeChild(form);
						});
					}
					
					// Copy all logs
					if(copyAllBtn && copyAllSuccess) {
						copyAllBtn.addEventListener('click', function() {
							// Read all log files from PHP
							var allLogsContent = '';
							var logsData = [
								<?php
								foreach ( $log_files as $log_file ) {
									// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Reading local log file
									$content = file_get_contents( GV_UPDATER_LOG_DIR . $log_file );
									echo '{';
									echo 'filename: ' . wp_json_encode( $log_file ) . ',';
									echo 'content: ' . wp_json_encode( $content );
									echo '},';
								}
								?>
							];
							
							// Combine all logs with separators
							logsData.forEach(function(log) {
								var separator = '========================================\n';
								var header = 'LOG FILE: ' + log.filename + '\n';
								allLogsContent += separator + header + separator + '\n' + log.content + '\n\n';
							});
							
							// Copy to clipboard
							if(navigator.clipboard) {
								navigator.clipboard.writeText(allLogsContent).then(function() {
									copyAllSuccess.style.display = 'inline';
									setTimeout(function() { copyAllSuccess.style.display = 'none'; }, 1800);
								});
							} else {
								// Fallback for older browsers
								var textarea = document.createElement('textarea');
								textarea.value = allLogsContent;
								textarea.style.position = 'fixed';
								textarea.style.opacity = '0';
								document.body.appendChild(textarea);
								textarea.select();
								
								try {
									document.execCommand('copy');
									copyAllSuccess.style.display = 'inline';
									setTimeout(function() { copyAllSuccess.style.display = 'none'; }, 1800);
								} catch(e) {}
								
								document.body.removeChild(textarea);
							}
						});
					}
				})();
				</script>
			<?php else : ?>
				<div class="updated inline gv-material-instruction"><p><?php esc_html_e( 'There are currently no logs to view.', 'gplvault' ); ?></p></div>
			<?php endif; ?>
		</div>
		
		<div class="gv-material-tab-content <?php echo $active_tab === 'environment' ? 'active' : ''; ?>" id="tab-environment">
			<p class="gv-material-instruction">
				<?php esc_html_e( 'If support requests environment information, please use the buttons below to copy or download the information and send it as instructed.', 'gplvault' ); ?>
			</p>
			
			<?php
			// Display summary box if there are issues
			if ( ! empty( $all_issues ) ) :
				$has_errors    = false;
				$error_count   = 0;
				$warning_count = 0;

				foreach ( $all_issues as $issue ) {
					if ( $issue['level'] === 'error' ) {
						$has_errors = true;
						++$error_count;
					} else {
						++$warning_count;
					}
				}

				$summary_class = $has_errors ? 'has-errors' : 'has-issues';
				?>
				<div class="gv-env-summary <?php echo esc_attr( $summary_class ); ?>">
					<h4>
						<?php
						if ( $has_errors ) {
							esc_html_e( 'Environment Issues Detected', 'gplvault' );
						} else {
							esc_html_e( 'Environment Warnings', 'gplvault' );
						}
						?>
					</h4>
					<p>
						<?php
						$summary_parts = array();
						if ( $error_count > 0 ) {
							/* translators: %d: number of errors found in environment check */
							$summary_parts[] = sprintf( _n( '%d error', '%d errors', $error_count, 'gplvault' ), $error_count );
						}
						if ( $warning_count > 0 ) {
							/* translators: %d: number of warnings found in environment check */
							$summary_parts[] = sprintf( _n( '%d warning', '%d warnings', $warning_count, 'gplvault' ), $warning_count );
						}
						/* translators: %s: list of issues found in environment check, separated by "and" */
						echo esc_html( sprintf( __( 'Found %s in your environment configuration:', 'gplvault' ), implode( ' and ', $summary_parts ) ) );
						?>
					</p>
					<ul>
						<?php
						// Show errors first
						foreach ( $all_issues as $issue ) {
							if ( $issue['level'] === 'error' ) {
								echo '<li><strong>' . esc_html( $issue['key'] ) . '</strong>: ' . esc_html( $issue['message'] ) . '</li>';
							}
						}
						// Then warnings
						foreach ( $all_issues as $issue ) {
							if ( $issue['level'] === 'warning' ) {
								echo '<li><strong>' . esc_html( $issue['key'] ) . '</strong>: ' . esc_html( $issue['message'] ) . '</li>';
							}
						}
						?>
					</ul>
				</div>
			<?php endif; ?>
			
			<div class="gv-material-actions">
				<button type="button" class="gv-material-button gv-material-button-primary" id="gv-download-env-btn">
					<span class="dashicons dashicons-download"></span>
					<?php esc_html_e( 'Download environment info', 'gplvault' ); ?>
				</button>
				<button type="button" class="gv-material-button gv-material-button-outline" id="gv-copy-env-btn" data-env-target="gv-env-content">
					<span class="dashicons dashicons-clipboard"></span>
					<?php esc_html_e( 'Copy to clipboard', 'gplvault' ); ?>
				</button>
				<span id="gv-copy-env-success" class="gv-material-copied"><?php esc_html_e( 'Copied!', 'gplvault' ); ?></span>
			</div>
			
			<!-- Environment information is displayed in the tables below -->
			
			<div class="gv-material-env-sections">
				<?php foreach ( $environment_info as $section => $items ) : ?>
					<div class="gv-material-env-section">
						<h3><?php echo esc_html( $section ); ?></h3>
						
						<?php if ( $section === 'Active Plugins' ) : ?>
							<ul>
								<?php foreach ( $items as $plugin_item ) : ?>
									<li><?php echo esc_html( $plugin_item ); ?></li>
								<?php endforeach; ?>
							</ul>
						<?php else : ?>
							<table class="gv-material-env-table">
								<tbody>
									<?php foreach ( $items as $key => $value ) : ?>
										<?php
										$issues    = gv_check_environment_issues( $key, $value );
										$row_class = '';
										if ( ! empty( $issues ) ) {
											// Store issues for summary
											foreach ( $issues as $issue ) {
												$all_issues[] = array(
													'key' => $key,
													'level' => $issue['level'],
													'message' => $issue['message'],
												);
											}

											// Determine row class based on highest severity
											$has_error = false;
											foreach ( $issues as $issue ) {
												if ( $issue['level'] === 'error' ) {
													$has_error = true;
													break;
												}
											}
											$row_class = $has_error ? 'gv-env-error' : 'gv-env-warning';
										}
										?>
										<tr class="<?php echo esc_attr( $row_class ); ?>">
											<th><?php echo esc_html( $key ); ?></th>
											<td>
												<?php echo esc_html( $value ); ?>
												<?php if ( ! empty( $issues ) ) : ?>
													<?php foreach ( $issues as $issue ) : ?>
														<span class="gv-env-indicator <?php echo esc_attr( $issue['level'] ); ?>" 
																title="<?php echo esc_attr( $issue['message'] ); ?>">!</span>
														<span class="gv-env-note <?php echo esc_attr( $issue['level'] ); ?>">
															<?php echo esc_html( $issue['message'] ); ?>
														</span>
													<?php endforeach; ?>
												<?php endif; ?>
											</td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
			
			<script>
			(function(){
				var copyBtn = document.getElementById('gv-copy-env-btn');
				var copySuccess = document.getElementById('gv-copy-env-success');
				var downloadBtn = document.getElementById('gv-download-env-btn');
				var envSections = document.querySelectorAll('.gv-material-env-section');
				
				// Function to generate plain text from the environment tables
				function generateEnvText() {
					var text = '';
					
					envSections.forEach(function(section) {
						var sectionTitle = section.querySelector('h3').innerText;
						text += '### ' + sectionTitle + ' ###\n\n';
						
						// Check if this is the plugins section (has ul instead of table)
						var pluginsList = section.querySelector('ul');
						if (pluginsList) {
							var plugins = pluginsList.querySelectorAll('li');
							plugins.forEach(function(plugin_item) {
								text += '- ' + plugin_item.innerText + '\n';
							});
						} else {
							// Regular section with table
							var rows = section.querySelectorAll('tr');
							rows.forEach(function(row) {
								var key = row.querySelector('th').innerText;
								var value = row.querySelector('td').innerText;
								text += key + ': ' + value + '\n';
							});
						}
						
						text += '\n';
					});
					
					return text;
				}
				
				// phpcs:ignore WordPress.PHP.YodaConditions.NotYoda -- JavaScript code, not PHP
				if(copyBtn && copySuccess){
					copyBtn.addEventListener('click', function(){
						var text = generateEnvText();
						if(null !== navigator.clipboard){
							navigator.clipboard.writeText(text).then(function(){
								copySuccess.style.display = 'inline';
								setTimeout(function(){ copySuccess.style.display = 'none'; }, 1800);
							});
						}else{
							// Create a temporary textarea to copy from
							var textarea = document.createElement('textarea');
							textarea.value = text;
							textarea.style.position = 'fixed';  // Prevent scrolling to bottom
							document.body.appendChild(textarea);
							textarea.select();
							
							try {
								document.execCommand('copy');
								copySuccess.style.display = 'inline';
								setTimeout(function(){ copySuccess.style.display = 'none'; }, 1800);
							} catch(e){}
							
							document.body.removeChild(textarea);
						}
					});
				}
				
				// phpcs:ignore WordPress.PHP.YodaConditions.NotYoda -- JavaScript code, not PHP
				if(downloadBtn){
					downloadBtn.addEventListener('click', function(){
						var text = generateEnvText();
						var blob = new Blob([text], {type: 'text/plain'});
						var url = URL.createObjectURL(blob);
						var a = document.createElement('a');
						a.href = url;
						a.download = 'gplvault-environment-info.txt';
						document.body.appendChild(a);
						a.click();
						document.body.removeChild(a);
						URL.revokeObjectURL(url);
					});
				}
			})();
			</script>
		</div>
	</div>
</div>
