<?php
/**
 * Health History Dashboard Widget
 *
 * Compact widget showing health trend on main WPShadow dashboard.
 *
 * @package    WPShadow
 * @subpackage Admin
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Admin;

use WPShadow\Analytics\Health_History;
use WPShadow\Core\Hook_Subscriber_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Health History Widget Class
 *
 * @since 1.6093.1200
 */
class Health_History_Widget extends Hook_Subscriber_Base {

	/**
	 * Get hook subscriptions.
	 *
	 * @since 1.6093.1200
	 * @return array Hook subscriptions.
	 */
	protected static function get_hooks(): array {
		return array(
			'wpshadow_dashboard_widgets' => 'render_widget',
			'admin_enqueue_scripts'      => 'enqueue_assets',
		);
	}

	/**
	 * Initialize the widget (deprecated)
	 *
	 * @deprecated1.0 Use Health_History_Widget::subscribe() instead
	 * @since 1.6093.1200
	 * @return     void
	 */
	public static function init() {
		self::subscribe();
	}

	/**
	 * Enqueue widget assets.
	 *
	 * @since 1.6093.1200
	 * @param  string $hook Current admin page hook.
	 * @return void
	 */
	public static function enqueue_assets( $hook ) {
		// Only on WPShadow main dashboard.
		if ( 'toplevel_page_wpshadow' !== $hook ) {
			return;
		}

		wp_enqueue_style(
			'wpshadow-health-widget',
			WPSHADOW_URL . 'assets/css/health-history-widget.css',
			array( 'wpshadow-admin-pages' ),
			WPSHADOW_VERSION
		);
	}

	/**
	 * Render the widget.
	 *
	 * @since 1.6093.1200
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
		<?php
	}
}
