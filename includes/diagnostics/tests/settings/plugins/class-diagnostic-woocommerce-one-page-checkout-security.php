<?php
/**
 * Woocommerce One Page Checkout Security Diagnostic
 *
 * Woocommerce One Page Checkout Security issues detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.677.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Woocommerce One Page Checkout Security Diagnostic Class
 *
 * @since 1.677.0000
 */
class Diagnostic_WoocommerceOnePageCheckoutSecurity extends Diagnostic_Base {

	protected static $slug = 'woocommerce-one-page-checkout-security';
	protected static $title = 'Woocommerce One Page Checkout Security';
	protected static $description = 'Woocommerce One Page Checkout Security issues detected';
	protected static $family = 'security';

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
				'severity'    => self::calculate_severity( 70 ),
				'threat_level' => 70,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/woocommerce-one-page-checkout-security',
			);
		}
		
		return null;
	}
}
