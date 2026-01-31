<?php
/**
 * WooCommerce Cart Abandonment Not Tracked Diagnostic
 *
 * Checks if cart abandonment is being monitored.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2310
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WooCommerce Cart Abandonment Not Tracked Diagnostic Class
 *
 * Detects missing cart abandonment tracking.
 *
 * @since 1.2601.2310
 */
class Diagnostic_WooCommerce_Cart_Abandonment_Not_Tracked extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'woocommerce-cart-abandonment-not-tracked';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'WooCommerce Cart Abandonment Not Tracked';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if cart abandonment is monitored';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'plugins';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2310
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if WooCommerce is active
		if ( ! class_exists( 'WooCommerce' ) ) {
			return null;
		}

		// Check for cart abandonment plugins
		$abandonment_plugins = array(
			'woocommerce-cart-abandonment-recovery/woo-cart-abandonment-recovery.php',
			'abandoned-cart-lite-for-woocommerce/woocommerce-abandoned-cart.php',
			'cart-abandonment-recovery/cart-abandonment-recovery.php',
		);

		$abandonment_active = false;
		foreach ( $abandonment_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$abandonment_active = true;
				break;
			}
		}

		if ( ! $abandonment_active ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'No cart abandonment tracking plugin is active. You\'re losing revenue from customers who don\'t complete their purchase.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 45,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/woocommerce-cart-abandonment-not-tracked',
			);
		}

		return null;
	}
}
