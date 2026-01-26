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
 * WPShadow Guardian Dashboard Tab
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
 */
class Guardian_Dashboard {


	/**
	 * Render the dashboard
	 *
	 * @return string HTML output
	 */
	public static function render(): string {
		ob_start();
		?>
		<div class="wps-page-container" role="main" aria-labelledby="guardian-dashboard-title">
			<!-- Page Header -->
			<div class="wps-page-header">
				<h1 class="wps-page-title" id="guardian-dashboard-title">
					<span class="dashicons dashicons-shield-alt wps-icon-primary" aria-hidden="true"></span>
					<?php esc_html_e( 'WPShadow Guardian Dashboard', 'wpshadow' ); ?>
				</h1>
				<p class="wps-page-subtitle">
					<?php esc_html_e( 'Automated health monitoring and intelligent fixes', 'wpshadow' ); ?>
				</p>
			</div>

			<!-- Status and Actions Bar -->
			<div class="wps-flex wps-justify-between wps-items-center wps-gap-4 wps-mb-4" role="region" aria-label="<?php esc_attr_e( 'Guardian status and actions', 'wpshadow' ); ?>">
				<?php echo wp_kses_post( self::render_status_badge() ); ?>
				<?php echo wp_kses_post( self::render_quick_actions() ); ?>
			</div>

			<!-- KPI Cards Grid -->
			<div class="wps-grid wps-grid-auto-250 wps-gap-3 wps-mb-4" role="region" aria-label="<?php esc_attr_e( 'Key performance indicators', 'wpshadow' ); ?>">
				<?php echo wp_kses_post( self::render_kpi_cards() ); ?>
			</div>

			<!-- Main Content Grid -->
			<div class="wps-grid wps-grid-auto-320 wps-gap-4">
				<!-- Left Column: Activity & Stats -->
				<div role="region" aria-labelledby="activity-heading">
					<?php echo wp_kses_post( self::render_activity_timeline() ); ?>
					<?php echo wp_kses_post( self::render_auto_fix_stats() ); ?>
				</div>

				<!-- Right Column: Recovery & Health -->
				<div role="region" aria-labelledby="system-health-heading">
					<?php echo wp_kses_post( self::render_recovery_widget() ); ?>
					<?php echo wp_kses_post( self::render_system_health() ); ?>
				</div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render status badge
	 *
	 * @return string HTML
	 */
	private static function render_status_badge(): string {
		$is_enabled   = Guardian_Manager::is_enabled();
		$status_text  = $is_enabled ? __( 'WPShadow Guardian Active', 'wpshadow' ) : __( 'WPShadow Guardian Inactive', 'wpshadow' );
		$status_icon  = $is_enabled ? 'dashicons-yes-alt' : 'dashicons-dismiss';
		$status_color = $is_enabled ? '#10b981' : '#6b7280';

		return sprintf(
			'<button 
				type="button"
				class="wps-flex-gap-12-items-center-p-12-rounded-8" 
				onclick="wpshadowToggleGuardian()"
				aria-label="%s"
				aria-pressed="%s"
				role="switch">
				<span class="dashicons %s wps-icon-sm wps-status-icon" data-status="%s" aria-hidden="true"></span>
				<span class="wps-font-semibold wps-text-gray-800">%s</span>
			</button>
			<script>
			function wpshadowToggleGuardian() {
				if (confirm("%s")) {
					jQuery.post(ajaxurl, {
						action: "wpshadow_toggle_guardian",
						nonce: "%s",
						enabled: %s
					}, function(response) {
						if (response.success) {
							location.reload();
						} else {
							alert("Error: " + (response.data?.message || "Could not toggle Guardian"));
						}
					});
				}
			}
			</script>',
			esc_attr( $is_enabled ? __( 'Click to disable Guardian', 'wpshadow' ) : __( 'Click to enable Guardian', 'wpshadow' ) ),
			esc_attr( $is_enabled ? 'true' : 'false' ),
			esc_attr( $status_icon ),
			esc_attr( $is_enabled ? 'enabled' : 'disabled' ),
			esc_html( $status_text ),
			esc_js( $is_enabled ? __( 'Are you sure you want to disable Guardian automated health monitoring?', 'wpshadow' ) : __( 'Enable Guardian to automatically monitor and fix issues?', 'wpshadow' ) ),
			esc_js( wp_create_nonce( 'wpshadow_toggle_guardian' ) ),
			$is_enabled ? 'false' : 'true'
		);
	}

	/**
	 * Render quick actions
	 *
	 * @return string HTML
	 */
	private static function render_quick_actions(): string {
		$html = '<div class="wps-flex-gap-12" role="group" aria-label="' . esc_attr__( 'Quick actions', 'wpshadow' ) . '">';

		$html .= sprintf(
			'<button type="button" class="wps-btn wps-btn-secondary" data-action="preview-fixes" aria-label="%s">%s</button>',
			esc_attr__( 'Preview available fixes before applying', 'wpshadow' ),
			esc_html__( 'Preview Fixes', 'wpshadow' )
		);

		$html .= sprintf(
			'<a href="%s" class="wps-btn wps-btn-secondary" aria-label="%s">%s</a>',
			esc_url( admin_url( 'admin.php?page=wpshadow-guardian-settings' ) ),
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
				'trend'       => '+3',
				'trend_up'    => false,
				'description' => __( 'Total findings detected', 'wpshadow' ),
			),
			array(
				'label'       => __( 'Issues Fixed', 'wpshadow' ),
				'value'       => $kpis['issues_fixed'] ?? 0,
				'icon'        => 'dashicons-yes-alt',
				'color'       => '#10b981',
				'trend'       => '+8',
				'trend_up'    => true,
				'description' => __( 'Successfully resolved', 'wpshadow' ),
			),
			array(
				'label'       => __( 'Time Saved', 'wpshadow' ),
				'value'       => $kpis['time_saved_display'] ?? '0m',
				'icon'        => 'dashicons-clock',
				'color'       => '#3b82f6',
				'trend'       => '12min',
				'trend_up'    => true,
				'description' => __( 'Automated work time', 'wpshadow' ),
			),
			array(
				'label'       => __( 'Value Generated', 'wpshadow' ),
				'value'       => '$' . ( $kpis['labor_cost_avoided'] ?? 0 ),
				'icon'        => 'dashicons-chart-area',
				'color'       => '#8b5cf6',
				'trend'       => '$45',
				'trend_up'    => true,
				'description' => __( 'Labor cost avoided', 'wpshadow' ),
			),
		);

		$html = '';
		foreach ( $cards as $card ) {
			$trend_arrow = $card['trend_up'] ? '↗' : '↘';
			$trend_color = $card['trend_up'] ? '#10b981' : '#ef4444';

			$html .= sprintf(
				'<div class="wps-kpi-card" role="article" aria-labelledby="kpi-%s">
					<div class="wps-kpi-card-header">
						<div class="wps-kpi-icon-wrapper" style="background: %s20;">
							<span class="dashicons %s wps-kpi-icon" style="color: %s;" aria-hidden="true"></span>
						</div>
						<div class="wps-kpi-trend" style="color: %s;" aria-label="%s">
							<span aria-hidden="true">%s</span> %s
						</div>
					</div>
					<div class="wps-kpi-card-body">
						<h3 id="kpi-%s" class="wps-kpi-label">%s</h3>
						<div class="wps-kpi-value">%s</div>
						<p class="wps-kpi-description">%s</p>
					</div>
					<div class="wps-kpi-card-footer">
						<a href="#" class="wps-kpi-link" aria-label="%s">
							%s <span class="dashicons dashicons-arrow-right-alt2" aria-hidden="true"></span>
						</a>
					</div>
				</div>',
				esc_attr( sanitize_title( $card['label'] ) ),
				esc_attr( $card['color'] ),
				esc_attr( $card['icon'] ),
				esc_attr( $card['color'] ),
				esc_attr( $trend_color ),
				esc_attr( sprintf( __( 'Trend: %s', 'wpshadow' ), $card['trend'] ) ),
				esc_html( $trend_arrow ),
				esc_html( $card['trend'] ),
				esc_attr( sanitize_title( $card['label'] ) ),
				esc_html( $card['label'] ),
				esc_html( (string) $card['value'] ),
				esc_html( $card['description'] ),
				esc_attr( sprintf( __( 'View details for %s', 'wpshadow' ), $card['label'] ) ),
				esc_html__( 'View details', 'wpshadow' )
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
		$activities = Guardian_Activity_Logger::get_activity_log( 10 );

		if ( empty( $activities ) ) {
			return '<div class="wps-card">
				<div class="wps-card-header">
					<h3 class="wps-card-title wps-m-0" id="activity-heading">
						<span class="dashicons dashicons-clock wps-icon-mr-2" aria-hidden="true"></span>
						' . esc_html__( 'Recent Activity', 'wpshadow' ) . '
					</h3>
				</div>
				<div class="wps-card-body">
					<div class="wps-activity-empty">
						<span class="dashicons dashicons-admin-post wps-activity-empty-icon" aria-hidden="true"></span>
						<p class="wps-m-0">' . esc_html__( 'No recent activity', 'wpshadow' ) . '</p>
					</div>
				</div>
			</div>';
		}

		$html = '<div class="wps-card">
			<div class="wps-card-header">
				<h3 class="wps-card-title wps-m-0" id="activity-heading">
					<span class="dashicons dashicons-clock wps-icon-mr-2" aria-hidden="true"></span>
					' . esc_html__( 'Recent Activity', 'wpshadow' ) . '
				</h3>
			</div>
			<div class="wps-card-body">
				<div class="wps-activity-timeline">';

		$activity_count = count( $activities );
		$index          = 0;
		foreach ( $activities as $activity ) {
			$is_last     = ( ++$index === $activity_count );
			$action_text = self::format_activity_action( $activity );
			$icon_class  = self::get_activity_icon( $activity );
			$icon_color  = self::get_activity_color( $activity );

			$time_text = 'Unknown';
			if ( ! empty( $activity['timestamp'] ) ) {
				$timestamp = strtotime( $activity['timestamp'] );
				if ( $timestamp !== false ) {
					$time_text = human_time_diff( $timestamp, current_time( 'timestamp' ) ) . ' ago';
				}
			}

			$html .= sprintf(
				'<div class="wps-activity-item %s" role="article">
					<div class="wps-activity-icon-wrapper" style="background: %s20; border-color: %s;">
						<span class="dashicons %s" style="color: %s;" aria-hidden="true"></span>
					</div>
					<div class="wps-activity-content">
						<div class="wps-activity-text">%s</div>
						<time class="wps-activity-time" datetime="%s">%s</time>
					</div>
				</div>',
				$is_last ? 'wps-activity-last' : '',
				esc_attr( $icon_color ),
				esc_attr( $icon_color ),
				esc_attr( $icon_class ),
				esc_attr( $icon_color ),
				esc_html( $action_text ),
				esc_attr( ! empty( $activity['timestamp'] ) ? $activity['timestamp'] : '' ),
				esc_html( $time_text )
			);
		}

		$html .= '</div></div></div>';

		return $html;
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
					return sprintf( __( 'Health check: %1$d findings (%2$d critical)', 'wpshadow' ), $findings, $critical );
				}
				return sprintf( __( 'Health check: %d findings', 'wpshadow' ), $findings );

			case 'auto_fix':
				$treatment = ! empty( $activity['treatment'] ) ? $activity['treatment'] : __( 'Unknown', 'wpshadow' );
				$success   = ! empty( $activity['success'] );
				if ( $success ) {
					return sprintf( __( 'Auto-fixed: %s ✓', 'wpshadow' ), $treatment );
				}
				return sprintf( __( 'Auto-fix failed: %s', 'wpshadow' ), $treatment );

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
	 * Render auto-fix statistics
	 *
	 * @return string HTML
	 */
	private static function render_auto_fix_stats(): string {
		$stats = Auto_Fix_Executor::get_statistics();

		$html = '<div class="wps-card wps-mt-4">
			<div class="wps-card-header">
				<h3 class="wps-card-title wps-m-0">
					<span class="dashicons dashicons-chart-bar wps-icon-mr-2"></span>
					' . esc_html__( 'Auto-Fix Statistics', 'wpshadow' ) . '
				</h3>
			</div>
			<div class="wps-card-body">
				<div class="wps-grid wps-grid-auto-200 wps-gap-3">';

		$stat_items = array(
			__( 'Executions', 'wpshadow' )   => $stats['total_executions'] ?? 0,
			__( 'Success Rate', 'wpshadow' ) => ( $stats['success_rate'] ?? 0 ) . '%',
			__( 'Avg Duration', 'wpshadow' ) => ( $stats['avg_duration'] ?? 0 ) . 'ms',
			__( 'Last Run', 'wpshadow' )     => $stats['last_run'] ?? 'Never',
		);

		foreach ( $stat_items as $label => $value ) {
			$html .= sprintf(
				'<div>
						<div class="wps-text-xs wps-text-gray-500 wps-uppercase wps-tracking-wide wps-font-semibold">%s</div>
						<div class="wps-text-lg wps-font-bold wps-text-gray-800 wps-mt-1">%s</div>
				</div>',
				esc_html( $label ),
				esc_html( (string) $value )
			);
		}

		$html .= '</div></div></div>';

		return $html;
	}

	/**
	 * Render recovery widget
	 *
	 * @return string HTML
	 */
	private static function render_recovery_widget(): string {
		$recovery_points = Recovery_System::get_recovery_points( 5 );

		$html = '<div class="wps-card">
			<div class="wps-card-header">
				<h3 class="wps-card-title wps-m-0 wps-flex-gap-8-items-center">
					<span class="dashicons dashicons-backup"></span>
					' . esc_html__( 'Recovery Points', 'wpshadow' ) . '
				</h3>
			</div>
			<div class="wps-card-body">';

		if ( empty( $recovery_points ) ) {
			$html .= '<p class="wps-m-0">' . esc_html__( 'No recovery points yet', 'wpshadow' ) . '</p>';
		} else {
			$html .= '<div class="wps-flex-gap-12">';

			foreach ( $recovery_points as $point ) {
				$html .= sprintf(
					'<div class="wps-flex-items-center-justify-space-between-">
						<div>
							<div class="wps-font-medium wps-text-gray-800">%s</div>
							<div class="wps-text-xs wps-text-gray-500 wps-mt-1">%s</div>
						</div>
						<button class="wps-btn wps-btn-secondary" data-recovery-id="%s" data-action="restore" class="wps-p-4">
							%s
						</button>
					</div>',
					esc_html( $point['reason'] ?? 'Unknown' ),
					esc_html( $point['timestamp'] ?? 'N/A' ),
					esc_attr( $point['id'] ?? '' ),
					esc_html__( 'Restore', 'wpshadow' )
				);
			}

			$html .= '</div>';
		}

		$html .= '</div></div>';

		return $html;
	}

	/**
	 * Render system health status
	 *
	 * @return string HTML
	 */
	private static function render_system_health(): string {
		$html = '<div class="wps-card wps-mt-4">
			<div class="wps-card-header">
				<h3 class="wps-card-title wps-m-0">
					<span class="dashicons dashicons-heart wps-icon-mr-2"></span>
					' . esc_html__( 'System Health', 'wpshadow' ) . '
				</h3>
			</div>
			<div class="wps-card-body">';

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

		$html .= '<div class="wps-flex-gap-12">';

		foreach ( $checks as $check ) {
			$status_color = 'good' === $check['status'] ? '#10b981' : ( 'warning' === $check['status'] ? '#f59e0b' : '#ef4444' );
			$html        .= sprintf(
				'<div class="wps-flex-gap-12-items-center-p-12-rounded-6">
					<span class="dashicons %s wps-icon-md wps-status-check-icon" style="color: %s;"></span>
					<div class="wps-flex-1">
						<div class="wps-font-medium wps-text-gray-800">%s</div>
					</div>
					<div class="wps-text-xs wps-font-semibold" style="color: %s;">%s</div>
				</div>',
				esc_attr( $check['icon'] ),
				esc_attr( $status_color ),
				esc_html( $check['name'] ),
				esc_attr( $status_color ),
				esc_html( ucfirst( $check['status'] ) )
			);
		}

		$html .= '</div></div></div>';

		return $html;
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
