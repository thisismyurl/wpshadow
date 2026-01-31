<?php
/**
 * Woocommerce Product Vendors Reports Diagnostic
 *
 * Woocommerce Product Vendors Reports issues detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.654.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Woocommerce Product Vendors Reports Diagnostic Class
 *
 * @since 1.654.0000
 */
class Diagnostic_WoocommerceProductVendorsReports extends Diagnostic_Base {

	protected static $slug = 'woocommerce-product-vendors-reports';
	protected static $title = 'Woocommerce Product Vendors Reports';
	protected static $description = 'Woocommerce Product Vendors Reports issues detected';
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
				'kb_link'     => 'https://wpshadow.com/kb/woocommerce-product-vendors-reports',
			);
		}
		
		return null;
	}
}
