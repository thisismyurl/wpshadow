<?php
/**
 * Hotel Booking Availability Diagnostic
 *
 * Hotel availability checks slow.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.609.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Hotel Booking Availability Diagnostic Class
 *
 * @since 1.609.0000
 */
class Diagnostic_HotelBookingAvailability extends Diagnostic_Base {

	protected static $slug = 'hotel-booking-availability';
	protected static $title = 'Hotel Booking Availability';
	protected static $description = 'Hotel availability checks slow';
	protected static $family = 'performance';

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
				'severity'    => self::calculate_severity( 55 ),
				'threat_level' => 55,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/hotel-booking-availability',
			);
		}
		
		return null;
	}
}
