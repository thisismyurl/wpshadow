<?php
/**
 * Guardian Activity Feed Widget - Shows recent Guardian actions
 * Philosophy #8: Inspire Confidence - Shows Guardian is always working
 *
 * @package WPShadow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPShadow_Activity_Feed_Widget {

	/**
	 * Render activity feed widget
	 */
	public static function render() {
		$activities = self::get_recent_activities( 5 );
		
		?>
		<div style="margin: 30px 0;">
			<h2><?php esc_html_e( 'Guardian Activity Feed', 'wpshadow' ); ?></h2>
			
			<div style="background: #fff; border: 1px solid #ddd; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
				<?php if ( ! empty( $activities ) ) : ?>
					<div style="padding: 0;">
						<?php foreach ( $activities as $index => $activity ) : 
							$is_last = $index === count( $activities ) - 1;
							?>
							<div style="padding: 16px; border-bottom: <?php echo $is_last ? 'none' : '1px solid #eee'; ?>; display: flex; gap: 12px;">
								<!-- Icon -->
								<div style="flex-shrink: 0; width: 32px; height: 32px; border-radius: 50%; background: <?php echo esc_attr( self::get_activity_color( $activity['type'] ) ); ?>; display: flex; align-items: center; justify-content: center; color: white; font-size: 14px;">
									<?php echo esc_html( self::get_activity_icon( $activity['type'] ) ); ?>
								</div>
								
								<!-- Content -->
								<div style="flex: 1; min-width: 0;">
									<div style="font-weight: 600; color: #333; font-size: 13px; margin-bottom: 2px;">
										<?php echo esc_html( $activity['title'] ); ?>
									</div>
									<div style="font-size: 12px; color: #666; margin-bottom: 6px;">
										<?php echo esc_html( $activity['description'] ); ?>
									</div>
									<div style="font-size: 11px; color: #999;">
										<?php echo esc_html( human_time_diff( $activity['timestamp'], time() ) . ' ' . __( 'ago', 'wpshadow' ) ); ?>
									</div>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
					<div style="padding: 12px; background: #f9f9f9; text-align: center; border-top: 1px solid #eee;">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-guardian&tab=overview' ) ); ?>" style="color: #0073aa; text-decoration: none; font-size: 12px; font-weight: 600;">
							<?php esc_html_e( 'View All Activity →', 'wpshadow' ); ?>
						</a>
					</div>
				<?php else : ?>
					<div style="padding: 32px; text-align: center; color: #999;">
						<div style="font-size: 40px; margin-bottom: 12px;">🤖</div>
						<p style="margin: 0; font-size: 14px;">
							<?php esc_html_e( 'No activity yet', 'wpshadow' ); ?>
						</p>
						<p style="margin: 8px 0 0 0; font-size: 12px; color: #ccc;">
							<?php esc_html_e( 'Guardian will start logging activity once it runs.', 'wpshadow' ); ?>
						</p>
					</div>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Get recent activities
	 */
	private static function get_recent_activities( $limit = 5 ) {
		if ( ! class_exists( '\WPShadow\Core\Activity_Logger' ) ) {
			return array();
		}
		
		$all_activities = \WPShadow\Core\Activity_Logger::get_activities( array(
			'limit' => $limit,
			'order' => 'DESC',
		) );
		
		// Transform for display
		$activities = array();
		foreach ( $all_activities as $activity ) {
			$action = isset( $activity['action'] ) ? $activity['action'] : '';
			$timestamp = isset( $activity['timestamp'] ) ? $activity['timestamp'] : time();
			
			// Skip old internal activities, focus on user-facing ones
			if ( in_array( $action, array( 'finding_viewed', 'filter_applied' ), true ) ) {
				continue;
			}
			
			$activities[] = array(
				'type' => self::get_activity_type( $action ),
				'title' => self::get_activity_title( $action, $activity ),
				'description' => self::get_activity_description( $action, $activity ),
				'timestamp' => $timestamp,
				'action' => $action,
			);
		}
		
		return array_slice( $activities, 0, $limit );
	}

	/**
	 * Get activity type for icon
	 */
	private static function get_activity_type( $action ) {
		$types = array(
			'workflow_created_from_finding' => 'workflow_created',
			'workflow_executed' => 'workflow_executed',
			'finding_fixed' => 'fixed',
			'finding_dismissed' => 'dismissed',
			'workflow_run_success' => 'workflow_success',
			'workflow_run_failed' => 'workflow_failed',
			'finding_detected' => 'detected',
			'scan_completed' => 'scan_complete',
		);
		
		return $types[ $action ] ?? 'other';
	}

	/**
	 * Get activity icon
	 */
	private static function get_activity_icon( $type ) {
		$icons = array(
			'workflow_created' => '⚙️',
			'workflow_executed' => '▶️',
			'fixed' => '✅',
			'dismissed' => '🚫',
			'workflow_success' => '✓',
			'workflow_failed' => '⚠️',
			'detected' => '🔍',
			'scan_complete' => '✨',
			'other' => '📌',
		);
		
		return $icons[ $type ] ?? '📌';
	}

	/**
	 * Get activity color
	 */
	private static function get_activity_color( $type ) {
		$colors = array(
			'workflow_created' => '#9c27b0',
			'workflow_executed' => '#0091b8',
			'fixed' => '#2e7d32',
			'dismissed' => '#f57c00',
			'workflow_success' => '#2e7d32',
			'workflow_failed' => '#c62828',
			'detected' => '#1976d2',
			'scan_complete' => '#388e3c',
			'other' => '#666',
		);
		
		return $colors[ $type ] ?? '#999';
	}

	/**
	 * Get activity title
	 */
	private static function get_activity_title( $action, $activity ) {
		switch ( $action ) {
			case 'workflow_created_from_finding':
				return __( 'Workflow Created', 'wpshadow' );
			case 'workflow_executed':
				return __( 'Workflow Executed', 'wpshadow' );
			case 'finding_fixed':
				return __( 'Issue Fixed', 'wpshadow' );
			case 'finding_dismissed':
				return __( 'Issue Dismissed', 'wpshadow' );
			case 'workflow_run_success':
				return __( 'Workflow Successful', 'wpshadow' );
			case 'workflow_run_failed':
				return __( 'Workflow Failed', 'wpshadow' );
			case 'scan_completed':
				return __( 'Scan Completed', 'wpshadow' );
			case 'finding_detected':
				return __( 'Issue Detected', 'wpshadow' );
			default:
				return __( 'Activity', 'wpshadow' );
		}
	}

	/**
	 * Get activity description
	 */
	private static function get_activity_description( $action, $activity ) {
		$description = '';
		
		if ( isset( $activity['finding_id'] ) ) {
			$finding_id = $activity['finding_id'];
			// Try to get finding title
			if ( function_exists( 'wpshadow_get_site_findings' ) ) {
				$findings = wpshadow_get_site_findings();
				foreach ( $findings as $finding ) {
					if ( ( $finding['id'] ?? '' ) === $finding_id ) {
						$description = isset( $finding['title'] ) ? substr( $finding['title'], 0, 50 ) : $finding_id;
						break;
					}
				}
			}
		}
		
		if ( empty( $description ) ) {
			switch ( $action ) {
				case 'workflow_created_from_finding':
					$description = isset( $activity['workflow_type'] ) ? ucfirst( str_replace( '_', ' ', $activity['workflow_type'] ) ) . ' workflow created' : __( 'New workflow created', 'wpshadow' );
					break;
				case 'workflow_executed':
					$description = isset( $activity['workflow_name'] ) ? $activity['workflow_name'] : __( 'Workflow ran successfully', 'wpshadow' );
					break;
				case 'scan_completed':
					$description = sprintf( __( '%d issues detected', 'wpshadow' ), isset( $activity['findings_count'] ) ? $activity['findings_count'] : 0 );
					break;
				default:
					$description = __( 'Activity recorded', 'wpshadow' );
			}
		}
		
		return $description;
	}
}
