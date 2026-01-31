<?php
/**
 * Woocommerce Pre Orders Availability Diagnostic
 *
 * Woocommerce Pre Orders Availability issues detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.669.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Woocommerce Pre Orders Availability Diagnostic Class
 *
 * @since 1.669.0000
 */
class Diagnostic_WoocommercePreOrdersAvailability extends Diagnostic_Base {

	protected static $slug = 'woocommerce-pre-orders-availability';
	protected static $title = 'Woocommerce Pre Orders Availability';
	protected static $description = 'Woocommerce Pre Orders Availability issues detected';
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
		$has_issue = !empty($issues)
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 50 ),
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/woocommerce-pre-orders-availability',
			);
		}
		
		return null;
	}
}
