<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_ElementorProWoocommerceOptimization extends Diagnostic_Base {
	protected static $slug = 'elementor-pro-woocommerce-optimization';
	protected static $title = 'Elementor Pro WooCommerce Optimization';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! defined( 'ELEMENTOR_PRO_VERSION' ) || ! class_exists( 'WooCommerce' ) ) { return null; }
		$woo_widgets = get_option( 'elementor_woocommerce_widgets_usage', 0 );
		if ( $woo_widgets > 10 ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => sprintf( __( '%d WooCommerce widgets - review performance', 'wpshadow' ), $woo_widgets ),
				'severity' => 'high',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/elementor-woocommerce',
			);
		}
		return null;
	}
}
