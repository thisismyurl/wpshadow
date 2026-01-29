<?php
/**
 * Woocommerce Pre Orders Payment Diagnostic
 *
 * Woocommerce Pre Orders Payment issues detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.668.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Woocommerce Pre Orders Payment Diagnostic Class
 *
 * @since 1.668.0000
 */
class Diagnostic_WoocommercePreOrdersPayment extends Diagnostic_Base {

	protected static $slug = 'woocommerce-pre-orders-payment';
	protected static $title = 'Woocommerce Pre Orders Payment';
	protected static $description = 'Woocommerce Pre Orders Payment issues detected';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return null;
		}
		
		$has_issue = false;
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 70 ),
				'threat_level' => 70,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/woocommerce-pre-orders-payment',
			);
		}
		
		return null;
	}
}
