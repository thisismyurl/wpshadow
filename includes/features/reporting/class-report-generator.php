<?php
declare(strict_types=1);

namespace WPShadow\Reporting;

use WPShadow\Core\KPI_Tracker;
use WPShadow\Guardian\Guardian_Activity_Logger;

/**
 * Report Generator for WPShadow Guardian & Auto-Fix System
 *
 * Generates comprehensive reports on:
 * - Diagnostics run
 * - Treatments applied
 * - Auto-fixes executed
 * - Issues fixed
 * - Time saved
 * - System health
 *
 * Features:
 * - Daily/Weekly/Monthly reports
 * - Custom date range reports
 * - Export formats (HTML, JSON, CSV)
 * - Scheduling
 * - Email delivery
 *
 * Philosophy: Show value through clear metrics.
 */
class Report_Generator {

	/**
	 * Generate report for date range
	 *
	 * @param string $start_date Start date (YYYY-MM-DD)
	 * @param string $end_date End date (YYYY-MM-DD)
	 * @param string $type Report type (summary, detailed, executive)
	 *
	 * @return array Report data
	 */
	public static function generate_report( string $start_date, string $end_date, string $type = 'summary' ): array {
		$start_date = sanitize_text_field( $start_date );
		$end_date   = sanitize_text_field( $end_date );

		$report = array(
			'title'        => 'WPShadow Report',
			'start_date'   => $start_date,
			'end_date'     => $end_date,
			'generated_at' => current_time( 'mysql' ),
			'type'         => $type,
		);

		// Add sections based on type
		$report['summary']         = self::get_summary_section( $start_date, $end_date );
		$report['kpis']            = self::get_kpi_section( $start_date, $end_date );
		$report['treatments']      = self::get_treatments_section( $start_date, $end_date );
		$report['auto_fixes']      = self::get_auto_fixes_section( $start_date, $end_date );
		$report['issues']          = self::get_issues_section( $start_date, $end_date );
		$report['recommendations'] = self::get_recommendations( $start_date, $end_date );

		if ( $type === 'detailed' ) {
			$report['events'] = Event_Logger::get_events(
				array(
					'start_date' => $start_date,
					'end_date'   => $end_date,
				),
				PHP_INT_MAX
			);
		}

		return $report;
	}

	/**
	 * Get summary section
	 *
	 * @param string $start_date Start date
	 * @param string $end_date End date
	 *
	 * @return array Summary data
	 */
	private static function get_summary_section( string $start_date, string $end_date ): array {
		$kpis = KPI_Tracker::get_summary();

		return array(
			'period_length'    => ( strtotime( $end_date ) - strtotime( $start_date ) ) / DAY_IN_SECONDS,
			'diagnostics_run'  => $kpis['diagnostics_run'] ?? 0,
			'issues_found'     => $kpis['issues_found'] ?? 0,
			'issues_fixed'     => $kpis['issues_fixed'] ?? 0,
			'time_saved'       => $kpis['time_saved'] ?? 0,
			'value_equivalent' => $kpis['value_equivalent'] ?? 0,
		);
	}

	/**
	 * Get success metrics section (what we improved for your site)
	 *
	 * @param string $start_date Start date
	 * @param string $end_date End date
	 *
	 * @return array Success metrics with novice-friendly labels and explanations
	 */
	private static function get_kpi_section( string $start_date, string $end_date ): array {
		$kpis = KPI_Tracker::get_summary();

		return array(
			'speed_improvements' => array(
				'label'       => __( 'Your Site Got Faster', 'wpshadow' ),
				'items'       => array(
					array(
						'value'       => $kpis['avg_page_load_time'] ?? 'N/A',
						'label'       => __( 'Pages Load Quicker', 'wpshadow' ),
						'explanation' => __( 'Like making your front door easier to open', 'wpshadow' ),
					),
					array(
						'value'       => $kpis['db_queries_reduced'] ?? 0,
						'label'       => __( 'Database Made Faster', 'wpshadow' ),
						'explanation' => __( 'Your site finds information quicker (organized filing cabinet)', 'wpshadow' ),
					),
					array(
						'value'       => $kpis['memory_reduced'] ?? 0,
						'label'       => __( 'Memory Usage Reduced', 'wpshadow' ),
						'explanation' => __( 'Uses less computer memory (like cleaning up RAM)', 'wpshadow' ),
					),
				),
			),
			'security_improvements' => array(
				'label'       => __( 'Your Site Got More Secure', 'wpshadow' ),
				'items'       => array(
					array(
						'value'       => $kpis['vulnerabilities_fixed'] ?? 0,
						'label'       => __( 'Security Holes Patched', 'wpshadow' ),
						'explanation' => __( 'We fixed broken locks on your doors', 'wpshadow' ),
					),
					array(
						'value'       => $kpis['plugins_updated'] ?? 0,
						'label'       => __( 'Add-ons Updated', 'wpshadow' ),
						'explanation' => __( 'Kept plugins current with latest security fixes', 'wpshadow' ),
					),
					array(
						'value'       => $kpis['security_settings'] ?? 0,
						'label'       => __( 'Security Settings Strengthened', 'wpshadow' ),
						'explanation' => __( 'Made your security settings more protective', 'wpshadow' ),
					),
				),
			),
			'maintenance' => array(
				'label'       => __( 'Site Maintenance Performed', 'wpshadow' ),
				'items'       => array(
					array(
						'value'       => $kpis['cleanup'] ?? 0,
						'label'       => __( 'Cleanup Tasks Completed', 'wpshadow' ),
						'explanation' => __( 'Removed unnecessary clutter (like spring cleaning)', 'wpshadow' ),
					),
					array(
						'value'       => $kpis['items_removed'] ?? 0,
						'label'       => __( 'Unused Items Removed', 'wpshadow' ),
						'explanation' => __( 'Deleted things you no longer need', 'wpshadow' ),
					),
					array(
						'value'       => $kpis['db_optimized'] ?? false,
						'label'       => __( 'Database Optimized', 'wpshadow' ),
						'explanation' => __( 'Organized your site data for better performance', 'wpshadow' ),
					),
				),
			),
		);
	}

	/**
	 * Get treatments section
	 *
	 * @param string $start_date Start date
	 * @param string $end_date End date
	 *
	 * @return array Treatments data
	 */
	private static function get_treatments_section( string $start_date, string $end_date ): array {
		$activity_log = Guardian_Activity_Logger::get_activity(
			array(
				'start_date' => $start_date,
				'end_date'   => $end_date,
			)
		);

		$treatments_by_type = array();
		$success_count      = 0;
		$failed_count       = 0;

		foreach ( $activity_log as $entry ) {
			if ( $entry['action'] !== 'treatment_applied' ) {
				continue;
			}

			$type    = $entry['data']['type'] ?? 'unknown';
			$success = $entry['data']['success'] ?? false;

			if ( ! isset( $treatments_by_type[ $type ] ) ) {
				$treatments_by_type[ $type ] = array(
					'total'   => 0,
					'success' => 0,
					'failed'  => 0,
				);
			}

			++$treatments_by_type[ $type ]['total'];

			if ( $success ) {
				++$treatments_by_type[ $type ]['success'];
				++$success_count;
			} else {
				++$treatments_by_type[ $type ]['failed'];
				++$failed_count;
			}
		}

		return array(
			'total_treatments' => $success_count + $failed_count,
			'successful'       => $success_count,
			'failed'           => $failed_count,
			'success_rate'     => $success_count + $failed_count > 0
				? round( ( $success_count / ( $success_count + $failed_count ) ) * 100, 1 )
				: 0,
			'by_type'          => $treatments_by_type,
		);
	}

	/**
	 * Get auto-fixes section
	 *
	 * @param string $start_date Start date
	 * @param string $end_date End date
	 *
	 * @return array Auto-fixes data
	 */
	private static function get_auto_fixes_section( string $start_date, string $end_date ): array {
		$activity_log = Guardian_Activity_Logger::get_activity(
			array(
				'start_date' => $start_date,
				'end_date'   => $end_date,
			)
		);

		$auto_fixes   = array();
		$paused_count = 0;

		foreach ( $activity_log as $entry ) {
			if ( $entry['action'] === 'auto_fix_executed' ) {
				$auto_fixes[] = $entry;
			} elseif ( $entry['action'] === 'auto_fix_paused' ) {
				++$paused_count;
			}
		}

		return array(
			'total_auto_fixes'        => count( $auto_fixes ),
			'paused_due_to_anomalies' => $paused_count,
			'average_duration_ms'     => count( $auto_fixes ) > 0
				? round( array_sum( array_column( $auto_fixes, 'duration' ) ) / count( $auto_fixes ), 2 )
				: 0,
			'no_anomaly_rate'         => count( $auto_fixes ) + $paused_count > 0
				? round( ( count( $auto_fixes ) / ( count( $auto_fixes ) + $paused_count ) ) * 100, 1 )
				: 100,
		);
	}

	/**
	 * Get issues section
	 *
	 * @param string $start_date Start date
	 * @param string $end_date End date
	 *
	 * @return array Issues data
	 */
	private static function get_issues_section( string $start_date, string $end_date ): array {
		$activity_log = Guardian_Activity_Logger::get_activity(
			array(
				'start_date' => $start_date,
				'end_date'   => $end_date,
			)
		);

		$issues_by_severity = array(
			'critical' => 0,
			'high'     => 0,
			'medium'   => 0,
			'low'      => 0,
		);

		foreach ( $activity_log as $entry ) {
			if ( $entry['action'] === 'issue_detected' ) {
				$severity = $entry['data']['severity'] ?? 'low';
				if ( isset( $issues_by_severity[ $severity ] ) ) {
					++$issues_by_severity[ $severity ];
				}
			}
		}

		return array(
			'total_issues'            => array_sum( $issues_by_severity ),
			'by_severity'             => $issues_by_severity,
			'critical_issues'         => $issues_by_severity['critical'],
			'urgent_attention_needed' => $issues_by_severity['critical'] + $issues_by_severity['high'] > 0,
		);
	}

	/**
	 * Get recommendations
	 *
	 * @param string $start_date Start date
	 * @param string $end_date End date
	 *
	 * @return array Recommendations
	 */
	private static function get_recommendations( string $start_date, string $end_date ): array {
		$recommendations = array();

		// Get latest diagnostics
		$activity_log = Guardian_Activity_Logger::get_activity(
			array(
				'start_date' => $start_date,
				'end_date'   => $end_date,
			)
		);

		// Find recurring issues
		$issue_counts = array();
		foreach ( $activity_log as $entry ) {
			if ( $entry['action'] === 'issue_detected' ) {
				$type                  = $entry['data']['type'] ?? 'unknown';
				$issue_counts[ $type ] = ( $issue_counts[ $type ] ?? 0 ) + 1;
			}
		}

		// Generate recommendations based on patterns
		arsort( $issue_counts );

		foreach ( array_slice( $issue_counts, 0, 5 ) as $issue_type => $count ) {
			if ( $count >= 3 ) {
				$recommendations[] = array(
					'issue'       => $issue_type,
					'occurrences' => $count,
					'action'      => "Address $issue_type to prevent recurring issues",
					'priority'    => $count >= 10 ? 'high' : 'medium',
				);
			}
		}

		return $recommendations;
	}

	/**
	 * Export report to HTML
	 *
	 * @param array $report Report data
	 *
	 * @return string HTML output
	 */
	public static function export_html( array $report ): string {
		ob_start();
		?>
		<!DOCTYPE html>
		<html>
		<head>
			<meta charset="UTF-8">
			<title><?php echo esc_html( $report['title'] ); ?></title>
			<style>
				body { font-family: Arial, sans-serif; }
				.report-header { background: #f0f0f0; padding: 20px; margin-bottom: 20px; }
				.section { margin: 30px 0; }
				.section h2 { border-bottom: 2px solid #0073aa; padding-bottom: 10px; }
				table { width: 100%; border-collapse: collapse; }
				th, td { padding: 10px; text-align: left; border: 1px solid #ddd; }
				th { background: #0073aa; color: white; }
				.metric { display: inline-block; margin-right: 30px; }
				.metric-value { font-size: 24px; font-weight: bold; color: #0073aa; }
			</style>
		</head>
		<body>
			<div class="report-header">
				<h1><?php echo esc_html( $report['title'] ); ?></h1>
				<p>Period: <?php echo esc_html( $report['start_date'] ); ?> to <?php echo esc_html( $report['end_date'] ); ?></p>
				<p>Generated: <?php echo esc_html( $report['generated_at'] ); ?></p>
			</div>

			<div class="section">
				<h2>Summary</h2>
				<?php foreach ( $report['summary'] as $key => $value ) : ?>
					<div class="metric">
						<div><?php echo esc_html( str_replace( '_', ' ', ucfirst( $key ) ) ); ?></div>
						<div class="metric-value"><?php echo esc_html( (string) $value ); ?></div>
					</div>
				<?php endforeach; ?>
			</div>

			<div class="section">
				<h2>Treatments</h2>
				<table>
					<tr>
						<th>Metric</th>
						<th>Value</th>
					</tr>
					<?php foreach ( $report['treatments'] as $key => $value ) : ?>
						<tr>
							<td><?php echo esc_html( str_replace( '_', ' ', ucfirst( $key ) ) ); ?></td>
							<td><?php echo esc_html( (string) $value ); ?></td>
						</tr>
					<?php endforeach; ?>
				</table>
			</div>
		</body>
		</html>
		<?php

		return ob_get_clean();
	}

	/**
	 * Export report to JSON
	 *
	 * @param array $report Report data
	 *
	 * @return string JSON output
	 */
	public static function export_json( array $report ): string {
		return json_encode( $report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
	}

	/**
	 * Export report to CSV
	 *
	 * @param array $report Report data
	 *
	 * @return string CSV output
	 */
	public static function export_csv( array $report ): string {
		$csv  = "WPShadow Report\n";
		$csv .= "Period: {$report['start_date']} to {$report['end_date']}\n";
		$csv .= "Generated: {$report['generated_at']}\n\n";

		foreach ( $report as $section => $data ) {
			if ( is_array( $data ) && $section !== 'title' ) {
				$csv .= "\n$section\n";

				foreach ( $data as $key => $value ) {
					if ( ! is_array( $value ) ) {
						$csv .= "\"$key\",\"$value\"\n";
					}
				}
			}
		}

		return $csv;
	}
}
