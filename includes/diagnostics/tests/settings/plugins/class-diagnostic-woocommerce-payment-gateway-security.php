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
		if ( ! class_exists( 'WooCommerce' ) ) { return null; }
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
		return null;
	}
}
