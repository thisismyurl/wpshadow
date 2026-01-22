<?php
/**
 * KPI Summary Card Generator
 *
 * Creates visually appealing KPI display cards for both
 * non-technical users and executive audiences.
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Core;

/**
 * Generates KPI summary cards with dual-audience appeal
 */
class KPI_Summary_Card {
	
	/**
	 * Render the full KPI summary card section
	 *
	 * @return void Outputs HTML directly.
	 */
	public static function render() {
		$kpis = KPI_Tracker::get_kpi_summary();
		?>
		<div class="wpshadow-kpi-summary-card" style="margin: 30px 0; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; padding: 24px; color: white; box-shadow: 0 8px 16px rgba(0,0,0,0.2);">
			
			<!-- Header -->
			<div style="margin-bottom: 20px;">
				<h2 style="margin: 0 0 8px 0; font-size: 24px; display: flex; align-items: center; gap: 8px;">
					<span class="dashicons dashicons-chart-line" style="font-size: 28px;"></span>
				<?php esc_html_e( 'WPShadow Impact', 'wpshadow' ); ?>
				</h2>
				<p style="margin: 0; font-size: 14px; opacity: 0.9;">
					<?php esc_html_e( 'The value you\'ve gained since installing WPShadow', 'wpshadow' ); ?>
				</p>
			</div>
			
			<!-- Toggle between Human & Executive View -->
			<div style="margin-bottom: 20px; display: flex; gap: 8px;">
				<button class="wpshadow-kpi-toggle" data-view="human" style="padding: 8px 16px; background: rgba(255,255,255,0.3); color: white; border: 1px solid rgba(255,255,255,0.5); border-radius: 4px; cursor: pointer; font-size: 14px; font-weight: 600; transition: all 0.2s;">
					<?php esc_html_e( '👤 Human Value', 'wpshadow' ); ?>
				</button>
				<button class="wpshadow-kpi-toggle" data-view="executive" style="padding: 8px 16px; background: rgba(255,255,255,0.2); color: rgba(255,255,255,0.7); border: 1px solid rgba(255,255,255,0.3); border-radius: 4px; cursor: pointer; font-size: 14px; font-weight: 600; transition: all 0.2s;">
					<?php esc_html_e( '📊 Executive Value', 'wpshadow' ); ?>
				</button>
			</div>
			
			<!-- Human Value View (Default) -->
			<div class="wpshadow-kpi-human-view" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
				
				<!-- Time Saved Card -->
				<div style="background: rgba(255,255,255,0.1); border-radius: 8px; padding: 16px; border-left: 4px solid #4ade80;">
					<div style="font-size: 12px; opacity: 0.8; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;">
						<?php esc_html_e( '⏱️ Time Saved', 'wpshadow' ); ?>
					</div>
					<div style="font-size: 28px; font-weight: bold; margin-bottom: 4px;">
					<?php echo esc_html( isset( $kpis['time_saved_display'] ) && $kpis['time_saved_display'] ? $kpis['time_saved_display'] : '0m' ); ?>
					</div>
					<div style="font-size: 13px; opacity: 0.8;">
					<?php echo esc_html( sprintf( __( 'That\'s %d hours of manual work avoided', 'wpshadow' ), isset( $kpis['time_saved_hours'] ) ? (int) $kpis['time_saved_hours'] : 0 ) ); ?>
					</div>
				</div>
				
				<!-- Issues Fixed Card -->
				<div style="background: rgba(255,255,255,0.1); border-radius: 8px; padding: 16px; border-left: 4px solid #60a5fa;">
					<div style="font-size: 12px; opacity: 0.8; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;">
						<?php esc_html_e( '🛡️ Issues Fixed', 'wpshadow' ); ?>
					</div>
					<div style="font-size: 28px; font-weight: bold; margin-bottom: 4px;">
					<?php echo isset( $kpis['issues_fixed'] ) ? (int) $kpis['issues_fixed'] : 0; ?>
					</div>
					<div style="font-size: 13px; opacity: 0.8;">
						<?php echo esc_html( __( 'Problems you fixed or auto-fixed', 'wpshadow' ) ); ?>
					</div>
				</div>
				
				<!-- Security Improvements Card -->
				<div style="background: rgba(255,255,255,0.1); border-radius: 8px; padding: 16px; border-left: 4px solid #f87171;">
					<div style="font-size: 12px; opacity: 0.8; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;">
						<?php esc_html_e( '🔒 Security Wins', 'wpshadow' ); ?>
					</div>
					<div style="font-size: 28px; font-weight: bold; margin-bottom: 4px;">
					<?php echo isset( $kpis['security_improvements'] ) ? (int) $kpis['security_improvements'] : 0; ?>
					</div>
					<div style="font-size: 13px; opacity: 0.8;">
						<?php echo esc_html( __( 'Vulnerabilities eliminated', 'wpshadow' ) ); ?>
					</div>
				</div>
				
				<!-- Health Improvement Card -->
				<div style="background: rgba(255,255,255,0.1); border-radius: 8px; padding: 16px; border-left: 4px solid #fbbf24;">
					<div style="font-size: 12px; opacity: 0.8; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;">
						<?php esc_html_e( '📈 Health Trend', 'wpshadow' ); ?>
					</div>
					<div style="font-size: 28px; font-weight: bold; margin-bottom: 4px;">
					<?php echo esc_html( isset( $kpis['confidence_trend'] ) && $kpis['confidence_trend'] ? $kpis['confidence_trend'] : '0%' ); ?>
					</div>
					<div style="font-size: 13px; opacity: 0.8;">
						<?php echo esc_html( __( 'Better than 30 days ago', 'wpshadow' ) ); ?>
					</div>
				</div>
				
			</div>
			
			<!-- Executive Value View (Hidden by default) -->
			<div class="wpshadow-kpi-executive-view" style="display: none; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
				
				<!-- ROI Card -->
				<div style="background: rgba(255,255,255,0.1); border-radius: 8px; padding: 16px; border-left: 4px solid #34d399;">
					<div style="font-size: 12px; opacity: 0.8; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;">
						<?php esc_html_e( '💰 Labor Cost Avoided', 'wpshadow' ); ?>
					</div>
					<div style="font-size: 28px; font-weight: bold; margin-bottom: 4px;">
					$<?php echo isset( $kpis['labor_cost_avoided'] ) ? number_format( (int) $kpis['labor_cost_avoided'] ) : '0'; ?>
					</div>
					<div style="font-size: 13px; opacity: 0.8;">
						<?php echo esc_html( sprintf( __( '%d hours @ $50/hr', 'wpshadow' ), $kpis['time_saved_hours'] ) ); ?>
					</div>
				</div>
				
				<!-- Critical Risks Mitigated -->
				<div style="background: rgba(255,255,255,0.1); border-radius: 8px; padding: 16px; border-left: 4px solid #f87171;">
					<div style="font-size: 12px; opacity: 0.8; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;">
						<?php esc_html_e( '⚠️ Critical Risks Mitigated', 'wpshadow' ); ?>
					</div>
					<div style="font-size: 28px; font-weight: bold; margin-bottom: 4px;">
					<?php echo isset( $kpis['critical_risks_mitigated'] ) ? (int) $kpis['critical_risks_mitigated'] : 0; ?>
					</div>
					<div style="font-size: 13px; opacity: 0.8;">
						<?php echo esc_html( __( 'High-severity vulnerabilities resolved', 'wpshadow' ) ); ?>
					</div>
				</div>
				
				<!-- Performance Optimizations -->
				<div style="background: rgba(255,255,255,0.1); border-radius: 8px; padding: 16px; border-left: 4px solid #60a5fa;">
					<div style="font-size: 12px; opacity: 0.8; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;">
						<?php esc_html_e( '⚡ Performance Gains', 'wpshadow' ); ?>
					</div>
					<div style="font-size: 28px; font-weight: bold; margin-bottom: 4px;">
					<?php echo isset( $kpis['performance_optimizations'] ) ? (int) $kpis['performance_optimizations'] : 0; ?>
					</div>
					<div style="font-size: 13px; opacity: 0.8;">
						<?php echo esc_html( __( 'Optimizations implemented', 'wpshadow' ) ); ?>
					</div>
				</div>
				
				<!-- Health Score Improvement -->
				<div style="background: rgba(255,255,255,0.1); border-radius: 8px; padding: 16px; border-left: 4px solid #fbbf24;">
					<div style="font-size: 12px; opacity: 0.8; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;">
						<?php esc_html_e( '📊 Health Score Growth', 'wpshadow' ); ?>
					</div>
					<div style="font-size: 28px; font-weight: bold; margin-bottom: 4px;">
						<?php echo esc_html( isset( $kpis['health_improvement'] ) ? $kpis['health_improvement'] : 0 ); ?>%
					</div>
					<div style="font-size: 13px; opacity: 0.8;">
						<?php echo esc_html( sprintf( __( '%d → %d score in 30 days', 'wpshadow' ), $kpis['health_score_30_days_ago'], $kpis['health_score_today'] ) ); ?>
					</div>
				</div>
				
			</div>
			
		</div>
		
		<script>
		document.addEventListener( 'DOMContentLoaded', function() {
			const buttons = document.querySelectorAll( '.wpshadow-kpi-toggle' );
			const humanView = document.querySelector( '.wpshadow-kpi-human-view' );
			const executiveView = document.querySelector( '.wpshadow-kpi-executive-view' );
			
			buttons.forEach( button => {
				button.addEventListener( 'click', function() {
					const view = this.dataset.view;
					buttons.forEach( b => {
						b.style.background = 'rgba(255,255,255,0.2)';
						b.style.color = 'rgba(255,255,255,0.7)';
						b.style.borderColor = 'rgba(255,255,255,0.3)';
					});
					this.style.background = 'rgba(255,255,255,0.3)';
					this.style.color = 'white';
					this.style.borderColor = 'rgba(255,255,255,0.5)';
					
					if ( view === 'human' ) {
						humanView.style.display = 'grid';
						executiveView.style.display = 'none';
					} else {
						humanView.style.display = 'none';
						executiveView.style.display = 'grid';
					}
				});
			});
		});
		</script>
		
		</div>
		<?php
	}
	
	/**
	 * Get styled HTML for a KPI metric
	 *
	 * @param string $label   Metric label.
	 * @param string $value   Metric value.
	 * @param string $icon    Emoji or dashicon class.
	 * @param string $color   Accent color (hex).
	 * @return string HTML.
	 */
	public static function metric_card( $label, $value, $icon = '📊', $color = '#667eea' ) {
		return sprintf(
			'<div style="background: rgba(255,255,255,0.1); border-radius: 8px; padding: 16px; border-left: 4px solid %s;">
				<div style="font-size: 12px; opacity: 0.8; margin-bottom: 8px;">%s %s</div>
				<div style="font-size: 24px; font-weight: bold; margin-bottom: 4px;">%s</div>
			</div>',
			esc_attr( $color ),
			esc_html( $icon ),
			esc_html( $label ),
			esc_html( $value )
		);
	}
}
