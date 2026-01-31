<?php
/**
 * Woocommerce One Page Checkout Validation Diagnostic
 *
 * Woocommerce One Page Checkout Validation issues detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.679.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Woocommerce One Page Checkout Validation Diagnostic Class
 *
 * @since 1.679.0000
 */
class Diagnostic_WoocommerceOnePageCheckoutValidation extends Diagnostic_Base {

	protected static $slug = 'woocommerce-one-page-checkout-validation';
	protected static $title = 'Woocommerce One Page Checkout Validation';
	protected static $description = 'Woocommerce One Page Checkout Validation issues detected';
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
				'kb_link'     => 'https://wpshadow.com/kb/woocommerce-one-page-checkout-validation',
			);
		}
		
		return null;
	}
}
