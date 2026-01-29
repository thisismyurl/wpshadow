<?php
/**
 * Events Calendar Pro Performance Diagnostic
 *
 * Events Calendar Pro slowing site.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.573.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Events Calendar Pro Performance Diagnostic Class
 *
 * @since 1.573.0000
 */
class Diagnostic_EventsCalendarProPerformance extends Diagnostic_Base {

	protected static $slug = 'events-calendar-pro-performance';
	protected static $title = 'Events Calendar Pro Performance';
	protected static $description = 'Events Calendar Pro slowing site';
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
				'severity'    => self::calculate_severity( 55 ),
				'threat_level' => 55,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/events-calendar-pro-performance',
			);
		}
		
		return null;
	}
}
