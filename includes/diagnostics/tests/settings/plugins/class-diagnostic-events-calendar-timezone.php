<?php
/**
 * The Events Calendar Timezone Diagnostic
 *
 * Event timezone settings inconsistent.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.266.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The Events Calendar Timezone Diagnostic Class
 *
 * @since 1.266.0000
 */
class Diagnostic_EventsCalendarTimezone extends Diagnostic_Base {

	protected static $slug = 'events-calendar-timezone';
	protected static $title = 'The Events Calendar Timezone';
	protected static $description = 'Event timezone settings inconsistent';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'Tribe__Events__Main' ) ) {
			return null;
		}
		
		$issues = array();
		// Check if feature is configured
		$option_prefix = 'diagnostic_' . str_replace('-', '_', self::$slug);
		$configured = get_option($option_prefix, false);
		if (!$configured) {
			$issues[] = 'feature not configured';
		}
		$has_issue = !empty($issues)
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 45 ),
				'threat_level' => 45,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/events-calendar-timezone',
			);
		}
		
		return null;
	}
}
