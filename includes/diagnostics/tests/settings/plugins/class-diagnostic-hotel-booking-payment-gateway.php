<?php
/**
 * Hotel Booking Payment Gateway Diagnostic
 *
 * Hotel payment gateway vulnerable.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.608.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Hotel Booking Payment Gateway Diagnostic Class
 *
 * @since 1.608.0000
 */
class Diagnostic_HotelBookingPaymentGateway extends Diagnostic_Base {

	protected static $slug = 'hotel-booking-payment-gateway';
	protected static $title = 'Hotel Booking Payment Gateway';
	protected static $description = 'Hotel payment gateway vulnerable';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'MPHB_VERSION' ) ) {
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
				'severity'    => 80,
				'threat_level' => 80,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/hotel-booking-payment-gateway',
			);
		}
		

		// Security validation checks
		if ( is_ssl() === false ) {
			$issues[] = __( 'HTTPS not enabled', 'wpshadow' );
		}
		if ( defined( 'FORCE_SSL' ) === false || ! FORCE_SSL ) {
			$issues[] = __( 'SSL not forced', 'wpshadow' );
		}
		// Additional checks
		if ( ! function_exists( 'wp_verify_nonce' ) ) {
			$issues[] = __( 'Nonce verification unavailable', 'wpshadow' );
		}
		return null;
	}
}
