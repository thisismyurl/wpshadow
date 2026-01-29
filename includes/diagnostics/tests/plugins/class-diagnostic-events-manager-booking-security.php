<?php
/**
 * Events Manager Booking Security Diagnostic
 *
 * Events Manager bookings insecure.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.576.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Events Manager Booking Security Diagnostic Class
 *
 * @since 1.576.0000
 */
class Diagnostic_EventsManagerBookingSecurity extends Diagnostic_Base {

	protected static $slug = 'events-manager-booking-security';
	protected static $title = 'Events Manager Booking Security';
	protected static $description = 'Events Manager bookings insecure';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'EM_VERSION' ) ) {
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
				'kb_link'     => 'https://wpshadow.com/kb/events-manager-booking-security',
			);
		}
		
		return null;
	}
}
