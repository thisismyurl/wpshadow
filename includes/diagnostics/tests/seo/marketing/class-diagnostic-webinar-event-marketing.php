<?php
/**
 * Webinar and Event Marketing Diagnostic
 *
 * Checks whether webinar or event marketing tools are configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Marketing
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Webinar and Event Marketing Diagnostic Class
 *
 * Verifies webinar/event platform configuration and registration workflows.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Webinar_Event_Marketing extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'webinar-event-marketing';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'No Webinar or Event Marketing Strategy';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether webinars or event marketing tools are present';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'lead-generation';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$stats    = array();
		$issues   = array();
		$warnings = array();

		$total_points  = 100;
		$earned_points = 0;

		// Check for webinar/event platforms (55 points).
		$event_plugins = array(
			'the-events-calendar/the-events-calendar.php' => 'The Events Calendar',
			'event-espresso-free/event-espresso.php'      => 'Event Espresso',
			'webinarpress/webinarpress.php'               => 'WebinarPress',
			'wp-webinarsystem/wp-webinarsystem.php'       => 'WP WebinarSystem',
			'video-conferencing-with-zoom-api/video-conferencing-with-zoom-api.php' => 'Zoom Video Conferencing',
		);

		$active_events = array();
		foreach ( $event_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_events[] = $plugin_name;
				$earned_points  += 20;
			}
		}

		if ( count( $active_events ) > 0 ) {
			$stats['event_tools'] = implode( ', ', $active_events );
		} else {
			$issues[] = __( 'No webinar or event platform detected', 'wpshadow' );
		}

		// Check for registration pages (30 points).
		$registration_pages = self::find_pages_by_keywords(
			array(
				'webinar',
				'event',
				'register',
				'workshop',
			)
		);

		if ( count( $registration_pages ) > 0 ) {
			$earned_points              += 30;
			$stats['registration_pages'] = implode( ', ', $registration_pages );
		} else {
			$warnings[] = __( 'No webinar registration or event pages detected', 'wpshadow' );
		}

		// Check for email automation (15 points).
		$email_plugins = array(
			'mailchimp-for-wp/mailchimp-for-wp.php' => 'Mailchimp for WP',
			'fluentcrm/fluentcrm.php'              => 'FluentCRM',
			'newsletter/newsletter.php'            => 'Newsletter',
		);

		$active_email = array();
		foreach ( $email_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_email[] = $plugin_name;
				$earned_points += 5;
			}
		}

		if ( count( $active_email ) > 0 ) {
			$stats['email_tools'] = implode( ', ', $active_email );
		}

		$score      = ( $earned_points / $total_points ) * 100;
		$score_text = round( $score ) . '%';

		$stats['total_points']  = $total_points;
		$stats['earned_points'] = $earned_points;
		$stats['score']         = $score_text;

		if ( $score < 50 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: Score percentage */
					__( 'Your webinar and event marketing scored %s. Webinars create high-intent leads because attendees invest time. Without a webinar or event strategy, you miss a powerful channel for trust and conversions.', 'wpshadow' ),
					$score_text
				) . ' ' . implode( ' ', $issues ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/webinar-event-marketing?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'      => array(
					'stats'    => $stats,
					'issues'   => $issues,
					'warnings' => $warnings,
				),
			);
		}

		return null;
	}

	/**
	 * Find pages or posts by keyword search.
	 *
	 * @since 0.6093.1200
	 * @param  array $keywords Keywords to search for.
	 * @return array List of matching page titles.
	 */
	private static function find_pages_by_keywords( array $keywords ): array {
		$matches = array();

		foreach ( $keywords as $keyword ) {
			$results = get_posts(
				array(
					's'              => $keyword,
					'post_type'      => array( 'page', 'post' ),
					'posts_per_page' => 5,
				)
			);

			foreach ( $results as $post ) {
				$matches[ $post->ID ] = get_the_title( $post );
			}
		}

		return array_values( $matches );
	}
}
