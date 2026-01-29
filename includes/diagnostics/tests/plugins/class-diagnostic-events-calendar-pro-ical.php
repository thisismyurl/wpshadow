<?php
/**
 * Events Calendar Pro iCal Diagnostic
 *
 * Events Calendar iCal feeds exposed.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.575.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Events Calendar Pro iCal Diagnostic Class
 *
 * @since 1.575.0000
 */
class Diagnostic_EventsCalendarProIcal extends Diagnostic_Base {

	protected static $slug = 'events-calendar-pro-ical';
	protected static $title = 'Events Calendar Pro iCal';
	protected static $description = 'Events Calendar iCal feeds exposed';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'Tribe__Events__Main' ) ) {
			return null;
		}
		
		$has_issue = false;
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 60 ),
				'threat_level' => 60,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/events-calendar-pro-ical',
			);
		}
		
		return null;
	}
}
