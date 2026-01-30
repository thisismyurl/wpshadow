<?php
/**
 * Shipping Configuration Status Diagnostic
 *
 * Validates WooCommerce shipping settings are properly configured
 * to avoid lost orders, shipping cost issues, and customer confusion.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Shipping_Configuration_Status Class
 *
 * Verifies WooCommerce shipping is properly configured.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Shipping_Configuration_Status extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'shipping-configuration-status';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Shipping Configuration Status';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates WooCommerce shipping settings';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'ecommerce';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if shipping configuration issues found, null otherwise.
	 */
	public static function check() {
		// Check if WooCommerce is active
		if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			return null; // Not an e-commerce site
		}

		$shipping_config = self::validate_shipping_configuration();

		if ( $shipping_config['is_valid'] ) {
			return null; // Shipping properly configured
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Shipping configuration is incomplete or incorrect. Customers may not be able to complete checkout or receive incorrect shipping costs.', 'wpshadow' ),
			'severity'     => 'high',
			'threat_level' => 78,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/woocommerce-shipping',
			'family'       => self::$family,
			'meta'         => array(
				'missing_elements'  => $shipping_config['missing_items'],
				'checkout_blocking' => __( 'Incomplete shipping may prevent checkout' ),
				'revenue_impact'    => __( 'Broken shipping = lost sales' ),
			),
			'details'      => array(
				'configuration_checklist' => array(
					'Shipping Zones Configured' => array(
						__( 'At least 1 shipping zone required' ),
						__( 'Zones = geographic regions with shipping rules' ),
						__( 'Example: Continental US, Hawaii/Alaska, Canada, International' ),
					),
					'Shipping Methods Enabled' => array(
						__( 'Flat rate (fixed price per zone)' ),
						__( 'Free shipping (set thresholds)' ),
						__( 'Carrier integration (USPS, FedEx, UPS)' ),
						__( 'Local delivery option' ),
					),
					'Tax Settings' => array(
						__( 'Enable/disable shipping tax' ),
						__( 'Configure tax classes' ),
						__( 'Test tax calculation on orders' ),
					),
					'Address Fields' => array(
						__( 'Billing address required' ),
						__( 'Shipping address separate from billing' ),
						__( 'State/province/postcode validation' ),
					),
				),
				'setup_steps'             => array(
					'Step 1' => __( 'Go to WooCommerce → Settings → Shipping' ),
					'Step 2' => __( 'Create shipping zones (based on regions you ship to)' ),
					'Step 3' => __( 'Add shipping methods to each zone' ),
					'Step 4' => __( 'Set shipping costs (flat rate or weight-based)' ),
					'Step 5' => __( 'Enable free shipping threshold (e.g., free over $50)' ),
					'Step 6' => __( 'Test checkout with various addresses' ),
					'Step 7' => __( 'Verify shipping costs display correctly' ),
					'Step 8' => __( 'Set up carrier printing (USPS, FedEx integration)' ),
				),
				'carrier_integration'     => array(
					'USPS' => array(
						__( 'Plugin: WooCommerce USPS Shipping' ),
						__( 'Real-time rates from USPS' ),
						__( 'Print labels automatically' ),
						__( 'Cost: Free - $10/month' ),
					),
					'UPS/FedEx' => array(
						__( 'Plugin: WooCommerce UPS Shipping' ),
						__( 'Real-time rate quotes' ),
						__( 'Label printing included' ),
						__( 'Cost: $30-100/month' ),
					),
					'DHL' => array(
						__( 'International shipping specialist' ),
						__( 'Plugin: DHL WooCommerce' ),
						__( 'Global reach' ),
					),
				),
				'common_issues'           => array(
					'No shipping methods' => array(
						__( 'Cause: Shipping zones not created' ),
						__( 'Fix: Add shipping zone matching your location' ),
					),
					'Shipping too expensive' => array(
						__( 'Cause: Weight-based incorrectly configured' ),
						__( 'Fix: Review weight limits and cost per pound' ),
					),
					'Free shipping not working' => array(
						__( 'Cause: Threshold not met or method disabled' ),
						__( 'Fix: Check minimum order amount' ),
					),
				),
			),
		);
	}

	/**
	 * Validate shipping configuration.
	 *
	 * @since  1.2601.2148
	 * @return array Shipping configuration status.
	 */
	private static function validate_shipping_configuration() {
		$missing_items = array();

		// Check if shipping zones exist
		$shipping_zones = \WC_Shipping_Zones::get_zones();
		if ( empty( $shipping_zones ) ) {
			$missing_items[] = __( 'No shipping zones configured' );
		}

		// Check if any shipping methods are enabled
		$methods_enabled = false;
		foreach ( $shipping_zones as $zone ) {
			if ( ! empty( $zone['shipping_methods'] ) ) {
				$methods_enabled = true;
				break;
			}
		}

		if ( ! $methods_enabled ) {
			$missing_items[] = __( 'No shipping methods enabled' );
		}

		// Check if shipping address fields are enabled
		$wc_shipping_address = get_option( 'woocommerce_calc_taxes' );
		if ( ! $wc_shipping_address ) {
			$missing_items[] = __( 'Shipping address collection not enabled' );
		}

		return array(
			'is_valid'      => empty( $missing_items ),
			'missing_items' => $missing_items,
		);
	}
}
