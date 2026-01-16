<?php
/**
 * Health Score Dashboard Widget
 *
 * Displays health scores on WPShadow dashboard and WordPress dashboard.
 *
 * @package WPShadow\CoreSupport
 * @since 1.2601.75000
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPSHADOW_Health_Score_Widget
 *
 * Dashboard widget showing health metrics and scores.
 */
final class WPSHADOW_Health_Score_Widget {

	/**
	 * Initialize hooks.
	 *
	 * @return void
	 */
	public static function init(): void {
		// Add to WordPress dashboard.
		add_action( 'wp_dashboard_setup', array( __CLASS__, 'register_wp_dashboard_widget' ) );

		// Enqueue assets.
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
	}

	/**
	 * Register widget on WordPress dashboard.
	 *
	 * @return void
	 */
	public static function register_wp_dashboard_widget(): void {
		wp_add_dashboard_widget(
			'wpshadow_health_score',
			__( 'WPShadow Health Score', 'plugin-wpshadow' ),
			array( __CLASS__, 'render_widget' ),
			null,
			null,
			'normal',
			'high'
		);
	}

	/**
	 * Enqueue widget assets.
	 *
	 * @return void
	 */
	public static function enqueue_assets(): void {
		$screen = get_current_screen();
		if ( ! $screen || 'dashboard' !== $screen->id ) {
			return;
		}

		wp_enqueue_style(
			'wps-health-widget',
			plugins_url( 'assets/css/health-widget.css', dirname( __DIR__ ) . '/plugin-wpshadow.php' ),
			array(),
			WPSHADOW_VERSION
		);

		wp_enqueue_script(
			'wps-health-widget',
			plugins_url( 'assets/js/health-widget.js', dirname( __DIR__ ) . '/plugin-wpshadow.php' ),
			array( 'jquery' ),
			WPSHADOW_VERSION,
			true
		);

		wp_localize_script(
			'wps-health-widget',
			'wpsHealth',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'wps-health' ),
			)
		);
	}

	/**
	 * Render the health score widget.
	 *
	 * @return void
	 */
	public static function render_widget(): void {
		$overall            = WPSHADOW_Site_Health_Integration::calculate_overall_health();
		$category_breakdown = WPSHADOW_Site_Health_Integration::get_category_breakdown();

		$overall_status = self::get_status_class( $overall );

		?>
		<div class="wps-health-widget">
			<div class="wps-health-overall">
				<div class="wps-health-circle wps-health-<?php echo esc_attr( $overall_status ); ?>">
					<div class="wps-health-score"><?php echo esc_html( $overall ); ?></div>
					<div class="wps-health-label"><?php esc_html_e( 'Overall Health', 'plugin-wpshadow' ); ?></div>
				</div>
			</div>

			<div class="wps-health-breakdown">
				<?php foreach ( $category_breakdown as $category => $data ) : ?>
					<?php
					if ( $data['total'] === 0 ) {
						continue; // Skip categories with no features.
					}
					$category_status = self::get_status_class( $data['score'] );
					?>
					<div class="wps-health-item">
						<div class="wps-health-bar-container">
							<div class="wps-health-bar wps-health-<?php echo esc_attr( $category_status ); ?>" style="width: <?php echo esc_attr( $data['score'] ); ?>%"></div>
						</div>
						<div class="wps-health-meta">
							<span class="wps-health-category"><?php echo esc_html( ucfirst( $category ) ); ?></span>
							<span class="wps-health-value"><?php echo esc_html( $data['score'] ); ?>/100 (<?php echo esc_html( $data['enabled'] ); ?>/<?php echo esc_html( $data['total'] ); ?>)</span>
						</div>
					</div>
				<?php endforeach; ?>
			</div>

			<div class="wps-health-actions">
				<a href="<?php echo esc_url( admin_url( 'site-health.php' ) ); ?>" class="button">
					<?php esc_html_e( 'View Site Health', 'plugin-wpshadow' ); ?>
				</a>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow' ) ); ?>" class="button button-primary">
					<?php esc_html_e( 'WPShadow Dashboard', 'plugin-wpshadow' ); ?>
				</a>
			</div>

			<?php self::render_recommendations( $overall, $category_breakdown ); ?>
		</div>
		<?php
	}

	/**
	 * Render recommendations based on scores.
	 *
	 * @param int   $overall Overall score.
	 * @param array $categories Category breakdown.
	 * @return void
	 */
	private static function render_recommendations( int $overall, array $categories ): void {
		$recommendations = array();

		// Check each category for low scores.
		foreach ( $categories as $category => $data ) {
			if ( $data['score'] < 60 && $data['total'] > 0 ) {
				$recommendations[] = array(
					'title' => sprintf(
						/* translators: %s: category name */
						__( 'Improve %s', 'plugin-wpshadow' ),
						ucfirst( $category )
					),
					'text' => sprintf(
						/* translators: 1: enabled count, 2: total count, 3: category */
						__( 'Enable more %3$s features (%1$d of %2$d active).', 'plugin-wpshadow' ),
						$data['enabled'],
						$data['total'],
						$category
					),
					'url' => admin_url( 'admin.php?page=wpshadow-settings&tab=' . $category ),
				);
			}
		}

		if ( $overall >= 80 ) {
			$recommendations[] = array(
				'title' => __( 'Excellent Configuration', 'plugin-wpshadow' ),
				'text'  => __( 'Your site is well optimized. Keep monitoring for continued health.', 'plugin-wpshadow' ),
				'url'   => admin_url( 'site-health.php' ),
			);
		}

		if ( empty( $recommendations ) ) {
			return;
		}

		?>
		<div class="wps-health-recommendations">
			<h4><?php esc_html_e( 'Recommendations', 'plugin-wpshadow' ); ?></h4>
			<ul>
				<?php foreach ( array_slice( $recommendations, 0, 3 ) as $rec ) : ?>
					<li>
						<strong><?php echo esc_html( $rec['title'] ); ?></strong>
						<p><?php echo esc_html( $rec['text'] ); ?></p>
						<a href="<?php echo esc_url( $rec['url'] ); ?>"><?php esc_html_e( 'Learn More', 'plugin-wpshadow' ); ?> &rarr;</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php
	}

	/**
	 * Get status class based on score.
	 *
	 * @param int $score Score value.
	 * @return string Status class name.
	 */
	private static function get_status_class( int $score ): string {
		if ( $score >= 80 ) {
			return 'good';
		} elseif ( $score >= 60 ) {
			return 'warning';
		}
		return 'critical';
	}
}
