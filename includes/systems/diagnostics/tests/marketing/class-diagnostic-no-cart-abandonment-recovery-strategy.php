<?php
/**
 * No Cart Abandonment Recovery Strategy Diagnostic
 *
 * Checks if cart abandonment recovery strategy is in place.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cart Abandonment Recovery Strategy Diagnostic
 *
 * Average cart abandonment rate is 69.8%. Recovery emails recover
 * 10-15% of abandoned carts, generating massive additional revenue.
 *
 * @since 1.6035.0000
 */
class Diagnostic_No_Cart_Abandonment_Recovery_Strategy extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'no-cart-abandonment-recovery-strategy';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Cart Abandonment Recovery Strategy';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if cart abandonment recovery strategy is in place';

	/**
	 * Diagnostic family.
	 *
	 * @var string
	 */
	protected static $family = 'marketing';

	/**
	 * Run diagnostic check.
	 *
	 * @since  1.6035.0000
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		if ( ! self::has_abandonment_recovery() ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'No cart abandonment recovery detected. Average abandonment rate is 69.8% - you\'re losing 7 of every 10 potential sales. Recovery emails recover 10-15% of abandoned carts. Strategy: 1) Capture email before checkout, 2) Send 3-email sequence (1 hour, 24 hours, 72 hours), 3) Remind what they left behind (product images), 4) Address objections (free shipping, urgency), 5) Offer incentive in email 3 (10% discount), 6) Track recovery rate and revenue. For $100k/mo in abandoned carts, recovery generates $10-15k additional revenue.', 'wpshadow' ),
				'severity'    => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/cart-abandonment-recovery',
				'details'     => array(
					'issue'               => __( 'No cart abandonment recovery strategy detected', 'wpshadow' ),
					'recommendation'      => __( 'Implement automated cart abandonment email sequence', 'wpshadow' ),
					'business_impact'     => __( 'Losing 10-15% of abandoned cart revenue (potential $10k-15k per $100k abandoned)', 'wpshadow' ),
					'email_sequence'      => self::get_email_sequence(),
					'optimization_tips'   => self::get_optimization_tips(),
				),
			);
		}

		return null;
	}

	/**
	 * Check if abandonment recovery exists.
	 *
	 * @since  1.6035.0000
	 * @return bool True if recovery detected, false otherwise.
	 */
	private static function has_abandonment_recovery() {
		// Check if WooCommerce is active
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$has_woocommerce = is_plugin_active( 'woocommerce/woocommerce.php' );

		if ( ! $has_woocommerce ) {
			// Not an ecommerce site, doesn't apply
			return true;
		}

		// Check for abandonment recovery plugins
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$all_plugins = get_plugins();
		$recovery_keywords = array(
			'cart abandon',
			'abandoned cart',
			'cart recovery',
			'retainful',
			'cartbounty',
			'jilt',
		);

		foreach ( $all_plugins as $plugin_file => $plugin_data ) {
			$plugin_name = strtolower( $plugin_data['Name'] );
			foreach ( $recovery_keywords as $keyword ) {
				if ( false !== strpos( $plugin_name, $keyword ) ) {
					if ( is_plugin_active( $plugin_file ) ) {
						return true;
					}
				}
			}
		}

		return false;
	}

	/**
	 * Get recommended email sequence.
	 *
	 * @since  1.6035.0000
	 * @return array Email sequence structure.
	 */
	private static function get_email_sequence() {
		return array(
			'email_1' => array(
				'timing'  => __( '1 hour after abandonment', 'wpshadow' ),
				'subject' => __( '"You left something behind..."', 'wpshadow' ),
				'goal'    => __( 'Gentle reminder, show cart contents with images', 'wpshadow' ),
				'cta'     => __( 'Simple "Complete your purchase" button', 'wpshadow' ),
			),
			'email_2' => array(
				'timing'  => __( '24 hours after abandonment', 'wpshadow' ),
				'subject' => __( '"Still interested? Here\'s why customers love it"', 'wpshadow' ),
				'goal'    => __( 'Address objections with social proof, reviews, benefits', 'wpshadow' ),
				'cta'     => __( 'Add urgency: "Limited stock" or "Offer expires soon"', 'wpshadow' ),
			),
			'email_3' => array(
				'timing'  => __( '72 hours after abandonment', 'wpshadow' ),
				'subject' => __( '"Last chance: Here\'s 10% off to complete your order"', 'wpshadow' ),
				'goal'    => __( 'Final incentive to convert (discount, free shipping)', 'wpshadow' ),
				'cta'     => __( 'Time-limited offer: "Expires in 24 hours"', 'wpshadow' ),
			),
		);
	}

	/**
	 * Get recovery optimization tips.
	 *
	 * @since  1.6035.0000
	 * @return array Optimization tips.
	 */
	private static function get_optimization_tips() {
		return array(
			'capture_email'    => __( 'Capture email early (before checkout, not at last step)', 'wpshadow' ),
			'product_images'   => __( 'Show product images in email (visual reminder)', 'wpshadow' ),
			'personalization'  => __( 'Use customer name and specific products they left', 'wpshadow' ),
			'mobile_optimize'  => __( '65% of email opens are mobile - optimize design', 'wpshadow' ),
			'one_click'        => __( 'Link directly to cart, not homepage', 'wpshadow' ),
			'test_timing'      => __( 'Test email timing (some audiences respond faster)', 'wpshadow' ),
			'segment'          => __( 'Different messages for first-time vs repeat customers', 'wpshadow' ),
			'exit_intent'      => __( 'Combine with exit-intent popup for immediate capture', 'wpshadow' ),
		);
	}
}
