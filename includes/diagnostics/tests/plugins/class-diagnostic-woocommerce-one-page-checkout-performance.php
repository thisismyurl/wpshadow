<?php
/**
 * Woocommerce One Page Checkout Performance Diagnostic
 *
 * Woocommerce One Page Checkout Performance issues detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.678.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Woocommerce One Page Checkout Performance Diagnostic Class
 *
 * @since 1.678.0000
 */
class Diagnostic_WoocommerceOnePageCheckoutPerformance extends Diagnostic_Base {

	protected static $slug = 'woocommerce-one-page-checkout-performance';
	protected static $title = 'Woocommerce One Page Checkout Performance';
	protected static $description = 'Woocommerce One Page Checkout Performance issues detected';
	protected static $family = 'performance';

	public static function check() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Verify checkout page caching
		$checkout_cache = get_option( 'wc_opc_checkout_cache', false );
		if ( ! $checkout_cache ) {
			$issues[] = __( 'One-page checkout page caching not enabled', 'wpshadow' );
		}

		// Check 2: Check checkout script optimization
		$script_minified = get_option( 'wc_opc_script_minified', false );
		if ( ! $script_minified ) {
			$issues[] = __( 'Checkout scripts not minified', 'wpshadow' );
		}

		// Check 3: Verify AJAX performance
		$ajax_optimization = get_option( 'wc_opc_ajax_optimization', false );
		if ( ! $ajax_optimization ) {
			$issues[] = __( 'AJAX request optimization not enabled', 'wpshadow' );
		}

		// Check 4: Check form submission optimization
		$submit_optimization = get_option( 'wc_opc_submit_optimization', false );
		if ( ! $submit_optimization ) {
			$issues[] = __( 'Form submission performance not optimized', 'wpshadow' );
		}

		// Check 5: Verify CSS loading strategy
		$css_strategy = get_option( 'wc_opc_css_loading', '' );
		if ( 'defer' !== $css_strategy && 'inline' !== $css_strategy ) {
			$issues[] = __( 'CSS loading strategy not optimized', 'wpshadow' );
		}

		// Check 6: Check payload size optimization
		$payload_optimization = get_option( 'wc_opc_payload_optimization', false );
		if ( ! $payload_optimization ) {
			$issues[] = __( 'Checkout payload not optimized for performance', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 85, 55 + ( count( $issues ) * 5 ) );
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: Comma-separated list of issues */
					__( 'WooCommerce one-page checkout performance issues detected: %s', 'wpshadow' ),
					implode( ', ', $issues )
				),
				'severity'     => 'medium',
				'threat_level' => $threat_level,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/woocommerce-one-page-checkout-performance',
			);
		}

		return null;
	}
}
