<?php
/**
 * Performance Monitoring Dashboard View
 *
 * @package WPS_CoreSupport
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Check permissions.
if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'plugin-wp-support-thisismyurl' ) );
}

// Get current tab.
$current_tab = isset( $_GET['perf_tab'] ) ? sanitize_key( $_GET['perf_tab'] ) : 'overview'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

// Get performance data.
$metrics = WPS_Performance_Monitor::get_current_metrics();
$score_data = WPS_Performance_Monitor::calculate_performance_score();
$recommendations = WPS_Performance_Monitor::get_recommendations();
$db_stats = WPS_Performance_Monitor::get_database_stats();
$thresholds = WPS_Performance_Monitor::get_thresholds();

// Update thresholds if form submitted.
if ( isset( $_POST['wps_update_thresholds'] ) && check_admin_referer( 'wps_performance_thresholds' ) ) {
	$new_thresholds = array(
		'query_count' => isset( $_POST['threshold_query_count'] ) ? absint( $_POST['threshold_query_count'] ) : 50,
		'load_time'   => isset( $_POST['threshold_load_time'] ) ? floatval( $_POST['threshold_load_time'] ) : 2,
		'memory'      => isset( $_POST['threshold_memory'] ) ? absint( $_POST['threshold_memory'] ) : 80,
	);
	
	WPS_Performance_Monitor::update_thresholds( $new_thresholds );
	$thresholds = $new_thresholds;
	
	echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Alert thresholds updated successfully.', 'plugin-wp-support-thisismyurl' ) . '</p></div>';
}

?>
<div class="wrap wps-performance-dashboard">
	<h1><?php esc_html_e( 'Performance Monitoring Dashboard', 'plugin-wp-support-thisismyurl' ); ?></h1>
	
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
		<a href="?page=wp-support&WPS_tab=performance&perf_tab=overview" class="nav-tab <?php echo 'overview' === $current_tab ? 'nav-tab-active' : ''; ?>">
			<?php esc_html_e( 'Overview', 'plugin-wp-support-thisismyurl' ); ?>
		</a>
		<a href="?page=wp-support&WPS_tab=performance&perf_tab=database" class="nav-tab <?php echo 'database' === $current_tab ? 'nav-tab-active' : ''; ?>">
			<?php esc_html_e( 'Database', 'plugin-wp-support-thisismyurl' ); ?>
		</a>
		<a href="?page=wp-support&WPS_tab=performance&perf_tab=history" class="nav-tab <?php echo 'history' === $current_tab ? 'nav-tab-active' : ''; ?>">
			<?php esc_html_e( 'History', 'plugin-wp-support-thisismyurl' ); ?>
		</a>
		<a href="?page=wp-support&WPS_tab=performance&perf_tab=alerts" class="nav-tab <?php echo 'alerts' === $current_tab ? 'nav-tab-active' : ''; ?>">
			<?php esc_html_e( 'Alerts', 'plugin-wp-support-thisismyurl' ); ?>
		</a>
	</h2>

	<!-- Tab Content -->
	<div class="wps-tab-content">
		<?php if ( 'overview' === $current_tab ) : ?>
			<!-- Overview Tab -->
			<div class="wps-perf-overview">
				<!-- Performance Score -->
				<div class="wps-perf-card" style="max-width: 500px; margin: 20px auto;">
					<h3 style="text-align: center; font-size: 16px;"><?php esc_html_e( 'Performance Score', 'plugin-wp-support-thisismyurl' ); ?></h3>
					
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
								esc_html_e( '🎉 Excellent! Your site is performing very well.', 'plugin-wp-support-thisismyurl' );
							} elseif ( $score_data['score'] >= 70 ) {
								esc_html_e( '✅ Good performance. Check recommendations for improvements.', 'plugin-wp-support-thisismyurl' );
							} elseif ( $score_data['score'] >= 50 ) {
								esc_html_e( '⚠️ Performance needs attention. Review recommendations below.', 'plugin-wp-support-thisismyurl' );
							} else {
								esc_html_e( '🚨 Critical performance issues detected. Immediate action recommended.', 'plugin-wp-support-thisismyurl' );
							}
							?>
						</p>
					</div>
				</div>

				<!-- Current Metrics -->
				<h2><?php esc_html_e( '📊 Current Metrics', 'plugin-wp-support-thisismyurl' ); ?></h2>
				<div class="wps-perf-grid">
					<div class="wps-perf-card">
						<h3><?php esc_html_e( 'Database Queries', 'plugin-wp-support-thisismyurl' ); ?></h3>
						<span class="metric-value"><?php echo esc_html( $metrics['query_count'] ?? 0 ); ?></span>
						<span class="metric-label">
							<?php
							/* translators: %s: query time in seconds */
							echo esc_html( sprintf( __( '%s sec total time', 'plugin-wp-support-thisismyurl' ), number_format( (float) ( $metrics['query_time'] ?? 0 ), 3 ) ) );
							?>
						</span>
					</div>

					<div class="wps-perf-card">
						<h3><?php esc_html_e( 'Page Load Time', 'plugin-wp-support-thisismyurl' ); ?></h3>
						<span class="metric-value"><?php echo esc_html( number_format( (float) ( $metrics['load_time'] ?? 0 ), 3 ) ); ?>s</span>
						<span class="metric-label"><?php esc_html_e( 'Server generation time', 'plugin-wp-support-thisismyurl' ); ?></span>
					</div>

					<div class="wps-perf-card">
						<h3><?php esc_html_e( 'Memory Usage', 'plugin-wp-support-thisismyurl' ); ?></h3>
						<span class="metric-value"><?php echo esc_html( $metrics['memory_mb'] ?? 0 ); ?> MB</span>
						<span class="metric-label">
							<?php
							$memory_limit = ini_get( 'memory_limit' );
							/* translators: %s: memory limit */
							echo esc_html( sprintf( __( 'Limit: %s', 'plugin-wp-support-thisismyurl' ), $memory_limit ) );
							?>
						</span>
					</div>

					<div class="wps-perf-card">
						<h3><?php esc_html_e( 'Database Size', 'plugin-wp-support-thisismyurl' ); ?></h3>
						<span class="metric-value"><?php echo esc_html( $metrics['db_size'] ?? 0 ); ?> MB</span>
						<span class="metric-label"><?php esc_html_e( 'Total database size', 'plugin-wp-support-thisismyurl' ); ?></span>
					</div>

					<div class="wps-perf-card">
						<h3><?php esc_html_e( 'Active Plugins', 'plugin-wp-support-thisismyurl' ); ?></h3>
						<span class="metric-value"><?php echo esc_html( $metrics['active_plugins'] ?? 0 ); ?></span>
						<span class="metric-label"><?php esc_html_e( 'Currently active', 'plugin-wp-support-thisismyurl' ); ?></span>
					</div>
				</div>

				<!-- Recommendations -->
				<?php if ( ! empty( $recommendations ) ) : ?>
				<h2><?php esc_html_e( '💡 Optimization Recommendations', 'plugin-wp-support-thisismyurl' ); ?></h2>
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
					<p><?php esc_html_e( '✅ No performance issues detected! Your site is running well.', 'plugin-wp-support-thisismyurl' ); ?></p>
				</div>
				<?php endif; ?>
			</div>

		<?php elseif ( 'database' === $current_tab ) : ?>
			<!-- Database Tab -->
			<h2><?php esc_html_e( '🗄️ Database Statistics', 'plugin-wp-support-thisismyurl' ); ?></h2>
			
			<div class="wps-perf-grid">
				<div class="wps-perf-card">
					<h3><?php esc_html_e( 'Total Size', 'plugin-wp-support-thisismyurl' ); ?></h3>
					<span class="metric-value"><?php echo esc_html( $db_stats['total_size'] ); ?> MB</span>
					<span class="metric-label"><?php esc_html_e( 'All tables combined', 'plugin-wp-support-thisismyurl' ); ?></span>
				</div>

				<div class="wps-perf-card">
					<h3><?php esc_html_e( 'Transients', 'plugin-wp-support-thisismyurl' ); ?></h3>
					<span class="metric-value"><?php echo esc_html( $db_stats['transient_count'] ); ?></span>
					<span class="metric-label">
						<?php
						/* translators: %d: expired transients count */
						echo esc_html( sprintf( __( '%d expired', 'plugin-wp-support-thisismyurl' ), $db_stats['expired_transients'] ) );
						?>
					</span>
				</div>

				<div class="wps-perf-card">
					<h3><?php esc_html_e( 'Orphaned Data', 'plugin-wp-support-thisismyurl' ); ?></h3>
					<span class="metric-value"><?php echo esc_html( $db_stats['orphaned_postmeta'] ); ?></span>
					<span class="metric-label"><?php esc_html_e( 'Orphaned postmeta records', 'plugin-wp-support-thisismyurl' ); ?></span>
				</div>
			</div>

			<?php if ( ! empty( $db_stats['largest_tables'] ) ) : ?>
			<h3><?php esc_html_e( 'Largest Tables (Top 10)', 'plugin-wp-support-thisismyurl' ); ?></h3>
			<table class="wps-db-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Table Name', 'plugin-wp-support-thisismyurl' ); ?></th>
						<th><?php esc_html_e( 'Size (MB)', 'plugin-wp-support-thisismyurl' ); ?></th>
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
			<h3><?php esc_html_e( 'Slow Queries (&gt;100ms)', 'plugin-wp-support-thisismyurl' ); ?></h3>
			<table class="wps-db-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Query', 'plugin-wp-support-thisismyurl' ); ?></th>
						<th><?php esc_html_e( 'Time (s)', 'plugin-wp-support-thisismyurl' ); ?></th>
						<th><?php esc_html_e( 'Caller', 'plugin-wp-support-thisismyurl' ); ?></th>
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
			<h3><?php esc_html_e( 'Query Breakdown by Type', 'plugin-wp-support-thisismyurl' ); ?></h3>
			<div class="wps-perf-grid">
				<?php foreach ( $metrics['query_types'] as $type => $count ) : ?>
					<div class="wps-perf-card">
						<h3><?php echo esc_html( $type ); ?></h3>
						<span class="metric-value"><?php echo esc_html( $count ); ?></span>
						<span class="metric-label"><?php esc_html_e( 'queries', 'plugin-wp-support-thisismyurl' ); ?></span>
					</div>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>

		<?php elseif ( 'history' === $current_tab ) : ?>
			<!-- History Tab -->
			<h2><?php esc_html_e( '📈 Historical Performance Data', 'plugin-wp-support-thisismyurl' ); ?></h2>

			<?php
			// Get historical data for charts.
			$history_7days = WPS_Performance_Monitor::get_historical_metrics( 7 );
			$history_30days = WPS_Performance_Monitor::get_historical_metrics( 30 );
			?>

			<div class="wps-perf-card">
				<h3><?php esc_html_e( 'Performance Trends', 'plugin-wp-support-thisismyurl' ); ?></h3>
				<p><?php esc_html_e( 'Historical data tracking coming soon. This will show:' ); ?></p>
				<ul>
					<li><?php esc_html_e( 'Average page load time (7/30/90 days)', 'plugin-wp-support-thisismyurl' ); ?></li>
					<li><?php esc_html_e( 'Query count per request trends', 'plugin-wp-support-thisismyurl' ); ?></li>
					<li><?php esc_html_e( 'Memory usage trends', 'plugin-wp-support-thisismyurl' ); ?></li>
					<li><?php esc_html_e( 'Database size growth', 'plugin-wp-support-thisismyurl' ); ?></li>
				</ul>

				<p style="margin-top: 20px;">
					<?php
					/* translators: %d: number of data points */
					echo esc_html( sprintf( __( 'Currently tracking %d data points over the last 90 days.', 'plugin-wp-support-thisismyurl' ), count( WPS_Performance_Monitor::get_historical_metrics( 90 ) ) ) );
					?>
				</p>

				<p>
					<a href="#" class="button" id="wps-export-csv"><?php esc_html_e( 'Export as CSV', 'plugin-wp-support-thisismyurl' ); ?></a>
					<a href="#" class="button" id="wps-export-json"><?php esc_html_e( 'Export as JSON', 'plugin-wp-support-thisismyurl' ); ?></a>
				</p>
			</div>

		<?php elseif ( 'alerts' === $current_tab ) : ?>
			<!-- Alerts Tab -->
			<h2><?php esc_html_e( '🔔 Performance Alerts', 'plugin-wp-support-thisismyurl' ); ?></h2>

			<div class="wps-perf-card">
				<h3><?php esc_html_e( 'Alert Thresholds', 'plugin-wp-support-thisismyurl' ); ?></h3>
				<p><?php esc_html_e( 'Configure when performance alerts should be triggered.', 'plugin-wp-support-thisismyurl' ); ?></p>

				<form method="post" action="">
					<?php wp_nonce_field( 'wps_performance_thresholds' ); ?>
					<input type="hidden" name="wps_update_thresholds" value="1" />

					<table class="form-table">
						<tr>
							<th scope="row">
								<label for="threshold_query_count"><?php esc_html_e( 'Query Count Threshold', 'plugin-wp-support-thisismyurl' ); ?></label>
							</th>
							<td>
								<input type="number" id="threshold_query_count" name="threshold_query_count" value="<?php echo esc_attr( $thresholds['query_count'] ); ?>" class="small-text" />
								<p class="description"><?php esc_html_e( 'Alert when query count exceeds this value.', 'plugin-wp-support-thisismyurl' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="threshold_load_time"><?php esc_html_e( 'Load Time Threshold (seconds)', 'plugin-wp-support-thisismyurl' ); ?></label>
							</th>
							<td>
								<input type="number" id="threshold_load_time" name="threshold_load_time" value="<?php echo esc_attr( $thresholds['load_time'] ); ?>" step="0.1" class="small-text" />
								<p class="description"><?php esc_html_e( 'Alert when page load time exceeds this value.', 'plugin-wp-support-thisismyurl' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="threshold_memory"><?php esc_html_e( 'Memory Usage Threshold (%)', 'plugin-wp-support-thisismyurl' ); ?></label>
							</th>
							<td>
								<input type="number" id="threshold_memory" name="threshold_memory" value="<?php echo esc_attr( $thresholds['memory'] ); ?>" class="small-text" />
								<p class="description"><?php esc_html_e( 'Alert when memory usage exceeds this percentage of the limit.', 'plugin-wp-support-thisismyurl' ); ?></p>
							</td>
						</tr>
					</table>

					<?php submit_button( __( 'Update Thresholds', 'plugin-wp-support-thisismyurl' ) ); ?>
				</form>
			</div>

			<?php
			// Display recent alerts.
			$alerts = get_transient( 'wps_performance_alerts' );
			if ( ! empty( $alerts ) && is_array( $alerts ) ) :
				?>
			<div class="wps-perf-card" style="margin-top: 20px;">
				<h3><?php esc_html_e( 'Recent Alerts', 'plugin-wp-support-thisismyurl' ); ?></h3>
				<ul class="wps-recommendation-list">
					<?php foreach ( array_slice( array_reverse( $alerts ), 0, 10 ) as $alert ) : ?>
						<li class="warning">
							<strong><?php echo esc_html( ucfirst( $alert['type'] ) ); ?></strong>
							<?php echo esc_html( $alert['message'] ); ?>
							<br />
							<small style="color: #666;">
								<?php
								/* translators: %s: time ago */
								echo esc_html( sprintf( __( '%s ago', 'plugin-wp-support-thisismyurl' ), human_time_diff( $alert['timestamp'] ) ) );
								?>
							</small>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
			<?php else : ?>
			<div class="notice notice-success inline">
				<p><?php esc_html_e( '✅ No alerts triggered recently. Your site is performing well!', 'plugin-wp-support-thisismyurl' ); ?></p>
			</div>
			<?php endif; ?>

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
						action: 'wps_performance_export',
						format: 'csv',
						days: 30,
						nonce: '<?php echo esc_js( wp_create_nonce( 'wps_performance_export' ) ); ?>'
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
						action: 'wps_performance_export',
						format: 'json',
						days: 30,
						nonce: '<?php echo esc_js( wp_create_nonce( 'wps_performance_export' ) ); ?>'
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
