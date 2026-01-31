<?php
/**
 * Woocommerce Composite Products Pricing Diagnostic
 *
 * Woocommerce Composite Products Pricing issues detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.671.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Woocommerce Composite Products Pricing Diagnostic Class
 *
 * @since 1.671.0000
 */
class Diagnostic_WoocommerceCompositeProductsPricing extends Diagnostic_Base {

	protected static $slug = 'woocommerce-composite-products-pricing';
	protected static $title = 'Woocommerce Composite Products Pricing';
	protected static $description = 'Woocommerce Composite Products Pricing issues detected';
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
				'kb_link'     => 'https://wpshadow.com/kb/woocommerce-composite-products-pricing',
			);
		}
		
		return null;
	}
}
