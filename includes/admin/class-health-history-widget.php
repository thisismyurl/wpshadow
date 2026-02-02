<?php
/**
 * Health History Dashboard Widget
 *
 * Compact widget showing health trend on main WPShadow dashboard.
 *
 * @package    WPShadow
 * @subpackage Admin
 * @since      1.2602.0200
 */

declare(strict_types=1);

namespace WPShadow\Admin;

use WPShadow\Analytics\Health_History;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Health History Widget Class
 *
 * @since 1.2602.0200
 */
class Health_History_Widget {

	/**
	 * Initialize the widget.
	 *
	 * @since 1.2602.0200
	 * @return void
	 */
	public static function init() {
		add_action( 'wpshadow_dashboard_widgets', array( __CLASS__, 'render_widget' ) );
	}

	/**
	 * Render the widget.
	 *
	 * @since 1.2602.0200
	 * @return void
	 */
	public static function render_widget() {
		$summary = Health_History::get_summary( 7 );
		$history = Health_History::get_history( 7 );

		if ( empty( $history ) ) {
			return;
		}

		$health_scores = array_column( $history, 'overall_health' );
		$sparkline_data = implode( ',', $health_scores );

		$change = $summary['health_change'];
		$arrow = $change > 0 ? '↑' : ( $change < 0 ? '↓' : '→' );
		$change_class = $change > 0 ? 'positive' : ( $change < 0 ? 'negative' : 'neutral' );
		?>
		
		<div class="wpshadow-widget wpshadow-health-widget">
			<div class="widget-header">
				<h3><?php esc_html_e( 'Health Trend', 'wpshadow' ); ?></h3>
				<span class="health-change <?php echo esc_attr( $change_class ); ?>">
					<?php echo esc_html( $arrow . ' ' . abs( $change ) . '%' ); ?>
				</span>
			</div>

			<div class="widget-body">
				<div class="health-score-large">
					<?php echo esc_html( end( $health_scores ) ); ?>
					<span class="score-label">%</span>
				</div>

				<div class="health-sparkline" data-values="<?php echo esc_attr( $sparkline_data ); ?>">
					<svg width="200" height="40" viewBox="0 0 200 40">
						<?php
						$point_count = count( $health_scores );
						$x_step = 200 / max( 1, $point_count - 1 );
						$points = array();
						
						foreach ( $health_scores as $i => $score ) {
							$x = $i * $x_step;
							$y = 40 - ( $score / 100 * 30 );
							$points[] = "$x,$y";
						}
						
						$polyline = implode( ' ', $points );
						?>
						<polyline
							fill="none"
							stroke="#2271b1"
							stroke-width="2"
							points="<?php echo esc_attr( $polyline ); ?>"
						/>
					</svg>
				</div>

				<div class="widget-footer">
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-health-history' ) ); ?>" class="button">
						<?php esc_html_e( 'View Full History', 'wpshadow' ); ?>
					</a>
				</div>
			</div>
		</div>

		<style>
		.wpshadow-health-widget {
			background: #fff;
			border: 1px solid #ddd;
			border-radius: 8px;
			padding: 20px;
			margin-bottom: 20px;
		}

		.wpshadow-health-widget .widget-header {
			display: flex;
			justify-content: space-between;
			align-items: center;
			margin-bottom: 15px;
		}

		.wpshadow-health-widget h3 {
			margin: 0;
			font-size: 1.1em;
		}

		.health-change {
			font-weight: bold;
			font-size: 1.2em;
		}

		.health-change.positive {
			color: #00a32a;
		}

		.health-change.negative {
			color: #d63638;
		}

		.health-change.neutral {
			color: #666;
		}

		.health-score-large {
			font-size: 3em;
			font-weight: bold;
			color: #2271b1;
			text-align: center;
			margin: 15px 0;
		}

		.score-label {
			font-size: 0.5em;
			color: #666;
		}

		.health-sparkline {
			text-align: center;
			margin: 15px 0;
		}

		.health-sparkline svg {
			max-width: 100%;
			height: auto;
		}

		.wpshadow-health-widget .widget-footer {
			text-align: center;
			margin-top: 15px;
		}
		</style>
		<?php
	}
}
