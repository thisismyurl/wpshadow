<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_WoocommerceCoreSecurity extends Diagnostic_Base {
	protected static $slug = 'woocommerce-core-security';
	protected static $title = 'WooCommerce Core Security';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! class_exists( 'WooCommerce' ) ) { return null; }
		$force_ssl = get_option( 'woocommerce_force_ssl_checkout', 'no' );
		if ( 'yes' !== $force_ssl && ! is_ssl() ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'SSL not enforced for checkout', 'wpshadow' ),
				'severity' => 'critical',
				'threat_level' => 95,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/woocommerce-ssl',
			);
		}
		return null;
	}
}
