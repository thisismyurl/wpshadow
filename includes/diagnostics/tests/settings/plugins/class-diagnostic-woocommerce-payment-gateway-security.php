<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_WoocommercePaymentGatewaySecurity extends Diagnostic_Base {
	protected static $slug = 'woocommerce-payment-gateway-security';
	protected static $title = 'WooCommerce Payment Gateway Security';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! class_exists( 'WooCommerce' ) ) { if ( isset( $issues ) && ! empty( $issues ) ) {
		return array(
			'id' => self::$slug,
			'title' => self::$title,
			'description' => sprintf(
				__( 'Found %d issues', 'wpshadow' ),
				count( $issues )
			),
			'severity' => 'medium',
			'threat_level' => 45,
			'auto_fixable' => false,
			'kb_link' => 'https://wpshadow.com/kb/woocommerce-payment-gateway-security',
		);
	}
	return null; }
		$gateways = WC()->payment_gateways()->get_available_payment_gateways();
		if ( empty( $gateways ) ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'No payment gateways configured', 'wpshadow' ),
				'severity' => 'critical',
				'threat_level' => 90,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/woocommerce-payment-security',
			);
		}
		
	if ( ! (function_exists( "is_plugin_active" )) ) {
		if ( ! isset( $issues ) ) {
			$issues = array();
		}
		$issues[] = __( 'Plugin active', 'wpshadow' );
	}

	if ( ! (! empty( get_option( "woocommerce_payment_gateway_security_settings" ) )) ) {
		if ( ! isset( $issues ) ) {
			$issues = array();
		}
		$issues[] = __( 'Settings available', 'wpshadow' );
	}
	if ( isset( $issues ) && ! empty( $issues ) ) {
		return array(
			'id' => self::$slug,
			'title' => self::$title,
			'description' => sprintf(
				__( 'Found %d issues', 'wpshadow' ),
				count( $issues )
			),
			'severity' => 'medium',
			'threat_level' => 45,
			'auto_fixable' => false,
			'kb_link' => 'https://wpshadow.com/kb/woocommerce-payment-gateway-security',
		);
	}
	return null;
	}
}
