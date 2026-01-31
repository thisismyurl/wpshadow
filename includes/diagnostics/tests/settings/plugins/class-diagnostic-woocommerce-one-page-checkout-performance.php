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
				'severity'    => self::calculate_severity( 55 ),
				'threat_level' => 55,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/woocommerce-one-page-checkout-performance',
			);
		}
		
		return null;
	}
}
