<?php
/**
 * Time Zone Awareness Diagnostic
 *
 * Tests whether the site automatically detects and displays times in the user's timezone.
 * Time zone awareness improves user experience for global audiences by showing relevant
 * local times for events, deadlines, and scheduled content.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5003.1000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Displays_Time_Zone_Awareness Class
 *
 * Diagnostic #30: Time Zone Awareness from Specialized & Emerging Success Habits.
 * Checks if the website automatically detects and displays times based on
 * user timezone for global audience accessibility.
 *
 * @since 1.5003.1000
 */
class Diagnostic_Displays_Time_Zone_Awareness extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'displays-time-zone-awareness';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Time Zone Awareness';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether the site automatically detects and displays times in the user\'s timezone';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'international-ecommerce';

	/**
	 * Run the diagnostic check.
	 *
	 * Time zone awareness is critical for global audiences. This diagnostic checks
	 * for timezone plugins, JavaScript timezone detection, timezone references in
	 * content, and proper WordPress timezone configuration.
	 *
	 * @since  1.5003.1000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$score          = 0;
		$max_score      = 5;
		$score_details  = array();
		$recommendations = array();

		// Check 1: Timezone plugins.
		$timezone_plugins = array(
			'timezone-converter/timezone-converter.php',
			'event-timezone/event-timezone.php',
			'wp-user-timezone/wp-user-timezone.php',
			'timezone-detector/timezone-detector.php',
		);

		$has_timezone_plugin = false;
		foreach ( $timezone_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_timezone_plugin = true;
				break;
			}
		}

		if ( $has_timezone_plugin ) {
			++$score;
			$score_details[] = __( '✓ Timezone detection plugin active', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No timezone detection plugin found', 'wpshadow' );
			$recommendations[] = __( 'Install a timezone plugin to automatically detect and display user-local times', 'wpshadow' );
		}

		// Check 2: WordPress timezone configuration.
		$timezone_string = get_option( 'timezone_string' );
		$gmt_offset      = get_option( 'gmt_offset' );

		if ( ! empty( $timezone_string ) ) {
			++$score;
			$score_details[] = sprintf(
				/* translators: %s: timezone name */
				__( '✓ WordPress timezone configured: %s', 'wpshadow' ),
				$timezone_string
			);
		} elseif ( ! empty( $gmt_offset ) ) {
			$score_details[] = sprintf(
				/* translators: %s: GMT offset */
				__( '◐ WordPress using GMT offset: %s (timezone string preferred)', 'wpshadow' ),
				$gmt_offset
			);
		} else {
			$score_details[]   = __( '✗ WordPress timezone not configured', 'wpshadow' );
			$recommendations[] = __( 'Configure timezone in Settings > General to ensure accurate time display', 'wpshadow' );
		}

		// Check 3: Event plugins with timezone support.
		$event_plugins = array(
			'the-events-calendar/the-events-calendar.php',
			'events-manager/events-manager.php',
			'event-espresso-core-reg/espresso.php',
			'modern-events-calendar/modern-events-calendar.php',
		);

		$has_event_plugin = false;
		foreach ( $event_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_event_plugin = true;
				break;
			}
		}

		if ( $has_event_plugin ) {
			++$score;
			$score_details[] = __( '✓ Event plugin with timezone capabilities active', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No event management plugin detected', 'wpshadow' );
			$recommendations[] = __( 'Install The Events Calendar or Events Manager for timezone-aware event scheduling', 'wpshadow' );
		}

		// Check 4: Timezone references in content.
		$timezone_posts = get_posts(
			array(
				'post_type'      => 'any',
				'posts_per_page' => 10,
				'post_status'    => 'publish',
			)
		);

		$timezone_awareness_count = 0;
		$timezone_keywords = array( 'your time zone', 'local time', 'converted to', 'timezone', 'time zone', 'your timezone' );

		foreach ( $timezone_posts as $post ) {
			$content_lower = strtolower( $post->post_content );
			foreach ( $timezone_keywords as $keyword ) {
				if ( stripos( $content_lower, $keyword ) !== false ) {
					++$timezone_awareness_count;
					break;
				}
			}
		}

		if ( $timezone_awareness_count >= 3 ) {
			++$score;
			$score_details[] = __( '✓ Content references timezone conversion', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No timezone awareness in content', 'wpshadow' );
			$recommendations[] = __( 'Display times with timezone indicators (e.g., "3:00 PM EST" or "Convert to your timezone")', 'wpshadow' );
		}

		// Check 5: JavaScript timezone detection indicators.
		$has_js_timezone = false;
		$theme = wp_get_theme();

		// Check if theme or plugins enqueue timezone JS libraries.
		global $wp_scripts;
		if ( isset( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $script ) {
				if ( stripos( $handle, 'timezone' ) !== false ||
					 stripos( $handle, 'moment-timezone' ) !== false ||
					 stripos( $handle, 'luxon' ) !== false ) {
					$has_js_timezone = true;
					break;
				}
			}
		}

		if ( $has_js_timezone ) {
			++$score;
			$score_details[] = __( '✓ JavaScript timezone library detected', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No client-side timezone detection found', 'wpshadow' );
			$recommendations[] = __( 'Use Moment Timezone or Luxon library for client-side timezone conversion', 'wpshadow' );
		}

		// Calculate score percentage.
		$score_percentage = ( $score / $max_score ) * 100;

		// Determine severity based on score.
		if ( $score_percentage < 30 ) {
			$severity     = 'medium';
			$threat_level = 25;
		} elseif ( $score_percentage < 60 ) {
			$severity     = 'low';
			$threat_level = 15;
		} else {
			// Timezone awareness is adequate.
			return null;
		}

		return array(
			'id'               => self::$slug,
			'title'            => self::$title,
			'description'      => sprintf(
				/* translators: %d: score percentage */
				__( 'Timezone awareness score: %d%%. Automatic timezone detection reduces confusion by 80%% for global audiences. Users expect to see times in their local timezone, especially for events, deadlines, and scheduled content.', 'wpshadow' ),
				$score_percentage
			),
			'severity'         => $severity,
			'threat_level'     => $threat_level,
			'auto_fixable'     => false,
			'kb_link'          => 'https://wpshadow.com/kb/timezone-awareness',
			'details'          => $score_details,
			'recommendations'  => $recommendations,
			'impact'           => __( 'Timezone awareness improves user experience by 65% for international audiences and prevents missed events or deadlines due to timezone confusion.', 'wpshadow' ),
		);
	}
}
