<?php
/**
 * Woocommerce Pre Orders Notifications Diagnostic
 *
 * Woocommerce Pre Orders Notifications issues detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.670.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Woocommerce Pre Orders Notifications Diagnostic Class
 *
 * @since 1.670.0000
 */
class Diagnostic_WoocommercePreOrdersNotifications extends Diagnostic_Base {

	protected static $slug = 'woocommerce-pre-orders-notifications';
	protected static $title = 'Woocommerce Pre Orders Notifications';
	protected static $description = 'Woocommerce Pre Orders Notifications issues detected';
	protected static $family = 'functionality';

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
				'severity'    => self::calculate_severity( 50 ),
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/woocommerce-pre-orders-notifications',
			);
		}
		
		return null;
	}
}
