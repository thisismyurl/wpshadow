<?php
/**
 * WooCommerce Booking Availability Diagnostic
 *
 * WooCommerce availability checks slow.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.615.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WooCommerce Booking Availability Diagnostic Class
 *
 * @since 1.615.0000
 */
class Diagnostic_WoocommerceBookingAvailability extends Diagnostic_Base {

	protected static $slug = 'woocommerce-booking-availability';
	protected static $title = 'WooCommerce Booking Availability';
	protected static $description = 'WooCommerce availability checks slow';
	protected static $family = 'performance';

	public static function check() {
		if ( ! class_exists( 'WC_Bookings' ) ) {
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
				'kb_link'     => 'https://wpshadow.com/kb/woocommerce-booking-availability',
			);
		}
		

		// Performance optimization checks
		if ( ! defined( 'WP_CACHE' ) || ! WP_CACHE ) {
			$issues[] = __( 'Caching not enabled', 'wpshadow' );
		}
		if ( ! extension_loaded( 'zlib' ) ) {
			$issues[] = __( 'Gzip compression unavailable', 'wpshadow' );
		}
		// Check transient support
		if ( ! function_exists( 'set_transient' ) ) {
			$issues[] = __( 'Transient functions unavailable', 'wpshadow' );
		}
		return null;
	}
}
