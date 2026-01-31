<?php
/**
 * Booking Calendar Performance Diagnostic
 *
 * Booking calendar slowing page loads.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.617.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Booking Calendar Performance Diagnostic Class
 *
 * @since 1.617.0000
 */
class Diagnostic_BookingCalendarPerformance extends Diagnostic_Base {

	protected static $slug = 'booking-calendar-performance';
	protected static $title = 'Booking Calendar Performance';
	protected static $description = 'Booking calendar slowing page loads';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'BOOKING_CALENDAR_VERSION' ) && ! class_exists( 'WPBC' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Verify caching is enabled
		$cache_enabled = get_option( 'booking_cache', 0 );
		if ( ! $cache_enabled ) {
			$issues[] = 'Booking calendar cache not enabled';
		}

		// Check 2: Check for AJAX loading
		$ajax_loading = get_option( 'booking_calendar_ajax', 0 );
		if ( ! $ajax_loading ) {
			$issues[] = 'AJAX loading not enabled (can slow page loads)';
		}

		// Check 3: Verify calendar months limit
		$months_limit = get_option( 'booking_calendar_months', 0 );
		if ( $months_limit <= 0 || $months_limit > 12 ) {
			$issues[] = 'Calendar months display not optimized (recommend 1-12)';
		}

		// Check 4: Check for database cleanup
		$cleanup = get_option( 'booking_auto_cleanup', 0 );
		if ( ! $cleanup ) {
			$issues[] = 'Automatic booking cleanup not enabled';
		}

		// Check 5: Verify booking search cache
		$search_cache = get_option( 'booking_search_cache', 0 );
		if ( ! $search_cache ) {
			$issues[] = 'Booking search cache not enabled';
		}

		// Check 6: Check for page-level caching compatibility
		$page_cache = get_option( 'booking_page_cache_compat', 0 );
		if ( defined( 'WP_CACHE' ) && WP_CACHE && ! $page_cache ) {
			$issues[] = 'Page cache compatibility not enabled';
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 45;
			$threat_multiplier = 6;
			$max_threat = 75;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d booking calendar performance issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/booking-calendar-performance',
			);
		}

		return null;
	}
}
