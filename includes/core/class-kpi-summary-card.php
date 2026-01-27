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
		<div class="wpshadow-kpi-summary-card wps-m-30-p-24-rounded-12">
			
			<!-- Header -->
			<div class="wps-m-20-b">
				<h2 class="wps-flex-gap-8-items-center-m-0">
					<span class="dashicons dashicons-chart-line wps-kpi-icon"></span>
				<?php esc_html_e( 'WPShadow Impact', 'wpshadow' ); ?>
				</h2>
				<p class="wps-m-0">
					<?php esc_html_e( 'The value you\'ve gained since installing WPShadow', 'wpshadow' ); ?>
				</p>
			</div>
			
			<!-- Toggle between Human & Executive View -->
			<div class="wps-flex-gap-8">
				<button class="wpshadow-kpi-toggle" data-view="human" class="wps-p-8-rounded-4">
					<?php esc_html_e( '👤 Human Value', 'wpshadow' ); ?>
				</button>
				<button class="wpshadow-kpi-toggle" data-view="executive" class="wps-p-8-rounded-4">
					<?php esc_html_e( '📊 Executive Value', 'wpshadow' ); ?>
				</button>
			</div>
			
			<!-- Human Value View (Default) -->
			<div class="wpshadow-kpi-human-view wps-grid wps-grid-auto-200 wps-gap-4">
				
				<!-- Time Saved Card -->
				<div class="wps-p-16-rounded-8">
					<div class="wps-kpi-card-header">
						<?php esc_html_e( '⏱️ Time Saved', 'wpshadow' ); ?>
					</div>
					<div class="wps-kpi-card-value">
					<?php echo esc_html( isset( $kpis['time_saved_display'] ) && $kpis['time_saved_display'] ? $kpis['time_saved_display'] : '0m' ); ?>
					</div>
					<div class="wps-kpi-card-description">
					<?php echo esc_html( sprintf( __( 'That\'s %d hours of manual work avoided', 'wpshadow' ), isset( $kpis['time_saved_hours'] ) ? (int) $kpis['time_saved_hours'] : 0 ) ); ?>
					</div>
				</div>
				
				<!-- Issues Fixed Card -->
				<div class="wps-p-16-rounded-8">
					<div class="wps-kpi-card-header">
						<?php esc_html_e( '🛡️ Issues Fixed', 'wpshadow' ); ?>
					</div>
					<div class="wps-kpi-card-value">
					<?php echo isset( $kpis['issues_fixed'] ) ? (int) $kpis['issues_fixed'] : 0; ?>
					</div>
					<div class="wps-kpi-card-description">
						<?php echo esc_html( __( 'Problems you fixed or auto-fixed', 'wpshadow' ) ); ?>
					</div>
				</div>
				
				<!-- Security Improvements Card -->
				<div class="wps-p-16-rounded-8">
					<div class="wps-kpi-card-header">
						<?php esc_html_e( '🔒 Security Wins', 'wpshadow' ); ?>
					</div>
					<div class="wps-kpi-card-value">
					<?php echo isset( $kpis['security_improvements'] ) ? (int) $kpis['security_improvements'] : 0; ?>
					</div>
					<div class="wps-kpi-card-description">
						<?php echo esc_html( __( 'Vulnerabilities eliminated', 'wpshadow' ) ); ?>
					</div>
				</div>
				
				<!-- Health Improvement Card -->
				<div class="wps-p-16-rounded-8">
					<div class="wps-kpi-card-header">
						<?php esc_html_e( '📈 Health Trend', 'wpshadow' ); ?>
					</div>
					<div class="wps-kpi-card-value">
					<?php echo esc_html( isset( $kpis['confidence_trend'] ) && $kpis['confidence_trend'] ? $kpis['confidence_trend'] : '0%' ); ?>
					</div>
					<div class="wps-kpi-card-description">
						<?php echo esc_html( __( 'Better than 30 days ago', 'wpshadow' ) ); ?>
					</div>
				</div>
				
			</div>
			
			<!-- Executive Value View (Hidden by default) -->
			<div class="wpshadow-kpi-executive-view wps-grid wps-grid-auto-200 wps-gap-4 wps-none">
				
				<!-- ROI Card -->
				<div class="wps-p-16-rounded-8">
					<div class="wps-kpi-card-header">
						<?php esc_html_e( '💰 Labor Cost Avoided', 'wpshadow' ); ?>
					</div>
					<div class="wps-kpi-card-value">
					$<?php echo isset( $kpis['labor_cost_avoided'] ) ? number_format( (int) $kpis['labor_cost_avoided'] ) : '0'; ?>
					</div>
					<div class="wps-kpi-card-description">
						<?php echo esc_html( sprintf( __( '%d hours @ $50/hr', 'wpshadow' ), $kpis['time_saved_hours'] ) ); ?>
					</div>
				</div>
				
				<!-- Critical Risks Mitigated -->
				<div class="wps-p-16-rounded-8">
					<div class="wps-kpi-card-header">
						<?php esc_html_e( '⚠️ Critical Risks Mitigated', 'wpshadow' ); ?>
					</div>
					<div class="wps-kpi-card-value">
					<?php echo isset( $kpis['critical_risks_mitigated'] ) ? (int) $kpis['critical_risks_mitigated'] : 0; ?>
					</div>
					<div class="wps-kpi-card-description">
						<?php echo esc_html( __( 'High-severity vulnerabilities resolved', 'wpshadow' ) ); ?>
					</div>
				</div>
				
				<!-- Performance Optimizations -->
				<div class="wps-p-16-rounded-8">
					<div class="wps-kpi-card-header">
						<?php esc_html_e( '⚡ Performance Gains', 'wpshadow' ); ?>
					</div>
					<div class="wps-kpi-card-value">
					<?php echo isset( $kpis['performance_optimizations'] ) ? (int) $kpis['performance_optimizations'] : 0; ?>
					</div>
					<div class="wps-kpi-card-description">
						<?php echo esc_html( __( 'Optimizations implemented', 'wpshadow' ) ); ?>
					</div>
				</div>
				
				<!-- Health Score Improvement -->
				<div class="wps-p-16-rounded-8">
					<div class="wps-kpi-card-header">
						<?php esc_html_e( '📊 Health Score Growth', 'wpshadow' ); ?>
					</div>
					<div class="wps-kpi-card-value">
						<?php echo esc_html( isset( $kpis['health_improvement'] ) ? $kpis['health_improvement'] : 0 ); ?>%
					</div>
					<div class="wps-kpi-card-description">
						<?php echo esc_html( sprintf( __( '%1$d → %2$d score in 30 days', 'wpshadow' ), $kpis['health_score_30_days_ago'], $kpis['health_score_today'] ) ); ?>
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
						b.classList.remove( 'wps-kpi-toggle-active' );
					});
					this.classList.add( 'wps-kpi-toggle-active' );
					
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
			'<div class="wps-p-16-rounded-8">
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
