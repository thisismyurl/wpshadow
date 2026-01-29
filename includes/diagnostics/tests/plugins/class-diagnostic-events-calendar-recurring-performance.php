<?php
/**
 * The Events Calendar Recurring Events Diagnostic
 *
 * Recurring events slow down database.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.267.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The Events Calendar Recurring Events Diagnostic Class
 *
 * @since 1.267.0000
 */
class Diagnostic_EventsCalendarRecurringPerformance extends Diagnostic_Base {

	protected static $slug = 'events-calendar-recurring-performance';
	protected static $title = 'The Events Calendar Recurring Events';
	protected static $description = 'Recurring events slow down database';
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
				'severity'    => self::calculate_severity( 50 ),
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/events-calendar-recurring-performance',
			);
		}
		
		return null;
	}
}
