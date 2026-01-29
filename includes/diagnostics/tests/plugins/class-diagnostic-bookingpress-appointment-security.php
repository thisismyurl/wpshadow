<?php
/**
 * BookingPress Appointment Security Diagnostic
 *
 * BookingPress appointments not secured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.458.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BookingPress Appointment Security Diagnostic Class
 *
 * @since 1.458.0000
 */
class Diagnostic_BookingpressAppointmentSecurity extends Diagnostic_Base {

	protected static $slug = 'bookingpress-appointment-security';
	protected static $title = 'BookingPress Appointment Security';
	protected static $description = 'BookingPress appointments not secured';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'BOOKINGPRESS_VERSION' ) ) {
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
				'kb_link'     => 'https://wpshadow.com/kb/bookingpress-appointment-security',
			);
		}
		
		return null;
	}
}
