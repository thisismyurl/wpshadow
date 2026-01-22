<?php
/**
 * KPI Summary Widget - Shows value delivered this month
 * Philosophy #9: Show Value - Track time saved, issues fixed, value delivered
 *
 * @package WPShadow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPShadow_KPI_Summary_Widget {

	/**
	 * Render KPI summary widget
	 */
	public static function render() {
		// Get KPI data from Activity Logger
		$kpis = self::get_kpi_data();
		
		// Determine status color and message
		if ( $kpis['total_value'] > 0 ) {
			$status_icon = '🚀';
			$status_text = __( 'Delivering Value', 'wpshadow' );
			$status_color = '#2e7d32';
			$status_bg = '#e8f5e9';
		} else {
			$status_icon = '📊';
			$status_text = __( 'No Activity This Month', 'wpshadow' );
			$status_color = '#666';
			$status_bg = '#f5f5f5';
		}
		?>
		<div style="margin: 30px 0;">
			<h2><?php esc_html_e( 'This Month\'s Value', 'wpshadow' ); ?></h2>
			
			<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-top: 16px;">
				<!-- Time Saved Card -->
				<div style="background: linear-gradient(135deg, #e3f2fd 0%, #f0f7ff 100%); border: 1px solid #90caf9; border-radius: 8px; padding: 20px; text-align: center; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
					<div style="font-size: 32px; margin-bottom: 8px;">⏱️</div>
					<div style="font-size: 28px; font-weight: bold; color: #1976d2; margin-bottom: 4px;">
						<?php echo esc_html( self::format_hours( $kpis['time_saved_hours'] ) ); ?>
					</div>
					<div style="font-size: 12px; color: #666; margin-bottom: 12px;">
						<?php esc_html_e( 'Hours Saved', 'wpshadow' ); ?>
					</div>
					<div style="font-size: 11px; color: #999; padding-top: 8px; border-top: 1px solid rgba(25, 118, 210, 0.1);">
						<?php echo esc_html( sprintf( __( '%d auto-fix workflows', 'wpshadow' ), $kpis['workflows_executed'] ) ); ?>
					</div>
				</div>
				
				<!-- Issues Fixed Card -->
				<div style="background: linear-gradient(135deg, #f3e5f5 0%, #fce4ec 100%); border: 1px solid #ce93d8; border-radius: 8px; padding: 20px; text-align: center; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
					<div style="font-size: 32px; margin-bottom: 8px;">✅</div>
					<div style="font-size: 28px; font-weight: bold; color: #7b1fa2; margin-bottom: 4px;">
						<?php echo esc_html( $kpis['issues_fixed'] ); ?>
					</div>
					<div style="font-size: 12px; color: #666; margin-bottom: 12px;">
						<?php esc_html_e( 'Issues Fixed', 'wpshadow' ); ?>
					</div>
					<div style="font-size: 11px; color: #999; padding-top: 8px; border-top: 1px solid rgba(123, 31, 162, 0.1);">
						<?php echo esc_html( sprintf( __( 'Avg %d/week', 'wpshadow' ), max( 1, (int) ( $kpis['issues_fixed'] / 4 ) ) ) ); ?>
					</div>
				</div>
				
				<!-- Money Saved Card -->
				<div style="background: linear-gradient(135deg, #e8f5e9 0%, #f1f8e9 100%); border: 1px solid #81c784; border-radius: 8px; padding: 20px; text-align: center; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
					<div style="font-size: 32px; margin-bottom: 8px;">💰</div>
					<div style="font-size: 28px; font-weight: bold; color: #2e7d32; margin-bottom: 4px;">
						<?php echo esc_html( self::format_currency( $kpis['total_value'] ) ); ?>
					</div>
					<div style="font-size: 12px; color: #666; margin-bottom: 12px;">
						<?php esc_html_e( 'Estimated Value', 'wpshadow' ); ?>
					</div>
					<div style="font-size: 11px; color: #999; padding-top: 8px; border-top: 1px solid rgba(46, 125, 50, 0.1);">
						<?php echo esc_html( sprintf( __( 'At $%d/hour', 'wpshadow' ), self::get_hourly_rate() ) ); ?>
					</div>
				</div>
			</div>
			
			<!-- Status Badge -->
			<div style="margin-top: 16px; padding: 12px; background: <?php echo esc_attr( $status_bg ); ?>; border-left: 4px solid <?php echo esc_attr( $status_color ); ?>; border-radius: 4px; display: flex; align-items: center; gap: 10px;">
				<span style="font-size: 20px;"><?php echo esc_html( $status_icon ); ?></span>
				<div>
					<strong style="color: <?php echo esc_attr( $status_color ); ?>;"><?php echo esc_html( $status_text ); ?></strong>
					<p style="margin: 4px 0 0 0; font-size: 12px; color: #666;">
						<?php 
						if ( $kpis['total_value'] > 0 ) {
							echo esc_html( sprintf( 
								__( 'You\'ve automated %d workflows this month, saving your team valuable time.', 'wpshadow' ),
								$kpis['workflows_created']
							) );
						} else {
							echo esc_html( __( 'Create your first workflow to start tracking value saved.', 'wpshadow' ) );
						}
						?>
					</p>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Get KPI data from activity logger
	 */
	private static function get_kpi_data() {
		$now = time();
		$month_ago = strtotime( '-1 month', $now );
		
		// Get activities from this month
		if ( class_exists( '\WPShadow\Core\Activity_Logger' ) ) {
			$activities = \WPShadow\Core\Activity_Logger::get_activities( array(
				'start_date' => $month_ago,
				'end_date' => $now,
			) );
		} else {
			$activities = array();
		}
		
		// Calculate metrics
		$time_saved_hours = 0;
		$issues_fixed = 0;
		$workflows_created = 0;
		$workflows_executed = 0;
		
		foreach ( $activities as $activity ) {
			$action = isset( $activity['action'] ) ? $activity['action'] : '';
			
			if ( $action === 'workflow_created_from_finding' ) {
				$workflows_created++;
				// Estimate 30 minutes saved per workflow creation
				$time_saved_hours += 0.5;
			} elseif ( $action === 'finding_fixed' || $action === 'workflow_executed' ) {
				$issues_fixed++;
				$workflows_executed++;
				// Estimate 15 minutes saved per auto-fix
				$time_saved_hours += 0.25;
			} elseif ( $action === 'finding_dismissed' ) {
				// Dismissed issues still count as resolved
				$issues_fixed++;
			}
		}
		
		// Calculate estimated value
		$hourly_rate = self::get_hourly_rate();
		$total_value = (int) ( $time_saved_hours * $hourly_rate );
		
		return array(
			'time_saved_hours' => $time_saved_hours,
			'issues_fixed' => $issues_fixed,
			'workflows_created' => $workflows_created,
			'workflows_executed' => $workflows_executed,
			'total_value' => $total_value,
		);
	}

	/**
	 * Get hourly rate setting (Philosophy #9: Show Value)
	 */
	private static function get_hourly_rate() {
		$rate = get_option( 'wpshadow_hourly_rate', 50 );
		return (int) $rate;
	}

	/**
	 * Format hours for display
	 */
	private static function format_hours( $hours ) {
		if ( $hours >= 24 ) {
			$days = (int) ( $hours / 24 );
			return sprintf( __( '%d day%s', 'wpshadow' ), $days, $days > 1 ? 's' : '' );
		}
		return sprintf( __( '%.1f hr%s', 'wpshadow' ), $hours, $hours > 1 ? 's' : '' );
	}

	/**
	 * Format currency for display
	 */
	private static function format_currency( $amount ) {
		return '$' . number_format( $amount );
	}
}
