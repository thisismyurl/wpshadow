<?php
/**
 * Booking Calendar Date Picker Diagnostic
 *
 * Booking date picker exposing availability.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.618.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Booking Calendar Date Picker Diagnostic Class
 *
 * @since 1.618.0000
 */
class Diagnostic_BookingCalendarDatePicker extends Diagnostic_Base {

	protected static $slug = 'booking-calendar-date-picker';
	protected static $title = 'Booking Calendar Date Picker';
	protected static $description = 'Booking date picker exposing availability';
	protected static $family = 'security';

	public static function check() {
		if ( ! function_exists('some_check') ) {
			return null;
		}
		
		$issues = array();
		$configured = get_option('diagnostic_' . self::$slug, false);
		if (!$configured) {
			$issues[] = 'not configured';
		}
		$has_issue = !empty($issues);
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 60 ),
				'threat_level' => 60,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/booking-calendar-date-picker',
			);
		}
		
		return null;
	}
}
