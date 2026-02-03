<?php
/**
 * Community Events Hosted Diagnostic
 *
 * Tests whether the site hosts virtual or in-person community gatherings at least quarterly.
 *
 * @since   1.26034.0500
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Community Events Hosted Diagnostic Class
 *
 * Community events increase engagement by 600% and create 5x stronger member
 * relationships. Regular gatherings are essential for community cohesion.
 *
 * @since 1.26034.0500
 */
class Diagnostic_Hosts_Community_Events extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'hosts-community-events';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Community Events Hosted';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether the site hosts virtual or in-person community gatherings at least quarterly';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'community-building';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26034.0500
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$events_score = 0;
		$max_score = 5;

		// Check for event posts.
		$event_posts = self::check_event_posts();
		if ( $event_posts ) {
			$events_score++;
		} else {
			$issues[] = __( 'No community events announced', 'wpshadow' );
		}

		// Check for quarterly frequency.
		$quarterly_frequency = self::check_quarterly_frequency();
		if ( $quarterly_frequency ) {
			$events_score++;
		} else {
			$issues[] = __( 'Events not held quarterly (minimum 4/year)', 'wpshadow' );
		}

		// Check for event platform.
		$event_platform = self::check_event_platform();
		if ( $event_platform ) {
			$events_score++;
		} else {
			$issues[] = __( 'No event management system or platform integration', 'wpshadow' );
		}

		// Check for event types.
		$event_types = self::check_event_types();
		if ( $event_types ) {
			$events_score++;
		} else {
			$issues[] = __( 'Limited event variety (webinars, workshops, meetups)', 'wpshadow' );
		}

		// Check for event follow-up.
		$event_followup = self::check_event_followup();
		if ( $event_followup ) {
			$events_score++;
		} else {
			$issues[] = __( 'No event recordings or follow-up content shared', 'wpshadow' );
		}

		// Determine severity based on events.
		$events_percentage = ( $events_score / $max_score ) * 100;

		if ( $events_percentage < 40 ) {
			$severity = 'low';
			$threat_level = 25;
		} elseif ( $events_percentage < 70 ) {
			$severity = 'low';
			$threat_level = 15;
		} else {
			return null;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %d: Events program strength percentage */
				__( 'Community events program strength at %d%%. ', 'wpshadow' ),
				(int) $events_percentage
			) . implode( '. ', $issues ) . ' ' . __( 'Community events increase engagement by 600%', 'wpshadow' );

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/hosts-community-events',
			);
		}

		return null;
	}

	/**
	 * Check event posts.
	 *
	 * @since  1.26034.0500
	 * @return bool True if exist, false otherwise.
	 */
	private static function check_event_posts() {
		// Check for event content.
		$keywords = array( 'event', 'webinar', 'meetup', 'workshop', 'conference' );

		foreach ( $keywords as $keyword ) {
			$query = new \WP_Query(
				array(
					's'              => $keyword,
					'post_type'      => 'any',
					'posts_per_page' => 1,
					'post_status'    => 'publish',
				)
			);
			if ( $query->have_posts() ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check quarterly frequency.
	 *
	 * @since  1.26034.0500
	 * @return bool True if consistent, false otherwise.
	 */
	private static function check_quarterly_frequency() {
		// Check for recent events.
		$query = new \WP_Query(
			array(
				's'              => 'event webinar meetup',
				'post_type'      => 'any',
				'posts_per_page' => 10,
				'post_status'    => 'publish',
				'date_query'     => array(
					array(
						'after' => '1 year ago',
					),
				),
			)
		);

		// 4+ events per year = quarterly.
		return ( $query->found_posts >= 4 );
	}

	/**
	 * Check event platform.
	 *
	 * @since  1.26034.0500
	 * @return bool True if integrated, false otherwise.
	 */
	private static function check_event_platform() {
		// Check for event plugins.
		$event_plugins = array(
			'the-events-calendar/the-events-calendar.php',
			'events-manager/events-manager.php',
			'event-espresso-decaf/espresso.php',
		);

		foreach ( $event_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		// Check for Zoom/other platforms.
		$query = new \WP_Query(
			array(
				's'              => 'zoom.us meet.google.com teams.microsoft.com',
				'post_type'      => 'any',
				'posts_per_page' => 1,
				'post_status'    => 'publish',
			)
		);

		return $query->have_posts();
	}

	/**
	 * Check event types.
	 *
	 * @since  1.26034.0500
	 * @return bool True if varied, false otherwise.
	 */
	private static function check_event_types() {
		// Check for multiple event types.
		$types = array( 'webinar', 'workshop', 'meetup', 'Q&A', 'networking' );
		$found = 0;

		foreach ( $types as $type ) {
			$query = new \WP_Query(
				array(
					's'              => $type,
					'post_type'      => 'any',
					'posts_per_page' => 1,
					'post_status'    => 'publish',
				)
			);
			if ( $query->have_posts() ) {
				$found++;
			}
		}

		// At least 2 different event types.
		return ( $found >= 2 );
	}

	/**
	 * Check event follow-up.
	 *
	 * @since  1.26034.0500
	 * @return bool True if exists, false otherwise.
	 */
	private static function check_event_followup() {
		// Check for recording/recap content.
		$keywords = array( 'recording', 'replay', 'recap', 'summary', 'slides' );

		foreach ( $keywords as $keyword ) {
			$query = new \WP_Query(
				array(
					's'              => $keyword . ' event',
					'post_type'      => 'any',
					'posts_per_page' => 1,
					'post_status'    => 'publish',
				)
			);
			if ( $query->have_posts() ) {
				return true;
			}
		}

		return false;
	}
}
