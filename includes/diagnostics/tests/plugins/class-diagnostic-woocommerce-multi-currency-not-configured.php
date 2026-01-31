<?php
/**
 * WooCommerce Multi-Currency Not Configured Diagnostic
 *
 * Checks if multi-currency support is set up.
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
 * WooCommerce Multi-Currency Not Configured Diagnostic Class
 *
 * Detects missing multi-currency setup.
 *
 * @since 1.2601.2310
 */
class Diagnostic_WooCommerce_Multi_Currency_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'woocommerce-multi-currency-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'WooCommerce Multi-Currency Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if multi-currency is available';

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

		// Check for multi-currency plugins
		$multicurrency_plugins = array(
			'woocommerce-currency-switcher/woocommerce-currency-switcher.php',
			'multi-currency-for-woocommerce/multi-currency-for-woocommerce.php',
			'ocean-currency-switcher/ocean-currency-switcher.php',
		);

		$multicurrency_active = false;
		foreach ( $multicurrency_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$multicurrency_active = true;
				break;
			}
		}

		// If site serves international customers, warn
		global $wpdb;
		$orders_from_other_countries = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = '_billing_country' AND meta_value != 'US'"
		);

		if ( $orders_from_other_countries > 10 && ! $multicurrency_active ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'International orders detected but multi-currency is not configured. Customers from other countries see prices in site default currency.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 40,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/woocommerce-multi-currency-not-configured',
			);
		}

		return null;
	}
}
