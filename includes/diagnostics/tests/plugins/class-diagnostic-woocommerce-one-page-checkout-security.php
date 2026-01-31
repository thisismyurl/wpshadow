<?php
/**
 * Woocommerce One Page Checkout Security Diagnostic
 *
 * Woocommerce One Page Checkout Security issues detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.677.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Woocommerce One Page Checkout Security Diagnostic Class
 *
 * @since 1.677.0000
 */
class Diagnostic_WoocommerceOnePageCheckoutSecurity extends Diagnostic_Base {

	protected static $slug = 'woocommerce-one-page-checkout-security';
	protected static $title = 'Woocommerce One Page Checkout Security';
	protected static $description = 'Woocommerce One Page Checkout Security issues detected';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return null;
		}
		
		// Check if One Page Checkout plugin is active
		if ( ! class_exists( 'PP_One_Page_Checkout' ) && ! defined( 'WC_OPC_VERSION' ) ) {
			return null;
		}

		$issues = array();
		$threat_level = 0;

		// Check SSL
		if ( ! is_ssl() ) {
			$issues[] = 'ssl_not_enabled';
			$threat_level += 35;
		}

		// Check WooCommerce force SSL option
		$force_ssl = get_option( 'woocommerce_force_ssl_checkout', 'no' );
		if ( $force_ssl === 'no' ) {
			$issues[] = 'ssl_not_enforced';
			$threat_level += 30;
		}

		// Check guest checkout
		$guest_checkout = get_option( 'woocommerce_enable_guest_checkout', 'yes' );
		if ( $guest_checkout === 'yes' ) {
			$issues[] = 'guest_checkout_enabled';
			$threat_level += 20;
		}

		// Check checkout field validation
		$opc_settings = get_option( 'wc_opc_settings', array() );
		$validate_fields = isset( $opc_settings['validate_checkout_fields'] ) ? $opc_settings['validate_checkout_fields'] : false;
		if ( ! $validate_fields ) {
			$issues[] = 'field_validation_disabled';
			$threat_level += 25;
		}

		// Check AJAX security
		$ajax_nonce = isset( $opc_settings['use_ajax_nonce'] ) ? $opc_settings['use_ajax_nonce'] : false;
		if ( ! $ajax_nonce ) {
			$issues[] = 'ajax_nonce_disabled';
			$threat_level += 20;
		}

		// Check session management
		$session_type = WC()->session ? get_class( WC()->session ) : '';
		if ( empty( $session_type ) ) {
			$issues[] = 'session_handler_missing';
			$threat_level += 15;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of security issues */
				__( 'WooCommerce One Page Checkout has security vulnerabilities: %s. This exposes customer payment data and personal information.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) )
			);

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => $description,
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/woocommerce-one-page-checkout-security',
			);
		}
		
		return null;
	}
}
