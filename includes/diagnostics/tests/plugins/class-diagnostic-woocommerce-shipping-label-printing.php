<?php
/**
 * Woocommerce Shipping Label Printing Diagnostic
 *
 * Woocommerce Shipping Label Printing issues detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.660.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Woocommerce Shipping Label Printing Diagnostic Class
 *
 * @since 1.660.0000
 */
class Diagnostic_WoocommerceShippingLabelPrinting extends Diagnostic_Base {

	protected static $slug = 'woocommerce-shipping-label-printing';
	protected static $title = 'Woocommerce Shipping Label Printing';
	protected static $description = 'Woocommerce Shipping Label Printing issues detected';
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
				'kb_link'     => 'https://wpshadow.com/kb/woocommerce-shipping-label-printing',
			);
		}
		
		return null;
	}
}
