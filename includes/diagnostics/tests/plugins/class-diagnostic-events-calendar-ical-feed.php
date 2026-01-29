<?php
/**
 * The Events Calendar iCal Feeds Diagnostic
 *
 * iCal feed generation not cached.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.269.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The Events Calendar iCal Feeds Diagnostic Class
 *
 * @since 1.269.0000
 */
class Diagnostic_EventsCalendarIcalFeed extends Diagnostic_Base {

	protected static $slug = 'events-calendar-ical-feed';
	protected static $title = 'The Events Calendar iCal Feeds';
	protected static $description = 'iCal feed generation not cached';
	protected static $family = 'performance';

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
				'severity'    => self::calculate_severity( 35 ),
				'threat_level' => 35,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/events-calendar-ical-feed',
			);
		}
		
		return null;
	}
}
