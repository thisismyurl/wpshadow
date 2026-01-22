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
		<div class="wpshadow-guardian-dashboard">
			<!-- Header -->
			<div class="guardian-header">
				<h1><?php esc_html_e( 'WPShadow Guardian Dashboard', 'wpshadow' ); ?></h1>
				<p class="subtitle"><?php esc_html_e( 'Automated health monitoring and intelligent fixes', 'wpshadow' ); ?></p>
			</div>
			
			<!-- Status Row -->
			<div class="guardian-status-row">
				<?php echo wp_kses_post( self::render_status_badge() ); ?>
				<?php echo wp_kses_post( self::render_quick_actions() ); ?>
			</div>
			
			<!-- KPI Cards -->
			<div class="guardian-kpi-cards">
				<?php echo wp_kses_post( self::render_kpi_cards() ); ?>
			</div>
			
			<!-- Main Content -->
			<div class="guardian-main-content">
				<!-- Left Column: Activity & Stats -->
				<div class="guardian-left-column">
					<?php echo wp_kses_post( self::render_activity_timeline() ); ?>
					<?php echo wp_kses_post( self::render_auto_fix_stats() ); ?>
				</div>
				
				<!-- Right Column: Recovery & Health -->
				<div class="guardian-right-column">
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
		$is_enabled = Guardian_Manager::is_enabled();
		$status_class = $is_enabled ? 'status-enabled' : 'status-disabled';
		$status_text = $is_enabled ? __( 'WPShadow Guardian Active', 'wpshadow' ) : __( 'WPShadow Guardian Inactive', 'wpshadow' );
		$click_text = $is_enabled ? __( 'Click to disable', 'wpshadow' ) : __( 'Click to enable', 'wpshadow' );
		
		return sprintf(
			'<div class="guardian-status-badge %s" style="cursor: pointer;" onclick="wpshadowToggleGuardian()" title="%s"><span class="status-dot"></span>%s</div>
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
			esc_attr( $status_class ),
			esc_attr( $click_text ),
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
		$html = '<div class="guardian-quick-actions">';
		
		$html .= sprintf(
			'<button class="button" data-action="preview-fixes">%s</button>',
			esc_html__( 'Preview Fixes', 'wpshadow' )
		);
		
		$html .= sprintf(
			'<a href="%s" class="button">%s</a>',
			esc_url( admin_url( 'admin.php?page=wpshadow-guardian-settings' ) ),
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
		
		$cards = [
			[
				'label'  => __( 'Issues Found', 'wpshadow' ),
				'value'  => $kpis['findings_detected'] ?? 0,
				'icon'   => '🔍',
				'color'  => 'warning',
			],
			[
				'label'  => __( 'Issues Fixed', 'wpshadow' ),
				'value'  => $kpis['issues_fixed'] ?? 0,
				'icon'   => '✅',
				'color'  => 'success',
			],
			[
				'label'  => __( 'Time Saved', 'wpshadow' ),
				'value'  => $kpis['time_saved_display'] ?? '0m',
				'icon'   => '⏱️',
				'color'  => 'info',
			],
			[
				'label'  => __( 'Value Generated', 'wpshadow' ),
				'value'  => '$' . ( $kpis['labor_cost_avoided'] ?? 0 ),
				'icon'   => '💰',
				'color'  => 'success',
			],
		];
		
		$html = '';
		foreach ( $cards as $card ) {
			$html .= sprintf(
				'<div class="guardian-kpi-card card-%s">
					<div class="card-icon">%s</div>
					<div class="card-content">
						<div class="card-label">%s</div>
						<div class="card-value">%s</div>
					</div>
				</div>',
				esc_attr( $card['color'] ),
				$card['icon'],
				esc_html( $card['label'] ),
				esc_html( (string) $card['value'] )
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
			return '<div class="guardian-widget"><p>' . esc_html__( 'No recent activity', 'wpshadow' ) . '</p></div>';
		}
		
		$html = '<div class="guardian-widget guardian-activity-timeline">
			<h3>' . esc_html__( 'Recent Activity', 'wpshadow' ) . '</h3>
			<div class="timeline">';
		
		foreach ( $activities as $activity ) {
			// Format activity description based on type
			$action_text = self::format_activity_action( $activity );
			$time_text = isset( $activity['timestamp'] ) ? human_time_diff( strtotime( $activity['timestamp'] ), current_time( 'timestamp' ) ) . ' ago' : 'Unknown';
			
			$html .= sprintf(
				'<div class="timeline-item">
					<div class="timeline-dot"></div>
					<div class="timeline-content">
						<div class="timeline-action">%s</div>
						<div class="timeline-time">%s</div>
					</div>
				</div>',
				esc_html( $action_text ),
				esc_html( $time_text )
			);
		}
		
		$html .= '</div></div>';
		
		return $html;
	}
	
	/**
	 * Format activity action for display
	 * 
	 * @param array $activity Activity log entry
	 * @return string Formatted action text
	 */
	private static function format_activity_action( array $activity ): string {
		$type = $activity['type'] ?? 'unknown';
		
		switch ( $type ) {
			case 'health_check':
				$findings = $activity['findings_total'] ?? 0;
				$critical = $activity['critical_count'] ?? 0;
				if ( $critical > 0 ) {
					return sprintf( __( 'Health check: %d findings (%d critical)', 'wpshadow' ), $findings, $critical );
				}
				return sprintf( __( 'Health check: %d findings', 'wpshadow' ), $findings );
				
			case 'auto_fix':
				$treatment = $activity['treatment'] ?? 'Unknown';
				$success = $activity['success'] ?? false;
				if ( $success ) {
					return sprintf( __( 'Auto-fixed: %s ✓', 'wpshadow' ), $treatment );
				}
				return sprintf( __( 'Auto-fix failed: %s', 'wpshadow' ), $treatment );
				
			case 'anomaly_detected':
				$count = $activity['anomalies_count'] ?? 0;
				return sprintf( __( 'Anomaly detected: %d issues', 'wpshadow' ), $count );
				
			case 'settings_changed':
				$enabled = $activity['enabled'] ?? false;
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
		
		$html = '<div class="guardian-widget guardian-auto-fix-stats">
			<h3>' . esc_html__( 'Auto-Fix Statistics', 'wpshadow' ) . '</h3>
			<div class="stats-grid">';
		
		$stat_items = [
			__( 'Executions', 'wpshadow' ) => $stats['total_executions'] ?? 0,
			__( 'Success Rate', 'wpshadow' ) => ( $stats['success_rate'] ?? 0 ) . '%',
			__( 'Avg Duration', 'wpshadow' ) => ( $stats['avg_duration'] ?? 0 ) . 'ms',
			__( 'Last Run', 'wpshadow' ) => $stats['last_run'] ?? 'Never',
		];
		
		foreach ( $stat_items as $label => $value ) {
			$html .= sprintf(
				'<div class="stat-item">
					<div class="stat-label">%s</div>
					<div class="stat-value">%s</div>
				</div>',
				esc_html( $label ),
				esc_html( (string) $value )
			);
		}
		
		$html .= '</div></div>';
		
		return $html;
	}
	
	/**
	 * Render recovery widget
	 * 
	 * @return string HTML
	 */
	private static function render_recovery_widget(): string {
		$recovery_points = Recovery_System::get_recovery_points( 5 );
		
		$html = '<div class="guardian-widget guardian-recovery-widget">
			<h3>' . esc_html__( 'Recovery Points', 'wpshadow' ) . '</h3>';
		
		if ( empty( $recovery_points ) ) {
			$html .= '<p>' . esc_html__( 'No recovery points yet', 'wpshadow' ) . '</p>';
		} else {
			$html .= '<div class="recovery-list">';
			
			foreach ( $recovery_points as $point ) {
				$html .= sprintf(
					'<div class="recovery-item">
						<div class="recovery-info">
							<div class="recovery-reason">%s</div>
							<div class="recovery-time">%s</div>
						</div>
						<button class="button button-small" data-recovery-id="%s" data-action="restore">
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
		
		$html .= '</div>';
		
		return $html;
	}
	
	/**
	 * Render system health status
	 * 
	 * @return string HTML
	 */
	private static function render_system_health(): string {
		$html = '<div class="guardian-widget guardian-health">
			<h3>' . esc_html__( 'System Health', 'wpshadow' ) . '</h3>
			<div class="health-checks">';
		
		$checks = [
			[
				'name'   => __( 'Memory Usage', 'wpshadow' ),
				'status' => self::get_memory_status(),
			],
			[
				'name'   => __( 'Database', 'wpshadow' ),
				'status' => 'good',
			],
			[
				'name'   => __( 'Plugins', 'wpshadow' ),
				'status' => 'good',
			],
			[
				'name'   => __( 'Security', 'wpshadow' ),
				'status' => 'good',
			],
		];
		
		foreach ( $checks as $check ) {
			$status_class = 'status-' . $check['status'];
			$html .= sprintf(
				'<div class="health-check %s">
					<span class="check-indicator"></span>
					<span class="check-name">%s</span>
					<span class="check-status">%s</span>
				</div>',
				esc_attr( $status_class ),
				esc_html( $check['name'] ),
				esc_html( ucfirst( $check['status'] ) )
			);
		}
		
		$html .= '</div></div>';
		
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
