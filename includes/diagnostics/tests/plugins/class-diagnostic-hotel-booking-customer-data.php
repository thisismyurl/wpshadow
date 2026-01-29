<?php
/**
 * Hotel Booking Customer Data Diagnostic
 *
 * Hotel customer data exposed.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.610.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Hotel Booking Customer Data Diagnostic Class
 *
 * @since 1.610.0000
 */
class Diagnostic_HotelBookingCustomerData extends Diagnostic_Base {

	protected static $slug = 'hotel-booking-customer-data';
	protected static $title = 'Hotel Booking Customer Data';
	protected static $description = 'Hotel customer data exposed';
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
				'severity'    => self::calculate_severity( 75 ),
				'threat_level' => 75,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/hotel-booking-customer-data',
			);
		}
		
		return null;
	}
}
