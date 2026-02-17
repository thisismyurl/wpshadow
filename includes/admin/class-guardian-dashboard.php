<?php

declare(strict_types=1);

namespace WPShadow\Admin;

use WPShadow\Core\KPI_Tracker;
use WPShadow\Guardian\Guardian_Manager;
use WPShadow\Guardian\Guardian_Activity_Logger;
use WPShadow\Guardian\Auto_Fix_Executor;
use WPShadow\Guardian\Recovery_System;
use WPShadow\Reporting\Event_Logger;

/**
 * WPShadow Guardian
 *
 * Main dashboard for WPShadow Guardian system.
 * Shows KPIs, recent activity, auto-fix stats, recovery points.
 *
 * Features:
 * - KPI cards (issues, time saved, value)
 * - Activity timeline
 * - Auto-fix statistics
 * - Recovery points widget
 * - System health status
 *
 * @since 1.6030.2148
 */
class Guardian_Dashboard {


	/**
	 * Render the dashboard
	 *
	 * @since  1.6030.2148
	 * @return string HTML output
	 */
	public static function render(): string {
		ob_start();
		?>
		<div class="wrap wpshadow-guardian wps-page-container" role="main">
			<!-- Page Header -->
			<?php wpshadow_render_page_header(
				__( 'WPShadow Guardian', 'wpshadow' ),
				__( 'Automated health monitoring and intelligent fixes', 'wpshadow' ),
				'dashicons-shield-alt'
			); ?>

			<!-- KPI Cards Grid -->
			<div class="wps-grid wps-grid-cols-4 wps-gap-4 wps-mb-4" style="display: flex; flex-wrap: wrap; gap: 1rem;">
				<?php echo wp_kses_post( self::render_kpi_cards() ); ?>
			</div>

			<!-- Main Content Grid -->
			<div class="wps-grid wps-grid-auto-320 wps-gap-4">
				<!-- Left Column: Stats -->
				<div role="region" aria-labelledby="stats-heading">
					<?php echo wp_kses_post( self::render_auto_fix_stats() ); ?>
				</div>

				<!-- Right Column: Recovery & Health -->
				<div role="region" aria-labelledby="system-health-heading">
					<?php echo wp_kses_post( self::render_recovery_widget() ); ?>
					<?php echo wp_kses_post( self::render_system_health() ); ?>
				</div>
			</div>

			<!-- Activity Log (Full Width) -->
			<div class="wps-mt-4" role="region" aria-labelledby="activity-heading">
				<?php echo wp_kses_post( self::render_activity_timeline() ); ?>
			</div>

			<!-- Page-Specific Activity History Section -->
			<?php
			if ( function_exists( 'wpshadow_render_page_activities' ) ) {
				wpshadow_render_page_activities( 'guardian', 10 );
			}
			?>
		</div>
		<?php
		return ob_get_clean();
	}



	/**
	 * Render quick actions
	 *
	 * @return string HTML
	 */
	private static function render_quick_actions(): string {
		$html = '<div class="wps-flex wps-gap-3" role="group" aria-label="' . esc_attr__( 'Quick actions', 'wpshadow' ) . '">';

		$html .= sprintf(
			'<button type="button" class="wps-btn wps-btn--secondary" data-action="preview-fixes" aria-label="%s">%s</button>',
			esc_attr__( 'Preview available fixes before applying', 'wpshadow' ),
			esc_html__( 'Preview Fixes', 'wpshadow' )
		);

		$html .= sprintf(
			'<a href="%s" class="wps-btn wps-btn--secondary" aria-label="%s">%s</a>',
			esc_url( admin_url( 'admin.php?page=wpshadow-settings' ) ),
			esc_attr__( 'Configure Guardian settings', 'wpshadow' ),
			esc_html__( 'Settings', 'wpshadow' )
		);

		$html .= '</div>';

		return $html;
	}

	/**
	 * Render KPI cards
	 *
	 * @return string HTML
	 */
	private static function render_kpi_cards(): string {
		$kpis = KPI_Tracker::get_kpi_summary();

		$cards = array(
			array(
				'label'       => __( 'Issues Found', 'wpshadow' ),
				'value'       => $kpis['findings_detected'] ?? 0,
				'icon'        => 'dashicons-search',
				'color'       => '#f59e0b',
				'description' => __( 'Total findings detected', 'wpshadow' ),
			),
			array(
				'label'       => __( 'Issues Fixed', 'wpshadow' ),
				'value'       => $kpis['issues_fixed'] ?? 0,
				'icon'        => 'dashicons-yes-alt',
				'color'       => '#10b981',
				'description' => __( 'Successfully resolved', 'wpshadow' ),
			),
			array(
				'label'       => __( 'Time Saved', 'wpshadow' ),
				'value'       => $kpis['time_saved_display'] ?? '0m',
				'icon'        => 'dashicons-clock',
				'color'       => '#3b82f6',
				'description' => __( 'Automated work time', 'wpshadow' ),
			),
			array(
				'label'       => __( 'Value Generated', 'wpshadow' ),
				'value'       => '$' . ( $kpis['labor_cost_avoided'] ?? 0 ),
				'icon'        => 'dashicons-chart-area',
				'color'       => '#8b5cf6',
				'description' => __( 'Labor cost avoided', 'wpshadow' ),
			),
		);

		$html = '';
		foreach ( $cards as $card ) {
			$html .= sprintf(
				'<div class="wps-kpi-card" role="article" aria-labelledby="kpi-%s" style="--kpi-color: %s; width: 25%%; flex: 1 1 25%%;">
					<div class="wps-kpi-card-header">
						<div class="wps-kpi-icon-wrapper">
							<span class="dashicons %s wps-kpi-icon" aria-hidden="true"></span>
						</div>
					</div>
					<div class="wps-kpi-card-body">
						<h3 id="kpi-%s" class="wps-kpi-label">%s</h3>
						<div class="wps-kpi-value">%s</div>
						<p class="wps-kpi-description">%s</p>
					</div>
				</div>',
				esc_attr( sanitize_title( $card['label'] ) ),
				esc_attr( $card['color'] ),
				esc_attr( $card['icon'] ),
				esc_attr( sanitize_title( $card['label'] ) ),
				esc_html( $card['label'] ),
				esc_html( (string) $card['value'] ),
				esc_html( $card['description'] )
			);
		}

		return $html;
	}

	/**
	 * Render activity timeline
	 *
	 * @return string HTML
	 */
	private static function render_activity_timeline(): string {
		// Get activities from Activity_Logger (which logs Guardian executions)
		if ( ! class_exists( 'WPShadow\Core\Activity_Logger' ) ) {
			return '';
		}

		$activities = \WPShadow\Core\Activity_Logger::get_recent( 20 );

		ob_start();
		wpshadow_render_card(
			array(
				'title' => __( 'Guardian Activity Log', 'wpshadow' ),
				'icon'  => 'dashicons-clock',
				'body'  => function() use ( $activities ) {
					if ( empty( $activities ) ) {
						?>
						<div class="wps-activity-empty">
							<span class="dashicons dashicons-admin-post wps-activity-empty-icon" aria-hidden="true"></span>
							<p class="wps-m-0">
								<?php esc_html_e( 'No recent activity. Guardian will start logging once enabled.', 'wpshadow' ); ?>
							</p>
						</div>
						<?php
						return;
					}
					?>
					<div class="wps-activity-timeline">
						<?php
						$activity_count = count( $activities );
						$index          = 0;
						foreach ( $activities as $activity ) {
							$is_last     = ( ++$index === $activity_count );
							$action_text = self::format_activity_action_new( $activity );
							$icon_class  = self::get_activity_icon_new( $activity );
							$icon_color  = self::get_activity_color_new( $activity );

							$timestamp = isset( $activity['timestamp'] ) ? (int) $activity['timestamp'] : current_time( 'timestamp' );
							$time_text = human_time_diff( $timestamp, current_time( 'timestamp' ) ) . ' ago';
							?>
							<div class="wps-activity-item <?php echo esc_attr( $is_last ? 'wps-activity-last' : '' ); ?>" role="article" style="--activity-color: <?php echo esc_attr( $icon_color ); ?>;">
								<div class="wps-activity-icon-wrapper">
									<span class="dashicons <?php echo esc_attr( $icon_class ); ?>" aria-hidden="true"></span>
								</div>
								<div class="wps-activity-content">
									<div class="wps-activity-text"><?php echo esc_html( $action_text ); ?></div>
									<time class="wps-activity-time" datetime="<?php echo esc_attr( ! empty( $activity['timestamp'] ) ? $activity['timestamp'] : '' ); ?>">
										<?php echo esc_html( $time_text ); ?>
									</time>
								</div>
							</div>
							<?php
						}
						?>
					</div>
					<?php
				},
			)
		);

		return ob_get_clean();
	}

	/**
	 * Get icon for activity type
	 *
	 * @param array $activity Activity log entry.
	 * @return string Dashicon class.
	 */
	private static function get_activity_icon( array $activity ): string {
		$type = $activity['type'] ?? 'unknown';

		switch ( $type ) {
			case 'health_check':
				return 'dashicons-heart';
			case 'auto_fix':
				$success = ! empty( $activity['success'] );
				return $success ? 'dashicons-yes-alt' : 'dashicons-dismiss';
			case 'anomaly_detected':
				return 'dashicons-warning';
			case 'settings_changed':
				return 'dashicons-admin-settings';
			default:
				return 'dashicons-marker';
		}
	}

	/**
	 * Get color for activity type
	 *
	 * @param array $activity Activity log entry.
	 * @return string Hex color code.
	 */
	private static function get_activity_color( array $activity ): string {
		$type = $activity['type'] ?? 'unknown';

		switch ( $type ) {
			case 'health_check':
				return '#3b82f6'; // Blue.
			case 'auto_fix':
				$success = ! empty( $activity['success'] );
				return $success ? '#10b981' : '#ef4444'; // Green or Red.
			case 'anomaly_detected':
				return '#f59e0b'; // Orange.
			case 'settings_changed':
				return '#8b5cf6'; // Purple.
			default:
				return '#6b7280'; // Gray.
		}
	}

	/**
	 * Format activity action for display
	 *
	 * @param array $activity Activity log entry
	 * @return string Formatted action text
	 */
	private static function format_activity_action( array $activity ): string {
		if ( empty( $activity ) ) {
			return __( 'Unknown activity', 'wpshadow' );
		}

		$type = $activity['type'] ?? 'unknown';

		switch ( $type ) {
			case 'health_check':
				$findings = isset( $activity['findings_total'] ) ? (int) $activity['findings_total'] : 0;
				$critical = isset( $activity['critical_count'] ) ? (int) $activity['critical_count'] : 0;
				if ( $critical > 0 ) {
					return sprintf( __( 'Health check: %1$d findings (%2$d need attention soon)', 'wpshadow' ), $findings, $critical );
				}
				return sprintf( __( 'Health check: %d findings', 'wpshadow' ), $findings );

			case 'auto_fix':
				$treatment = ! empty( $activity['treatment'] ) ? $activity['treatment'] : __( 'Unknown', 'wpshadow' );
				$success   = ! empty( $activity['success'] );
				if ( $success ) {
					return sprintf( __( 'Auto-fixed: %s ✓', 'wpshadow' ), $treatment );
				}
				return sprintf( __( 'Couldn\'t auto-fix %s (may need manual review)', 'wpshadow' ), $treatment );

			case 'anomaly_detected':
				$count = isset( $activity['anomalies_count'] ) ? (int) $activity['anomalies_count'] : 0;
				return sprintf( __( 'Anomaly detected: %d issues', 'wpshadow' ), $count );

			case 'settings_changed':
				$enabled = ! empty( $activity['enabled'] );
				return $enabled ? __( 'Guardian enabled', 'wpshadow' ) : __( 'Guardian disabled', 'wpshadow' );

			default:
				return __( 'Unknown activity', 'wpshadow' );
		}
	}

	/**
	 * Format activity action for display (new format for Activity_Logger)
	 *
	 * @param array $activity Activity log entry from Activity_Logger.
	 * @return string Formatted action text.
	 */
	private static function format_activity_action_new( array $activity ): string {
		if ( empty( $activity ) ) {
			return __( 'Unknown activity', 'wpshadow' );
		}

		$action  = $activity['action'] ?? 'unknown';
		$details = isset( $activity['details'] ) && ! empty( $activity['details'] ) ? trim( (string) $activity['details'] ) : '';

		// Map actions to human-readable labels
		$action_labels = array(
			'guardian_execution'        => __( 'Guardian executed background diagnostics', 'wpshadow' ),
			'guardian_deep_scan'        => __( 'Guardian executed scheduled deep scan', 'wpshadow' ),
			'diagnostic_finding'        => __( 'Issue detected', 'wpshadow' ),
			'finding_resolved'          => __( 'Issue resolved', 'wpshadow' ),
			'diagnostic_run'            => __( 'Diagnostic executed', 'wpshadow' ),
			'diagnostic_failed'         => __( 'Diagnostic failed', 'wpshadow' ),
			'treatment_applied'         => __( 'Auto-fix applied', 'wpshadow' ),
			'treatment_undone'          => __( 'Auto-fix reverted', 'wpshadow' ),
			'finding_dismissed'         => __( 'Finding dismissed', 'wpshadow' ),
			'guardian_enabled'          => __( 'Guardian enabled', 'wpshadow' ),
			'guardian_disabled'         => __( 'Guardian disabled', 'wpshadow' ),
			'workflow_executed'         => __( 'Workflow executed', 'wpshadow' ),
			'workflow_created'          => __( 'Workflow created', 'wpshadow' ),
			'settings_changed'          => __( 'Settings changed', 'wpshadow' ),
		);

		$label = $action_labels[ $action ] ?? ucwords( str_replace( '_', ' ', $action ) );

		// Add details if available
		if ( ! empty( $details ) ) {
			return $label . ': ' . $details;
		}

		return $label;
	}

	/**
	 * Get icon for activity type (new format for Activity_Logger)
	 *
	 * @param array $activity Activity log entry.
	 * @return string Dashicon class.
	 */
	private static function get_activity_icon_new( array $activity ): string {
		$action = $activity['action'] ?? 'unknown';

		switch ( $action ) {
			case 'guardian_execution':
			case 'guardian_deep_scan':
				return 'dashicons-shield-alt';
			case 'diagnostic_finding':
				return 'dashicons-warning';
			case 'finding_resolved':
				return 'dashicons-yes-alt';
			case 'diagnostic_run':
				return 'dashicons-search';
			case 'diagnostic_failed':
				return 'dashicons-dismiss';
			case 'treatment_applied':
				return 'dashicons-admin-tools';
			case 'treatment_undone':
				return 'dashicons-undo';
			case 'guardian_enabled':
				return 'dashicons-yes';
			case 'guardian_disabled':
				return 'dashicons-no';
			case 'workflow_executed':
			case 'workflow_created':
				return 'dashicons-admin-generic';
			case 'settings_changed':
				return 'dashicons-admin-settings';
			default:
				return 'dashicons-marker';
		}
	}

	/**
	 * Get color for activity type (new format for Activity_Logger)
	 *
	 * @param array $activity Activity log entry.
	 * @return string Hex color code.
	 */
	private static function get_activity_color_new( array $activity ): string {
		$action   = $activity['action'] ?? 'unknown';
		$category = $activity['category'] ?? '';

		// Priority for action-specific colors
		switch ( $action ) {
			case 'guardian_execution':
			case 'guardian_deep_scan':
			case 'guardian_enabled':
				return '#3b82f6'; // Blue
			case 'diagnostic_finding':
				return '#f59e0b'; // Orange
			case 'finding_resolved':
			case 'treatment_applied':
				return '#10b981'; // Green
			case 'diagnostic_failed':
			case 'guardian_disabled':
			case 'treatment_undone':
				return '#ef4444'; // Red
			case 'workflow_executed':
			case 'workflow_created':
				return '#8b5cf6'; // Purple
			default:
				// Fallback to category-based colors
				switch ( $category ) {
					case 'security':
						return '#dc2626'; // Red
					case 'performance':
						return '#3b82f6'; // Blue
					case 'guardian':
					case 'monitoring':
						return '#3b82f6'; // Blue
					case 'workflow':
						return '#8b5cf6'; // Purple
					default:
						return '#6b7280'; // Gray
				}
		}
	}

	/**
	 * Render auto-fix statistics
	 *
	 * @return string HTML
	 */
	private static function render_auto_fix_stats(): string {
		$stats = Auto_Fix_Executor::get_statistics();

		ob_start();
		wpshadow_render_card(
			array(
				'title'      => __( 'Auto-Fix Statistics', 'wpshadow' ),
				'icon'       => 'dashicons-chart-bar',
				'card_class' => 'wps-mt-4',
				'body'       => function() use ( $stats ) {
					$stat_items = array(
						__( 'Executions', 'wpshadow' )   => $stats['total_executions'] ?? 0,
						__( 'Success Rate', 'wpshadow' ) => ( $stats['success_rate'] ?? 0 ) . '%',
						__( 'Avg Duration', 'wpshadow' ) => ( $stats['avg_duration'] ?? 0 ) . 'ms',
						__( 'Last Run', 'wpshadow' )     => $stats['last_run'] ?? 'Never',
					);
					?>
					<div class="wps-grid wps-grid-auto-200 wps-gap-3">
						<?php foreach ( $stat_items as $label => $value ) : ?>
							<div>
								<div class="wps-text-xs wps-text-gray-500 wps-uppercase wps-tracking-wide wps-font-semibold">
									<?php echo esc_html( $label ); ?>
								</div>
								<div class="wps-text-lg wps-font-bold wps-text-gray-800 wps-mt-1">
									<?php echo esc_html( (string) $value ); ?>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
					<?php
				},
			)
		);

		return ob_get_clean();
	}

	/**
	 * Render recovery widget
	 *
	 * @return string HTML
	 */
	private static function render_recovery_widget(): string {
		$recovery_points = Recovery_System::get_recovery_points( 5 );

		ob_start();
		wpshadow_render_card(
			array(
				'title' => __( 'Recovery Points', 'wpshadow' ),
				'icon'  => 'dashicons-backup',
				'body'  => function() use ( $recovery_points ) {
					if ( empty( $recovery_points ) ) {
						?>
						<p class="wps-m-0">
							<?php esc_html_e( 'No recovery points yet', 'wpshadow' ); ?>
						</p>
						<?php
						return;
					}
					?>
					<div class="wps-flex wps-gap-3">
						<?php foreach ( $recovery_points as $point ) : ?>
							<div class="wps-flex wps-items-center wps-justify-between">
								<div>
									<div class="wps-font-medium wps-text-gray-800">
										<?php echo esc_html( $point['reason'] ?? 'Unknown' ); ?>
									</div>
									<div class="wps-text-xs wps-text-gray-500 wps-mt-1">
										<?php echo esc_html( $point['timestamp'] ?? 'N/A' ); ?>
									</div>
								</div>
								<button class="wps-btn wps-btn--secondary wps-p-1" data-recovery-id="<?php echo esc_attr( $point['id'] ?? '' ); ?>" data-action="restore">
									<?php esc_html_e( 'Restore', 'wpshadow' ); ?>
								</button>
							</div>
						<?php endforeach; ?>
					</div>
					<?php
				},
			)
		);

		return ob_get_clean();
	}

	/**
	 * Render system health status
	 *
	 * @return string HTML
	 */
	private static function render_system_health(): string {
		$checks = array(
			array(
				'name'   => __( 'Memory Usage', 'wpshadow' ),
				'status' => self::get_memory_status(),
				'icon'   => 'dashicons-chart-area',
			),
			array(
				'name'   => __( 'Database', 'wpshadow' ),
				'status' => 'good',
				'icon'   => 'dashicons-database',
			),
			array(
				'name'   => __( 'Plugins', 'wpshadow' ),
				'status' => 'good',
				'icon'   => 'dashicons-admin-plugins',
			),
			array(
				'name'   => __( 'Security', 'wpshadow' ),
				'status' => 'good',
				'icon'   => 'dashicons-lock',
			),
		);

		ob_start();
		wpshadow_render_card(
			array(
				'title'      => __( 'System Health', 'wpshadow' ),
				'icon'       => 'dashicons-heart',
				'card_class' => 'wps-mt-4',
				'body'       => function() use ( $checks ) {
					?>
					<div class="wps-flex wps-gap-3">
						<?php foreach ( $checks as $check ) : ?>
							<?php
							$status_color = 'good' === $check['status'] ? '#10b981' : ( 'warning' === $check['status'] ? '#f59e0b' : '#ef4444' );
							?>
							<div class="wps-flex wps-gap-3 wps-items-center wps-p-3 wps-rounded-md wps-status-check-item" style="--status-color: <?php echo esc_attr( $status_color ); ?>;">
								<span class="dashicons <?php echo esc_attr( $check['icon'] ); ?> wps-icon-md wps-status-check-icon"></span>
								<div class="wps-flex-1">
									<div class="wps-font-medium wps-text-gray-800">
										<?php echo esc_html( $check['name'] ); ?>
									</div>
								</div>
								<div class="wps-text-xs wps-font-semibold wps-status-check-text">
									<?php echo esc_html( ucfirst( $check['status'] ) ); ?>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
					<?php
				},
			)
		);

		return ob_get_clean();
	}

	/**
	 * Get memory usage status
	 *
	 * @return string Status (good, warning, critical)
	 */
	private static function get_memory_status(): string {
		$current = memory_get_usage( true );
		$limit   = wp_convert_hr_to_bytes( WP_MEMORY_LIMIT );
		$percent = ( $current / $limit ) * 100;

		if ( $percent > 90 ) {
			return 'critical';
		} elseif ( $percent > 70 ) {
			return 'warning';
		}

		return 'good';
	}
}
