<?php
/**
 * Woocommerce Product Bundles Cart Diagnostic
 *
 * Woocommerce Product Bundles Cart issues detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.676.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Woocommerce Product Bundles Cart Diagnostic Class
 *
 * @since 1.676.0000
 */
class Diagnostic_WoocommerceProductBundlesCart extends Diagnostic_Base {

	protected static $slug = 'woocommerce-product-bundles-cart';
	protected static $title = 'Woocommerce Product Bundles Cart';
	protected static $description = 'Woocommerce Product Bundles Cart issues detected';
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
				'severity'    => 50,
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/woocommerce-product-bundles-cart',
			);
		}
		
		return null;
	}
}
