<?php
/**
 * Health History View
 *
 * Displays health trends and analytics dashboard.
 *
 * @package    WPShadow
 * @subpackage Views
 * @since      1.602.0200
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPShadow\Analytics\Health_History;

// Get current summary.
$summary_30 = Health_History::get_summary( 30 );
$summary_90 = Health_History::get_summary( 90 );

?>
<div class="wrap wpshadow-health-history">
	<h1><?php esc_html_e( 'Health History', 'wpshadow' ); ?></h1>
	
	<div class="wpshadow-health-history-header">
		<div class="wpshadow-metrics-grid">
			<div class="wpshadow-metric-card">
				<div class="metric-icon">⏱️</div>
				<div class="metric-value">
					<?php
					$time_saved = $summary_30['issues_fixed'] * 15; // 15 min per issue estimate.
					echo esc_html( gmdate( 'H:i', $time_saved * 60 ) );
					?>
				</div>
				<div class="metric-label"><?php esc_html_e( 'Time Saved', 'wpshadow' ); ?></div>
				<div class="metric-sublabel"><?php esc_html_e( 'Last 30 days', 'wpshadow' ); ?></div>
			</div>

			<div class="wpshadow-metric-card">
				<div class="metric-icon">🛡️</div>
				<div class="metric-value"><?php echo esc_html( number_format( $summary_30['issues_fixed'] ) ); ?></div>
				<div class="metric-label"><?php esc_html_e( 'Issues Fixed', 'wpshadow' ); ?></div>
				<div class="metric-sublabel"><?php esc_html_e( 'Last 30 days', 'wpshadow' ); ?></div>
			</div>

			<div class="wpshadow-metric-card">
				<div class="metric-icon">📈</div>
				<div class="metric-value">
					<?php
					$change = $summary_30['health_change'];
					$arrow = $change > 0 ? '↑' : ( $change < 0 ? '↓' : '→' );
					echo esc_html( $arrow . ' ' . abs( $change ) . '%' );
					?>
				</div>
				<div class="metric-label"><?php esc_html_e( 'Health Improved', 'wpshadow' ); ?></div>
				<div class="metric-sublabel"><?php esc_html_e( 'Last 30 days', 'wpshadow' ); ?></div>
			</div>

			<div class="wpshadow-metric-card">
				<div class="metric-icon">💰</div>
				<div class="metric-value">
					<?php
					$estimated_value = $summary_30['issues_fixed'] * 50; // $50 per issue.
					echo esc_html( '$' . number_format( $estimated_value ) );
					?>
				</div>
				<div class="metric-label"><?php esc_html_e( 'Estimated Value', 'wpshadow' ); ?></div>
				<div class="metric-sublabel"><?php esc_html_e( 'Based on fixes', 'wpshadow' ); ?></div>
			</div>
		</div>
	</div>

	<div class="wpshadow-health-history-controls">
		<div class="date-range-selector">
			<button class="button date-range-btn active" data-range="7">
				<?php esc_html_e( '7 Days', 'wpshadow' ); ?>
			</button>
			<button class="button date-range-btn" data-range="30">
				<?php esc_html_e( '30 Days', 'wpshadow' ); ?>
			</button>
			<button class="button date-range-btn" data-range="60">
				<?php esc_html_e( '60 Days', 'wpshadow' ); ?>
			</button>
			<button class="button date-range-btn" data-range="90">
				<?php esc_html_e( '90 Days', 'wpshadow' ); ?>
			</button>
		</div>

		<div class="chart-actions">
			<button class="button" id="wpshadow-export-chart">
				<span class="dashicons dashicons-download"></span>
				<?php esc_html_e( 'Export Image', 'wpshadow' ); ?>
			</button>
			<button class="button" id="wpshadow-share-chart">
				<span class="dashicons dashicons-share"></span>
				<?php esc_html_e( 'Share', 'wpshadow' ); ?>
			</button>
		</div>
	</div>

	<div class="wpshadow-chart-container">
		<div class="wpshadow-chart-wrapper">
			<h2><?php esc_html_e( 'Overall Health Trend', 'wpshadow' ); ?></h2>
			<canvas id="wpshadow-health-trend-chart" width="800" height="300"></canvas>
		</div>

		<div class="wpshadow-chart-grid">
			<div class="wpshadow-chart-wrapper">
				<h3><?php esc_html_e( 'Category Health', 'wpshadow' ); ?></h3>
				<canvas id="wpshadow-category-chart" width="400" height="250"></canvas>
			</div>

			<div class="wpshadow-chart-wrapper">
				<h3><?php esc_html_e( 'Issues by Severity', 'wpshadow' ); ?></h3>
				<canvas id="wpshadow-issues-chart" width="400" height="250"></canvas>
			</div>
		</div>
	</div>

	<div class="wpshadow-health-history-footer">
		<p>
			<?php
			printf(
				/* translators: 1: number of days, 2: health score */
				esc_html__( 'Your site health over the last %1$d days averages %2$d%%. Keep up the great work!', 'wpshadow' ),
				90,
				(int) $summary_90['avg_health']
			);
			?>
		</p>
	</div>
</div>

<style>
.wpshadow-health-history {
	max-width: 1400px;
}

.wpshadow-metrics-grid {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
	gap: 20px;
	margin: 20px 0 30px;
}

.wpshadow-metric-card {
	background: #fff;
	border: 1px solid #ddd;
	border-radius: 8px;
	padding: 20px;
	text-align: center;
	box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.metric-icon {
	font-size: 2em;
	margin-bottom: 10px;
}

.metric-value {
	font-size: 2em;
	font-weight: bold;
	color: #2271b1;
	margin: 10px 0;
}

.metric-label {
	font-weight: 600;
	color: #333;
	margin-bottom: 5px;
}

.metric-sublabel {
	font-size: 0.9em;
	color: #666;
}

.wpshadow-health-history-controls {
	display: flex;
	justify-content: space-between;
	align-items: center;
	margin: 20px 0;
	padding: 15px;
	background: #f5f5f5;
	border-radius: 8px;
}

.date-range-selector {
	display: flex;
	gap: 10px;
}

.date-range-btn.active {
	background: #2271b1;
	color: #fff;
	border-color: #2271b1;
}

.chart-actions {
	display: flex;
	gap: 10px;
}

.wpshadow-chart-container {
	margin: 30px 0;
}

.wpshadow-chart-wrapper {
	background: #fff;
	border: 1px solid #ddd;
	border-radius: 8px;
	padding: 20px;
	margin-bottom: 20px;
}

.wpshadow-chart-wrapper h2,
.wpshadow-chart-wrapper h3 {
	margin-top: 0;
	margin-bottom: 15px;
	font-size: 1.2em;
}

.wpshadow-chart-grid {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
	gap: 20px;
}

.wpshadow-health-history-footer {
	text-align: center;
	padding: 30px;
	background: #f0f6fc;
	border-radius: 8px;
	margin-top: 30px;
}

.wpshadow-health-history-footer p {
	font-size: 1.1em;
	color: #2c3338;
	margin: 0;
}

@media (max-width: 768px) {
	.wpshadow-health-history-controls {
		flex-direction: column;
		gap: 15px;
	}
	
	.wpshadow-chart-grid {
		grid-template-columns: 1fr;
	}
}
</style>
