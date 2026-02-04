<?php
/**
 * No Predictive Customer Behavior Models Diagnostic
 *
 * Checks whether predictive analytics are used to spot churn or upsell signals.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\BusinessPerformance
 * @since      1.6035.1430
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Predictive Customer Behavior Diagnostic
 *
 * Detects when the site lacks predictive signals for churn or upsell. Early
 * warnings help protect revenue before customers leave.
 *
 * @since 1.6035.1430
 */
class Diagnostic_No_Predictive_Customer_Behavior_Models extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'no-predictive-customer-behavior-models';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Predictive Customer Behavior Signals Used';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether predictive churn or upsell signals are in place';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'marketing';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6035.1430
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$has_predictive = self::has_predictive_signals();

		if ( ! $has_predictive ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Predictive signals are not visible yet. This means you can only react after customers leave. Even a simple churn watchlist (based on inactivity or declining purchases) can protect revenue before it drops.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/predictive-customer-behavior',
				'details'      => array(
					'predictive_signals_detected' => false,
					'recommendation'              => __( 'Start with simple signals: 30+ days inactivity, declining order size, or reduced engagement.', 'wpshadow' ),
					'signal_examples'             => self::get_signal_examples(),
				),
			);
		}

		return null;
	}

	/**
	 * Check for predictive analytics signals.
	 *
	 * @since  1.6035.1430
	 * @return bool True if predictive signals exist.
	 */
	private static function has_predictive_signals(): bool {
		$keywords = array(
			'predictive',
			'churn risk',
			'propensity',
			'health score',
			'upsell signal',
			'behavior model',
		);

		if ( self::count_posts_by_keywords( $keywords ) > 0 ) {
			return true;
		}

		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$plugins = get_plugins();
		$predictive_plugins = array(
			'woocommerce-advanced-predictive-search/woocommerce-advanced-predictive-search.php',
			'woocommerce-recommendation-engine/woocommerce-recommendation-engine.php',
			'customer-journey/woocommerce-customer-journey.php',
		);

		foreach ( $predictive_plugins as $plugin_file ) {
			if ( isset( $plugins[ $plugin_file ] ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Count posts/pages containing any keyword.
	 *
	 * @since  1.6035.1430
	 * @param  array $keywords Keywords to search for.
	 * @return int Count of matching posts/pages.
	 */
	private static function count_posts_by_keywords( array $keywords ): int {
		$total = 0;

		foreach ( $keywords as $keyword ) {
			$matches = get_posts( array(
				'post_type'   => array( 'page', 'post' ),
				'numberposts' => 5,
				's'           => $keyword,
			) );

			$total += count( $matches );
		}

		return $total;
	}

	/**
	 * Provide example predictive signals.
	 *
	 * @since  1.6035.1430
	 * @return array Example signals.
	 */
	private static function get_signal_examples(): array {
		return array(
			__( 'No purchases in 30-60 days (for repeat products)', 'wpshadow' ),
			__( 'Login activity declining over 3+ sessions', 'wpshadow' ),
			__( 'Support tickets rising without resolution', 'wpshadow' ),
			__( 'Checkout started but abandoned twice', 'wpshadow' ),
			__( 'Customer satisfaction score below target', 'wpshadow' ),
		);
	}
}
