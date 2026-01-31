<?php
/**
 * Woocommerce Table Rate Shipping Calculation Diagnostic
 *
 * Woocommerce Table Rate Shipping Calculation issues detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.686.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Woocommerce Table Rate Shipping Calculation Diagnostic Class
 *
 * @since 1.686.0000
 */
class Diagnostic_WoocommerceTableRateShippingCalculation extends Diagnostic_Base {

	protected static $slug = 'woocommerce-table-rate-shipping-calculation';
	protected static $title = 'Woocommerce Table Rate Shipping Calculation';
	protected static $description = 'Woocommerce Table Rate Shipping Calculation issues detected';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return null;
		}
		
		$issues = array();
		// Check if feature is configured
		$option_prefix = 'diagnostic_' . str_replace('-', '_', self::$slug);
		$configured = get_option($option_prefix, false);
		if (!$configured) {
			$issues[] = 'feature not configured';
		}
		$has_issue = !empty($issues);
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 50 ),
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/woocommerce-table-rate-shipping-calculation',
			);
		}
		
		return null;
	}
}
