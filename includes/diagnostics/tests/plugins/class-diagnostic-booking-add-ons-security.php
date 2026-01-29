<?php
/**
 * Booking Add-ons Security Diagnostic
 *
 * Booking add-on prices manipulable.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.634.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Booking Add-ons Security Diagnostic Class
 *
 * @since 1.634.0000
 */
class Diagnostic_BookingAddOnsSecurity extends Diagnostic_Base {

	protected static $slug = 'booking-add-ons-security';
	protected static $title = 'Booking Add-ons Security';
	protected static $description = 'Booking add-on prices manipulable';
	protected static $family = 'security';

	public static function check() {
		if ( ! true // Generic plugin check ) {
			return null;
		}
		
		$has_issue = false;
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 65 ),
				'threat_level' => 65,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/booking-add-ons-security',
			);
		}
		
		return null;
	}
}
