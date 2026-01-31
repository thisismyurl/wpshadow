<?php
/**
 * WooCommerce Shipping Method Not Configured Diagnostic
 *
 * Checks if WooCommerce shipping methods are set up.
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
 * WooCommerce Shipping Method Not Configured Diagnostic Class
 *
 * Detects missing shipping configuration.
 *
 * @since 1.2601.2310
 */
class Diagnostic_WooCommerce_Shipping_Method_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'woocommerce-shipping-method-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'WooCommerce Shipping Method Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if shipping methods are configured';

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

		// Check if shipping is enabled
		if ( 'no' === get_option( 'woocommerce_ship_to_countries' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'WooCommerce shipping is disabled. Physical product orders cannot be fulfilled.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 75,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/woocommerce-shipping-method-not-configured',
			);
		}

		// Check if any shipping zones have methods
		global $wpdb;
		$shipping_methods = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->prefix}woocommerce_shipping_zone_methods"
		);

		if ( ! $shipping_methods ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'WooCommerce shipping is enabled but no shipping methods are configured. Customers cannot see shipping options.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 80,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/woocommerce-shipping-method-not-configured',
			);
		}

		return null;
	}
}
