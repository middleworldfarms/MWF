<?php
defined( 'ABSPATH' ) || exit;
/**
 * @var array $license_summary
 */
?>
<style>
/* Material Design styles for license status */
.gv-material-status-section {
	background: #f5f5f5;
	border-radius: var(--gv-material-radius);
	padding: 24px;
	margin-bottom: 32px;
}

/* Progress bar styles */
.gv-material-progress-container {
	background: #fff;
	border-radius: 8px;
	padding: 20px;
	margin-bottom: 20px;
	box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.gv-material-progress-info {
	display: flex;
	justify-content: space-between;
	align-items: center;
	margin-bottom: 12px;
}

.gv-material-progress-text {
	font-size: 14px;
	color: #666;
}

.gv-material-progress-percentage {
	font-size: 14px;
	font-weight: 600;
	color: #333;
}

.gv-material-progress-bar {
	width: 100%;
	height: 8px;
	background: #e0e0e0;
	border-radius: 4px;
	overflow: hidden;
	position: relative;
}

.gv-material-progress-fill {
	height: 100%;
	background: #4caf50;
	border-radius: 4px;
	transition: width 0.3s ease;
}

/* Grid layout for 3 cards */
.gv-material-status-grid {
	display: grid;
	grid-template-columns: repeat(3, 1fr);
	gap: 20px;
}

.gv-material-status-item {
	background: #fff;
	border-radius: 8px;
	padding: 24px 20px;
	text-align: center;
	box-shadow: 0 1px 3px rgba(0,0,0,0.1);
	display: flex;
	flex-direction: column;
	justify-content: center;
	min-height: 120px;
}

.gv-material-status-label {
	font-size: 13px;
	color: #666;
	text-transform: uppercase;
	letter-spacing: 0.5px;
	margin-bottom: 12px;
	font-weight: 500;
}

.gv-material-status-value {
	font-size: 36px;
	font-weight: 600;
	color: #333;
	line-height: 1;
}

/* Color coding for different metrics */
.gv-material-status-item.total {
	border-top: 3px solid #2196f3;
}

.gv-material-status-item.used {
	border-top: 3px solid #ff9800;
}

.gv-material-status-item.remaining {
	border-top: 3px solid #4caf50;
}

/* Responsive adjustments */
@media (max-width: 1024px) {
	.gv-material-status-grid {
		grid-template-columns: repeat(3, 1fr);
		gap: 16px;
	}
}

@media (max-width: 782px) {
	.gv-material-status-grid {
		grid-template-columns: 1fr;
		gap: 16px;
	}
	
	.gv-material-status-item {
		padding: 20px;
		min-height: auto;
	}
	
	.gv-material-status-value {
		font-size: 32px;
	}
}
</style>

<div class="gv-material-status-section" id="gv_license_status_section">
	<h2 class="gv-material-section-title" style="font-size: 20px; font-weight: 500; color: #333; margin: 0 0 20px 0;">
		<?php esc_html_e( 'License Status', 'gplvault' ); ?>
	</h2>
	
	<?php
	$used_percentage = $license_summary['total_activations_purchased'] > 0
		? round( ( $license_summary['total_activations'] / $license_summary['total_activations_purchased'] ) * 100, 1 )
		: 0;
	?>
	
	<!-- Progress bar -->
	<div class="gv-material-progress-container">
		<div class="gv-material-progress-info">
			<span class="gv-material-progress-text">
				<?php
				printf(
					esc_html__( '%1$d of %2$d licenses used', 'gplvault' ),
					absint( $license_summary['total_activations'] ),
					absint( $license_summary['total_activations_purchased'] )
				);
				?>
			</span>
			<span class="gv-material-progress-percentage"><?php echo esc_html( $used_percentage ); ?>%</span>
		</div>
		<div class="gv-material-progress-bar">
			<div class="gv-material-progress-fill" style="width: <?php echo esc_attr( $used_percentage ); ?>%"></div>
		</div>
	</div>
	
	<!-- 3 metric cards -->
	<div class="gv-material-status-grid">
		<div class="gv-material-status-item total">
			<div class="gv-material-status-label"><?php esc_html_e( 'Total Quota', 'gplvault' ); ?></div>
			<div class="gv-material-status-value">
				<?php echo esc_html( $license_summary['total_activations_purchased'] ); ?>
			</div>
		</div>
		
		<div class="gv-material-status-item used">
			<div class="gv-material-status-label"><?php esc_html_e( 'Already Activated', 'gplvault' ); ?></div>
			<div class="gv-material-status-value">
				<?php echo esc_html( $license_summary['total_activations'] ); ?>
			</div>
		</div>
		
		<div class="gv-material-status-item remaining">
			<div class="gv-material-status-label"><?php esc_html_e( 'Remaining', 'gplvault' ); ?></div>
			<div class="gv-material-status-value">
				<?php echo esc_html( $license_summary['activations_remaining'] ); ?>
			</div>
		</div>
	</div>
</div>
