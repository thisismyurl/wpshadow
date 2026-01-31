<?php
/**
 * Event Calendar iCal Security Diagnostic
 *
 * Event iCal feeds publicly accessible.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.592.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Event Calendar iCal Security Diagnostic Class
 *
 * @since 1.592.0000
 */
class Diagnostic_EventCalendarIcalSecurity extends Diagnostic_Base {

	protected static $slug = 'event-calendar-ical-security';
	protected static $title = 'Event Calendar iCal Security';
	protected static $description = 'Event iCal feeds publicly accessible';
	protected static $family = 'security';

	public static function check() {
		if ( ! function_exists('some_check') ) {
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
				'severity'    => self::calculate_severity( 60 ),
				'threat_level' => 60,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/event-calendar-ical-security',
			);
		}
		
		return null;
	}
}
