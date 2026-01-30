<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_WoocommerceTaxConfiguration extends Diagnostic_Base {
	protected static $slug = 'woocommerce-tax-configuration';
	protected static $title = 'WooCommerce Tax Configuration';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! class_exists( 'WooCommerce' ) ) { return null; }
		$tax_enabled = get_option( 'woocommerce_calc_taxes', 'no' );
		if ( 'yes' !== $tax_enabled ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'Tax calculation not enabled', 'wpshadow' ),
				'severity' => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/woocommerce-tax',
			);
		}
		return null;
	}
}
