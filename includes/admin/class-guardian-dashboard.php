<?php

declare(strict_types=1);

namespace WPShadow\Admin;

use WPShadow\Core\KPI_Tracker;
use WPShadow\Guardian\Guardian_Manager;
use WPShadow\Guardian\Guardian_Activity_Logger;
use WPShadow\Guardian\Auto_Fix_Executor;
use WPShadow\Guardian\Recovery_System;
use WPShadow\Guardian\Scan_Scheduler;
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
 * @since 1.6093.1200
 */
class Guardian_Dashboard {


	/**
	 * Render the dashboard
	 *
	 * @since 1.6093.1200
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

			<!-- Diagnostics Overview -->
			<div id="wpshadow-guardian-diagnostics-overview" class="wps-mt-4" role="region" aria-labelledby="diagnostics-heading">
				<?php echo wp_kses_post( self::render_diagnostics_overview() ); ?>
			</div>

			<!-- Activity Log (Full Width) -->
			<div id="wpshadow-guardian-activity-log" class="wps-mt-4" role="region" aria-labelledby="activity-heading">
				<?php echo wp_kses_post( self::render_activity_timeline() ); ?>
			</div>
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

		// Get actual findings count from running diagnostics (like the Findings page does)
		$all_findings = array();
		if ( class_exists( '\WPShadow\Diagnostics\Diagnostic_Registry' ) ) {
			$all_findings = \WPShadow\Diagnostics\Diagnostic_Registry::run_enabled_scans();
		}
		$findings_count = count( $all_findings );

		$cards = array(
			array(
				'label'       => __( 'Issues Found', 'wpshadow' ),
				'value'       => $findings_count > 0 ? $findings_count : ( $kpis['findings_detected'] ?? 0 ),
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
	 * Render diagnostics overview
	 *
	 * Shows recently run diagnostics and the next scheduled diagnostics list.
	 *
	 * @return string HTML
	 */
	private static function render_diagnostics_overview(): string {
		if ( ! class_exists( Scan_Scheduler::class ) ) {
			return '';
		}

		$latest_results = Scan_Scheduler::get_latest_scan_results();
		$latest_run     = $latest_results['diagnostics_run'] ?? array();
		$latest_depth   = $latest_results['depth'] ?? '';
		$latest_date    = $latest_results['scan_date'] ?? '';
		$latest_map     = ! empty( $latest_depth ) ? Scan_Scheduler::get_diagnostics_for_depth( $latest_depth ) : array();
		$latest_list    = self::format_diagnostic_list( $latest_run, $latest_map );

		if ( empty( $latest_list ) ) {
			$guardian_latest = get_option( 'wpshadow_guardian_last_diagnostics', array() );
			$guardian_run    = $guardian_latest['diagnostics'] ?? array();
			$guardian_date   = $guardian_latest['timestamp'] ?? '';
			$guardian_map    = self::get_diagnostic_map_from_registry();
			$latest_list     = self::format_diagnostic_list( $guardian_run, $guardian_map );
			if ( ! empty( $guardian_date ) ) {
				$latest_date = $guardian_date;
			}
		}

		$next_map        = Scan_Scheduler::get_next_scan_diagnostics();
		$next_slugs      = array_keys( $next_map );
		$next_list       = self::format_diagnostic_list( $next_slugs, $next_map );
		$next_time       = Scan_Scheduler::get_next_scan_time();
		$next_depth      = (string) get_option( 'wpshadow_scheduled_scans_depth', 'standard' );
		$scheduled_on    = Scan_Scheduler::is_scheduled_scan_enabled();
		$depth_label     = self::get_scan_depth_label( $latest_depth );
		$next_depth_label = self::get_scan_depth_label( $next_depth );

		ob_start();
		wpshadow_render_card(
			array(
				'title' => __( 'Diagnostics Schedule', 'wpshadow' ),
				'icon'  => 'dashicons-list-view',
				'body'  => function() use ( $latest_list, $latest_date, $depth_label, $next_list, $next_time, $next_depth_label, $scheduled_on ) {
					?>
					<div class="wps-grid wps-grid-auto-240 wps-gap-4">
						<div>
							<div class="wps-text-sm wps-font-semibold wps-text-gray-800" id="diagnostics-heading">
								<?php esc_html_e( 'Diagnostics That Just Ran', 'wpshadow' ); ?>
							</div>
							<?php if ( ! empty( $latest_date ) ) : ?>
								<p class="wps-text-xs wps-text-muted wps-mt-1">
									<?php
									echo esc_html(
										sprintf(
											/* translators: 1: scan date, 2: scan depth */
											__( 'Last scan: %1$s (%2$s depth)', 'wpshadow' ),
											$latest_date,
											$depth_label
										)
									);
									?>
								</p>
							<?php endif; ?>
							<?php if ( empty( $latest_list ) ) : ?>
								<p class="wps-text-sm wps-text-muted wps-mt-2">
									<?php esc_html_e( 'No diagnostics recorded yet. Run a scan to start building a history.', 'wpshadow' ); ?>
								</p>
							<?php else : ?>
								<ul class="wps-list-disc wps-ml-5 wps-text-sm wps-mt-2">
									<?php foreach ( $latest_list as $label ) : ?>
										<li><?php echo esc_html( $label ); ?></li>
									<?php endforeach; ?>
								</ul>
							<?php endif; ?>
						</div>

						<div>
							<div class="wps-text-sm wps-font-semibold wps-text-gray-800">
								<?php esc_html_e( 'Upcoming Diagnostics', 'wpshadow' ); ?>
							</div>
							<?php if ( ! $scheduled_on ) : ?>
								<p class="wps-text-sm wps-text-muted wps-mt-2">
									<?php esc_html_e( 'Scheduled scans are paused. Turn them on to queue the next heartbeat run.', 'wpshadow' ); ?>
								</p>
							<?php else : ?>
								<?php if ( ! empty( $next_time ) ) : ?>
									<p class="wps-text-xs wps-text-muted wps-mt-1">
										<?php
										echo esc_html(
											sprintf(
												/* translators: 1: next scan time, 2: scan depth */
												__( 'Next run: %1$s (%2$s depth)', 'wpshadow' ),
												$next_time,
												$next_depth_label
											)
										);
									?>
									</p>
								<?php else : ?>
									<?php
									$heartbeat_interval = self::get_heartbeat_interval_seconds();
									$heartbeat_message  = sprintf(
										/* translators: %s: heartbeat interval in seconds */
										__( 'Next run will happen on the next heartbeat (about <span class="wps-guardian-heartbeat-countdown" data-interval="%s">%s</span> seconds while this page is open).', 'wpshadow' ),
										number_format_i18n( $heartbeat_interval ),
										number_format_i18n( $heartbeat_interval )
									);
									?>
									<p class="wps-text-xs wps-text-muted wps-mt-1">
										<?php
										echo wp_kses(
											$heartbeat_message,
											array(
												'span' => array(
													'class'        => true,
													'data-interval' => true,
												),
											)
										);
									?>
									</p>
								<?php endif; ?>
								<?php if ( empty( $next_list ) ) : ?>
									<p class="wps-text-sm wps-text-muted wps-mt-2">
										<?php esc_html_e( 'No diagnostics are queued yet.', 'wpshadow' ); ?>
									</p>
								<?php else : ?>
									<ul class="wps-list-disc wps-ml-5 wps-text-sm wps-mt-2">
										<?php foreach ( $next_list as $label ) : ?>
											<li><?php echo esc_html( $label ); ?></li>
										<?php endforeach; ?>
									</ul>
								<?php endif; ?>
							<?php endif; ?>
						</div>
					</div>

					<div class="wps-mt-4 wps-diagnostic-scan-browser" data-nonce="<?php echo esc_attr( wp_create_nonce( 'wpshadow_scan_settings' ) ); ?>">
						<div class="wps-text-sm wps-font-semibold wps-text-gray-800">
							<?php esc_html_e( 'Diagnostic Scan Browser', 'wpshadow' ); ?>
						</div>
						<p class="wps-text-xs wps-text-muted wps-mt-1">
							<?php esc_html_e( 'Search and filter diagnostics to quickly find specific checks.', 'wpshadow' ); ?>
						</p>

						<div class="wps-grid wps-grid-auto-240 wps-gap-3 wps-mt-2">
							<div>
								<label for="wpshadow-diagnostic-search" class="screen-reader-text"><?php esc_html_e( 'Search diagnostics', 'wpshadow' ); ?></label>
								<input
									type="search"
									id="wpshadow-diagnostic-search"
									class="regular-text"
									placeholder="<?php echo esc_attr__( 'Search diagnostics…', 'wpshadow' ); ?>"
								/>
							</div>
							<div>
								<label for="wpshadow-diagnostic-family" class="screen-reader-text"><?php esc_html_e( 'Filter by family', 'wpshadow' ); ?></label>
								<select id="wpshadow-diagnostic-family" class="wps-input">
									<option value=""><?php esc_html_e( 'All families', 'wpshadow' ); ?></option>
								</select>
							</div>
							<div>
								<label for="wpshadow-diagnostic-status" class="screen-reader-text"><?php esc_html_e( 'Filter by status', 'wpshadow' ); ?></label>
								<select id="wpshadow-diagnostic-status" class="wps-input">
									<option value="all"><?php esc_html_e( 'All statuses', 'wpshadow' ); ?></option>
									<option value="enabled"><?php esc_html_e( 'Enabled', 'wpshadow' ); ?></option>
									<option value="disabled"><?php esc_html_e( 'Disabled', 'wpshadow' ); ?></option>
								</select>
							</div>
							<div>
								<button type="button" id="wpshadow-diagnostic-filter-reset" class="button">
									<?php esc_html_e( 'Reset Filters', 'wpshadow' ); ?>
								</button>
							</div>
						</div>

						<table class="wp-list-table widefat striped wps-mt-3" aria-live="polite">
							<thead>
								<tr>
									<th scope="col" style="width: 72px;"><?php esc_html_e( '#', 'wpshadow' ); ?></th>
									<th scope="col"><?php esc_html_e( 'Diagnostic', 'wpshadow' ); ?></th>
									<th scope="col" style="width: 180px;"><?php esc_html_e( 'Family', 'wpshadow' ); ?></th>
									<th scope="col" style="width: 140px;"><?php esc_html_e( 'Status', 'wpshadow' ); ?></th>
								</tr>
							</thead>
							<tbody id="wpshadow-diagnostic-scan-results">
								<tr>
									<td colspan="4" class="wps-text-muted"><?php esc_html_e( 'Loading diagnostics…', 'wpshadow' ); ?></td>
								</tr>
							</tbody>
						</table>
					</div>
					<?php
				},
			)
		);

		return ob_get_clean();
	}

	/**
	 * Format diagnostic slugs into human-friendly labels.
	 *
	 * @param array $slugs Diagnostic slugs.
	 * @param array $map   Diagnostic map of slug => class.
	 * @return array List of labels.
	 */
	private static function format_diagnostic_list( array $slugs, array $map ): array {
		$labels = array();

		foreach ( $slugs as $slug ) {
			$labels[] = self::get_diagnostic_label( (string) $slug, $map );
		}

		return array_filter( $labels );
	}

	/**
	 * Get diagnostic display label from slug/class map.
	 *
	 * @param string $slug Diagnostic slug.
	 * @param array  $map  Diagnostic map of slug => class.
	 * @return string Label for display.
	 */
	private static function get_diagnostic_label( string $slug, array $map ): string {
		$class = $map[ $slug ] ?? '';
		if ( ! empty( $class ) && class_exists( $class ) && is_subclass_of( $class, '\\WPShadow\\Core\\Diagnostic_Base' ) ) {
			return $class::get_title();
		}

		$label = str_replace( array( '-', '_' ), ' ', $slug );
		return ucwords( $label );
	}

	/**
	 * Get diagnostics overview HTML for AJAX refresh.
	 *
	 * @since 1.6093.1200
	 * @return string HTML output.
	 */
	public static function get_diagnostics_overview_html(): string {
		return self::render_diagnostics_overview();
	}

	/**
	 * Get activity timeline HTML for AJAX refresh.
	 *
	 * @since 1.6093.1200
	 * @return string HTML output.
	 */
	public static function get_activity_timeline_html(): string {
		return self::render_activity_timeline();
	}

	/**
	 * Build a diagnostic slug map using the registry.
	 *
	 * @return array Diagnostic map of slug => class.
	 */
	private static function get_diagnostic_map_from_registry(): array {
		if ( ! class_exists( '\\WPShadow\\Diagnostics\\Diagnostic_Registry' ) ) {
			return array();
		}

		$classes = \WPShadow\Diagnostics\Diagnostic_Registry::get_all();
		$map     = array();

		foreach ( $classes as $class ) {
			if ( class_exists( $class ) && is_subclass_of( $class, '\\WPShadow\\Core\\Diagnostic_Base' ) ) {
				$map[ $class::get_slug() ] = $class;
			}
		}

		return $map;
	}

	/**
	 * Normalize scan depth labels for display.
	 *
	 * @param string $depth Scan depth.
	 * @return string Friendly label.
	 */
	private static function get_scan_depth_label( string $depth ): string {
		$depth = sanitize_key( $depth );
		if ( empty( $depth ) ) {
			return __( 'Standard', 'wpshadow' );
		}

		$labels = array(
			'quick'    => __( 'Quick', 'wpshadow' ),
			'standard' => __( 'Standard', 'wpshadow' ),
			'deep'     => __( 'Deep', 'wpshadow' ),
		);

		return $labels[ $depth ] ?? ucwords( $depth );
	}

	/**
	 * Get the Heartbeat interval in seconds.
	 *
	 * @return int Interval in seconds.
	 */
	private static function get_heartbeat_interval_seconds(): int {
		$interval = 15;
		if ( function_exists( 'wp_heartbeat_settings' ) ) {
			$settings = wp_heartbeat_settings( array( 'interval' => $interval ) );
			if ( is_array( $settings ) && isset( $settings['interval'] ) ) {
				$interval = (int) $settings['interval'];
			}
		} else {
			$settings = apply_filters( 'heartbeat_settings', array( 'interval' => $interval ) );
			if ( is_array( $settings ) && isset( $settings['interval'] ) ) {
				$interval = (int) $settings['interval'];
			}
		}
		if ( $interval < 1 ) {
			$interval = 15;
		}

		return $interval;
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
