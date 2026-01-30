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
		
		// Check 1: Timezone configured
		$timezone = get_option( 'tribe_timezone_setting', '' );
		if ( empty( $timezone ) ) {
			$issues[] = 'Event timezone not configured';
		}
		
		// Check 2: DST handling
		$dst = get_option( 'tribe_daylight_saving_enabled', 0 );
		if ( ! $dst ) {
			$issues[] = 'Daylight saving time handling not configured';
		}
		
		// Check 3: Event display format
		$format = get_option( 'tribe_event_timezone_display_format', '' );
		if ( empty( $format ) ) {
			$issues[] = 'Event timezone display format not set';
		}
		
		// Check 4: Recurring event timezone
		$recurring = get_option( 'tribe_recurring_event_timezone_consistency', 0 );
		if ( ! $recurring ) {
			$issues[] = 'Recurring event timezone consistency not enabled';
		}
		
		// Check 5: Import timezone handling
		$import = get_option( 'tribe_import_timezone_handling_enabled', 0 );
		if ( ! $import ) {
			$issues[] = 'Import timezone handling not configured';
		}
		
		// Check 6: Timezone sync
		$sync = get_option( 'tribe_timezone_sync_enabled', 0 );
		if ( ! $sync ) {
			$issues[] = 'Timezone synchronization not enabled';
		}
		
		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 30;
			$threat_multiplier = 6;
			$max_threat = 60;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );
			
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d timezone configuration issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/events-calendar-timezone',
			);
		}
		
		return null;
	}
}
