<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_WoocommercePerformanceCaching extends Diagnostic_Base {
	protected static $slug = 'woocommerce-performance-caching';
	protected static $title = 'WooCommerce Performance Caching';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! class_exists( 'WooCommerce' ) ) { return null; }
		$cart_fragments = get_option( 'woocommerce_cart_fragments_disabled', 'no' );
		if ( 'no' === $cart_fragments ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'Cart fragments enabled - may impact performance', 'wpshadow' ),
				'severity' => 'high',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/woocommerce-caching',
			);
		}
		return null;
	}
}
