<?php
/**
 * Performance Monitoring Dashboard View
 *
 * @package WPSHADOW_CoreSupport
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Check permissions.
if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'plugin-wpshadow' ) );
}

// Get current tab.
$current_tab = isset( $_GET['perf_tab'] ) ? sanitize_key( $_GET['perf_tab'] ) : 'overview'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

// Get performance data.
$metrics         = WPSHADOW_Performance_Monitor::get_current_metrics();
$score_data      = WPSHADOW_Performance_Monitor::calculate_performance_score();
$recommendations = WPSHADOW_Performance_Monitor::get_recommendations();
$db_stats        = WPSHADOW_Performance_Monitor::get_database_stats();

?>
<div class="wrap wps-performance-dashboard">
	<h1><?php esc_html_e( 'Performance Monitoring Dashboard', 'plugin-wpshadow' ); ?></h1>
	
	<style>
		.wps-performance-dashboard .nav-tab-wrapper {
			margin: 20px 0;
			border-bottom: 1px solid #ccc;
		}
		.wps-performance-dashboard .nav-tab {
			border: 1px solid #ccc;
			border-bottom: none;
			background: #f1f1f1;
			color: #0073aa;
			margin-right: 5px;
			padding: 8px 12px;
			font-size: 14px;
		}
		.wps-performance-dashboard .nav-tab-active {
			background: #fff;
			border-bottom: 1px solid #fff;
			margin-bottom: -1px;
		}
		.wps-perf-score-ring {
			width: 200px;
			height: 200px;
			margin: 0 auto 20px;
			position: relative;
		}
		.wps-perf-score-ring svg {
			transform: rotate(-90deg);
		}
		.wps-perf-score-ring .score-text {
			position: absolute;
			top: 50%;
			left: 50%;
			transform: translate(-50%, -50%);
			text-align: center;
		}
		.wps-perf-score-ring .score-text .value {
			font-size: 48px;
			font-weight: 700;
			display: block;
		}
		.wps-perf-score-ring .score-text .grade {
			font-size: 24px;
			color: #666;
		}
		.wps-perf-grid {
			display: grid;
			grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
			gap: 20px;
			margin: 20px 0;
		}
		.wps-perf-card {
			background: #fff;
			border: 1px solid #ccc;
			border-radius: 4px;
			padding: 20px;
			box-shadow: 0 1px 3px rgba(0,0,0,0.1);
		}
		.wps-perf-card h3 {
			margin: 0 0 15px 0;
			font-size: 14px;
			color: #666;
			text-transform: uppercase;
			letter-spacing: 0.5px;
		}
		.wps-perf-card .metric-value {
			font-size: 32px;
			font-weight: 700;
			color: #0073aa;
			display: block;
			margin-bottom: 5px;
		}
		.wps-perf-card .metric-label {
			font-size: 13px;
			color: #666;
		}
		.wps-recommendation-list {
			list-style: none;
			margin: 20px 0;
			padding: 0;
		}
		.wps-recommendation-list li {
			padding: 15px;
			margin-bottom: 10px;
			border-radius: 4px;
			border-left: 4px solid;
		}
		.wps-recommendation-list li.warning {
			background: #fff3cd;
			border-left-color: #ffc107;
		}
		.wps-recommendation-list li.critical {
			background: #f8d7da;
			border-left-color: #dc3545;
		}
		.wps-recommendation-list li.info {
			background: #d1ecf1;
			border-left-color: #17a2b8;
		}
		.wps-recommendation-list li strong {
			display: block;
			margin-bottom: 5px;
			font-size: 14px;
		}
		.wps-db-table {
			width: 100%;
			border-collapse: collapse;
			margin: 20px 0;
		}
		.wps-db-table th,
		.wps-db-table td {
			padding: 10px;
			text-align: left;
			border-bottom: 1px solid #ddd;
		}
		.wps-db-table th {
			background: #f8f9fa;
			font-weight: 600;
		}
		.wps-db-table tr:hover {
			background: #f8f9fa;
		}
	</style>

	<!-- Tab Navigation -->
	<h2 class="nav-tab-wrapper">
		<a href="?page=wp-support&WPSHADOW_tab=performance&perf_tab=overview" class="nav-tab <?php echo 'overview' === $current_tab ? 'nav-tab-active' : ''; ?>">
			<?php esc_html_e( 'Overview', 'plugin-wpshadow' ); ?>
		</a>
		<a href="?page=wp-support&WPSHADOW_tab=performance&perf_tab=database" class="nav-tab <?php echo 'database' === $current_tab ? 'nav-tab-active' : ''; ?>">
			<?php esc_html_e( 'Database', 'plugin-wpshadow' ); ?>
		</a>
		<a href="?page=wp-support&WPSHADOW_tab=performance&perf_tab=history" class="nav-tab <?php echo 'history' === $current_tab ? 'nav-tab-active' : ''; ?>">
			<?php esc_html_e( 'History', 'plugin-wpshadow' ); ?>
		</a>
	</h2>

	<!-- Tab Content -->
	<div class="wps-tab-content">
		<?php if ( 'overview' === $current_tab ) : ?>
			<!-- Overview Tab -->
			<div class="wps-perf-overview">
				<!-- Performance Score -->
				<div class="wps-perf-card" style="max-width: 500px; margin: 20px auto;">
					<h3 style="text-align: center; font-size: 16px;"><?php esc_html_e( 'Performance Score', 'plugin-wpshadow' ); ?></h3>
					
					<div class="wps-perf-score-ring">
						<svg width="200" height="200" viewBox="0 0 200 200">
							<circle cx="100" cy="100" r="90" fill="none" stroke="#e9ecef" stroke-width="12" />
							<circle cx="100" cy="100" r="90" fill="none" stroke="<?php echo esc_attr( $score_data['color'] ); ?>" stroke-width="12" 
								stroke-dasharray="<?php echo esc_attr( ( $score_data['score'] / 100 ) * 565 ); ?> 565" 
								stroke-linecap="round" />
						</svg>
						<div class="score-text">
							<span class="value" style="color: <?php echo esc_attr( $score_data['color'] ); ?>;"><?php echo esc_html( $score_data['score'] ); ?></span>
							<span class="grade"><?php echo esc_html( $score_data['grade'] ); ?></span>
						</div>
					</div>

					<div style="text-align: center; padding: 15px; background: #f8f9fa; border-radius: 4px; margin-top: 20px;">
						<p style="margin: 0; font-size: 14px; color: #666;">
							<?php
							if ( $score_data['score'] >= 90 ) {
								esc_html_e( '🎉 Excellent! Your site is performing very well.', 'plugin-wpshadow' );
							} elseif ( $score_data['score'] >= 70 ) {
								esc_html_e( '✅ Good performance. Check recommendations for improvements.', 'plugin-wpshadow' );
							} elseif ( $score_data['score'] >= 50 ) {
								esc_html_e( '⚠️ Performance needs attention. Review recommendations below.', 'plugin-wpshadow' );
							} else {
								esc_html_e( '🚨 Critical performance issues detected. Immediate action recommended.', 'plugin-wpshadow' );
							}
							?>
						</p>
					</div>
				</div>

				<!-- Current Metrics -->
				<h2><?php esc_html_e( '📊 Current Metrics', 'plugin-wpshadow' ); ?></h2>
				<div class="wps-perf-grid">
					<div class="wps-perf-card">
						<h3><?php esc_html_e( 'Database Queries', 'plugin-wpshadow' ); ?></h3>
						<span class="metric-value"><?php echo esc_html( $metrics['query_count'] ?? 0 ); ?></span>
						<span class="metric-label">
							<?php
							/* translators: %s: query time in seconds */
							echo esc_html( sprintf( __( '%s sec total time', 'plugin-wpshadow' ), number_format( (float) ( $metrics['query_time'] ?? 0 ), 3 ) ) );
							?>
						</span>
					</div>

					<div class="wps-perf-card">
						<h3><?php esc_html_e( 'Page Load Time', 'plugin-wpshadow' ); ?></h3>
						<span class="metric-value"><?php echo esc_html( number_format( (float) ( $metrics['load_time'] ?? 0 ), 3 ) ); ?>s</span>
						<span class="metric-label"><?php esc_html_e( 'Server generation time', 'plugin-wpshadow' ); ?></span>
					</div>

					<div class="wps-perf-card">
						<h3><?php esc_html_e( 'Memory Usage', 'plugin-wpshadow' ); ?></h3>
						<span class="metric-value"><?php echo esc_html( $metrics['memory_mb'] ?? 0 ); ?> MB</span>
						<span class="metric-label">
							<?php
							$memory_limit = ini_get( 'memory_limit' );
							/* translators: %s: memory limit */
							echo esc_html( sprintf( __( 'Limit: %s', 'plugin-wpshadow' ), $memory_limit ) );
							?>
						</span>
					</div>

					<div class="wps-perf-card">
						<h3><?php esc_html_e( 'Database Size', 'plugin-wpshadow' ); ?></h3>
						<span class="metric-value"><?php echo esc_html( $metrics['db_size'] ?? 0 ); ?> MB</span>
						<span class="metric-label"><?php esc_html_e( 'Total database size', 'plugin-wpshadow' ); ?></span>
					</div>

					<div class="wps-perf-card">
						<h3><?php esc_html_e( 'Active Plugins', 'plugin-wpshadow' ); ?></h3>
						<span class="metric-value"><?php echo esc_html( $metrics['active_plugins'] ?? 0 ); ?></span>
						<span class="metric-label"><?php esc_html_e( 'Currently active', 'plugin-wpshadow' ); ?></span>
					</div>
				</div>

				<!-- Recommendations -->
				<?php if ( ! empty( $recommendations ) ) : ?>
				<h2><?php esc_html_e( '💡 Optimization Recommendations', 'plugin-wpshadow' ); ?></h2>
				<ul class="wps-recommendation-list">
					<?php foreach ( $recommendations as $rec ) : ?>
						<li class="<?php echo esc_attr( $rec['type'] ); ?>">
							<strong><?php echo esc_html( $rec['title'] ); ?></strong>
							<?php echo esc_html( $rec['description'] ); ?>
						</li>
					<?php endforeach; ?>
				</ul>
				<?php else : ?>
				<div class="notice notice-success inline">
					<p><?php esc_html_e( '✅ No performance issues detected! Your site is running well.', 'plugin-wpshadow' ); ?></p>
				</div>
				<?php endif; ?>
			</div>

		<?php elseif ( 'database' === $current_tab ) : ?>
			<!-- Database Tab -->
			<h2><?php esc_html_e( '🗄️ Database Statistics', 'plugin-wpshadow' ); ?></h2>
			
			<div class="wps-perf-grid">
				<div class="wps-perf-card">
					<h3><?php esc_html_e( 'Total Size', 'plugin-wpshadow' ); ?></h3>
					<span class="metric-value"><?php echo esc_html( $db_stats['total_size'] ); ?> MB</span>
					<span class="metric-label"><?php esc_html_e( 'All tables combined', 'plugin-wpshadow' ); ?></span>
				</div>

				<div class="wps-perf-card">
					<h3><?php esc_html_e( 'Transients', 'plugin-wpshadow' ); ?></h3>
					<span class="metric-value"><?php echo esc_html( $db_stats['transient_count'] ); ?></span>
					<span class="metric-label">
						<?php
						/* translators: %d: expired transients count */
						echo esc_html( sprintf( __( '%d expired', 'plugin-wpshadow' ), $db_stats['expired_transients'] ) );
						?>
					</span>
				</div>

				<div class="wps-perf-card">
					<h3><?php esc_html_e( 'Orphaned Data', 'plugin-wpshadow' ); ?></h3>
					<span class="metric-value"><?php echo esc_html( $db_stats['orphaned_postmeta'] ); ?></span>
					<span class="metric-label"><?php esc_html_e( 'Orphaned postmeta records', 'plugin-wpshadow' ); ?></span>
				</div>
			</div>

			<?php if ( ! empty( $db_stats['largest_tables'] ) ) : ?>
			<h3><?php esc_html_e( 'Largest Tables (Top 10)', 'plugin-wpshadow' ); ?></h3>
			<table class="wps-db-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Table Name', 'plugin-wpshadow' ); ?></th>
						<th><?php esc_html_e( 'Size (MB)', 'plugin-wpshadow' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $db_stats['largest_tables'] as $table ) : ?>
						<tr>
							<td><code><?php echo esc_html( $table['table_name'] ); ?></code></td>
							<td><?php echo esc_html( $table['size_mb'] ); ?> MB</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<?php endif; ?>

			<?php if ( ! empty( $metrics['slow_queries'] ) ) : ?>
			<h3><?php esc_html_e( 'Slow Queries (&gt;100ms)', 'plugin-wpshadow' ); ?></h3>
			<table class="wps-db-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Query', 'plugin-wpshadow' ); ?></th>
						<th><?php esc_html_e( 'Time (s)', 'plugin-wpshadow' ); ?></th>
						<th><?php esc_html_e( 'Caller', 'plugin-wpshadow' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( array_slice( $metrics['slow_queries'], 0, 10 ) as $query ) : ?>
						<tr>
							<td><code style="font-size: 11px;"><?php echo esc_html( $query['sql'] ); ?></code></td>
							<td><?php echo esc_html( number_format( $query['time'], 4 ) ); ?>s</td>
							<td style="font-size: 11px;"><?php echo esc_html( $query['caller'] ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<?php endif; ?>

			<?php if ( ! empty( $metrics['query_types'] ) ) : ?>
			<h3><?php esc_html_e( 'Query Breakdown by Type', 'plugin-wpshadow' ); ?></h3>
			<div class="wps-perf-grid">
				<?php foreach ( $metrics['query_types'] as $type => $count ) : ?>
					<div class="wps-perf-card">
						<h3><?php echo esc_html( $type ); ?></h3>
						<span class="metric-value"><?php echo esc_html( $count ); ?></span>
						<span class="metric-label"><?php esc_html_e( 'queries', 'plugin-wpshadow' ); ?></span>
					</div>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>

		<?php elseif ( 'history' === $current_tab ) : ?>
			<!-- History Tab -->
			<h2><?php esc_html_e( '📈 Historical Performance Data', 'plugin-wpshadow' ); ?></h2>

			<?php
			// Get historical data for charts.
			$history_7days  = WPSHADOW_Performance_Monitor::get_historical_metrics( 7 );
			$history_30days = WPSHADOW_Performance_Monitor::get_historical_metrics( 30 );
			?>

			<div class="wps-perf-card">
				<h3><?php esc_html_e( 'Performance Trends', 'plugin-wpshadow' ); ?></h3>
				<p><?php esc_html_e( 'Historical data tracking coming soon. This will show:' ); ?></p>
				<ul>
					<li><?php esc_html_e( 'Average page load time (7/30/90 days)', 'plugin-wpshadow' ); ?></li>
					<li><?php esc_html_e( 'Query count per request trends', 'plugin-wpshadow' ); ?></li>
					<li><?php esc_html_e( 'Memory usage trends', 'plugin-wpshadow' ); ?></li>
					<li><?php esc_html_e( 'Database size growth', 'plugin-wpshadow' ); ?></li>
				</ul>

				<p style="margin-top: 20px;">
					<?php
					/* translators: %d: number of data points */
					echo esc_html( sprintf( __( 'Currently tracking %d data points over the last 90 days.', 'plugin-wpshadow' ), count( WPSHADOW_Performance_Monitor::get_historical_metrics( 90 ) ) ) );
					?>
				</p>

				<p>
					<a href="#" class="button" id="wps-export-csv"><?php esc_html_e( 'Export as CSV', 'plugin-wpshadow' ); ?></a>
					<a href="#" class="button" id="wps-export-json"><?php esc_html_e( 'Export as JSON', 'plugin-wpshadow' ); ?></a>
				</p>
			</div>

		<?php endif; ?>
	</div>

	<!-- Export Functionality -->
	<script>
		jQuery(document).ready(function($) {
			var ajaxurl = '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>';
			
			// Export as CSV.
			$('#wps-export-csv').on('click', function(e) {
				e.preventDefault();
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'wpshadow_performance_export',
						format: 'csv',
						days: 30,
						nonce: '<?php echo esc_js( wp_create_nonce( 'wpshadow_performance_export' ) ); ?>'
					},
					success: function(response) {
						if (response.success && response.data) {
							const blob = new Blob([response.data.data], {type: 'text/csv'});
							const url = window.URL.createObjectURL(blob);
							const a = document.createElement('a');
							a.href = url;
							a.download = 'performance-data-' + new Date().toISOString().split('T')[0] + '.csv';
							a.click();
							window.URL.revokeObjectURL(url);
						}
					}
				});
			});

			// Export as JSON.
			$('#wps-export-json').on('click', function(e) {
				e.preventDefault();
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'wpshadow_performance_export',
						format: 'json',
						days: 30,
						nonce: '<?php echo esc_js( wp_create_nonce( 'wpshadow_performance_export' ) ); ?>'
					},
					success: function(response) {
						if (response.success && response.data) {
							const blob = new Blob([response.data.data], {type: 'application/json'});
							const url = window.URL.createObjectURL(blob);
							const a = document.createElement('a');
							a.href = url;
							a.download = 'performance-data-' + new Date().toISOString().split('T')[0] + '.json';
							a.click();
							window.URL.revokeObjectURL(url);
						}
					}
				});
			});
		});
	</script>
</div>
