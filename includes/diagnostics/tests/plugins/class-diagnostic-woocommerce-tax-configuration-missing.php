<?php
/**
 * WooCommerce Tax Configuration Missing Diagnostic
 *
 * Checks if WooCommerce tax rates are configured.
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
 * WooCommerce Tax Configuration Missing Diagnostic Class
 *
 * Detects missing tax configuration.
 *
 * @since 1.2601.2310
 */
class Diagnostic_WooCommerce_Tax_Configuration_Missing extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'woocommerce-tax-configuration-missing';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'WooCommerce Tax Configuration Missing';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if tax rates are configured';

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

		// Check if taxes are enabled
		if ( 'yes' !== get_option( 'woocommerce_calc_taxes', 'no' ) ) {
			return null; // Taxes disabled is a valid choice
		}

		global $wpdb;

		// Check if any tax rates are configured
		$tax_rates = $wpdb->get_results( 
			"SELECT * FROM {$wpdb->prefix}woocommerce_tax_rates LIMIT 1"
		);

		if ( empty( $tax_rates ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'WooCommerce tax calculation is enabled but no tax rates are configured. Customers will not see taxes on checkout.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 70,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/woocommerce-tax-configuration-missing',
			);
		}

		return null;
	}
}
