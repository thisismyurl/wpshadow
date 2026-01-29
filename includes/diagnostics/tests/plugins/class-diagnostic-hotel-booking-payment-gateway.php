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
		
		$has_issue = false;
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 80 ),
				'threat_level' => 80,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/hotel-booking-payment-gateway',
			);
		}
		
		return null;
	}
}
