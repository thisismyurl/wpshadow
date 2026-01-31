<?php
/**
 * Weglot Woocommerce Integration Diagnostic
 *
 * Weglot Woocommerce Integration misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1161.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Weglot Woocommerce Integration Diagnostic Class
 *
 * @since 1.1161.0000
 */
class Diagnostic_WeglotWoocommerceIntegration extends Diagnostic_Base {

	protected static $slug = 'weglot-woocommerce-integration';
	protected static $title = 'Weglot Woocommerce Integration';
	protected static $description = 'Weglot Woocommerce Integration misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'WEGLOT_VERSION' ) || ! class_exists( 'WooCommerce' ) ) {
			return null;
		}
		
		$issues = array();

		// Check 1: Verify WooCommerce integration is enabled
		$wc_integration = get_option( 'weglot_woocommerce_enabled', false );
		if ( ! $wc_integration ) {
			$issues[] = __( 'WooCommerce integration not enabled in Weglot', 'wpshadow' );
		}

		// Check 2: Check product content translation
		$translate_products = get_option( 'weglot_translate_wc_products', false );
		if ( ! $translate_products ) {
			$issues[] = __( 'Product content translation not configured', 'wpshadow' );
		}

		// Check 3: Verify checkout page translation
		$translate_checkout = get_option( 'weglot_translate_wc_checkout', false );
		if ( ! $translate_checkout ) {
			$issues[] = __( 'Checkout page translation not enabled', 'wpshadow' );
		}

		// Check 4: Check currency switching for translations
		$currency_switching = get_option( 'weglot_wc_currency_switching', false );
		if ( ! $currency_switching ) {
			$issues[] = __( 'Currency switching not configured for translations', 'wpshadow' );
		}

		// Check 5: Verify translated URL structure
		$url_structure = get_option( 'weglot_wc_url_structure', '' );
		if ( empty( $url_structure ) ) {
			$issues[] = __( 'Translated URL structure not configured', 'wpshadow' );
		}

		// Check 6: Check cache compatibility with WooCommerce
		$cache_compatibility = get_option( 'weglot_wc_cache_compatibility', false );
		if ( ! $cache_compatibility ) {
			$issues[] = __( 'Cache compatibility for WooCommerce not enabled', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 80, 50 + ( count( $issues ) * 5 ) );
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: Comma-separated list of issues */
					__( 'Weglot WooCommerce integration issues detected: %s', 'wpshadow' ),
					implode( ', ', $issues )
				),
				'severity'     => 'medium',
				'threat_level' => $threat_level,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/weglot-woocommerce-integration',
			);
		}

		return null;
	}
}
