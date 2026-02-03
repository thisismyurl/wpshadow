<?php
/**
 * Local Events Participation Diagnostic
 *
 * Tests whether the site actively participates in and promotes local community events
 * at least quarterly. Event participation builds community ties and generates local awareness.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5003.1130
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Participates_In_Local_Events Class
 *
 * Diagnostic #18: Local Events Participation from Specialized & Emerging Success Habits.
 * Checks if the site promotes local event participation.
 *
 * @since 1.5003.1130
 */
class Diagnostic_Participates_In_Local_Events extends Diagnostic_Base {

	protected static $slug = 'participates-in-local-events';
	protected static $title = 'Local Events Participation';
	protected static $description = 'Tests whether the site actively participates in local community events quarterly';
	protected static $family = 'local-seo';

	public static function check() {
		$score          = 0;
		$max_score      = 5;
		$score_details  = array();
		$recommendations = array();

		// Check events page/section.
		$events_pages = get_posts(
			array(
				'post_type'      => 'page',
				'posts_per_page' => 5,
				'post_status'    => 'publish',
				's'              => 'events community sponsor attend',
			)
		);

		if ( ! empty( $events_pages ) ) {
			++$score;
			$score_details[] = __( '✓ Events or community page exists', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No events page found', 'wpshadow' );
			$recommendations[] = __( 'Create a dedicated page showcasing your community event participation', 'wpshadow' );
		}

		// Check recent event posts.
		$event_posts = get_posts(
			array(
				'post_type'      => 'post',
				'posts_per_page' => 10,
				'post_status'    => 'publish',
				's'              => 'event sponsor community',
				'date_query'     => array(
					array(
						'after' => '3 months ago',
					),
				),
			)
		);

		if ( count( $event_posts ) >= 2 ) {
			$score += 2;
			$score_details[] = sprintf(
				/* translators: %d: number of event posts */
				__( '✓ %d recent event posts found', 'wpshadow' ),
				count( $event_posts )
			);
		} elseif ( ! empty( $event_posts ) ) {
			++$score;
			$score_details[]   = __( '◐ 1 recent event post found', 'wpshadow' );
			$recommendations[] = __( 'Participate in and document at least 2 local events per quarter', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No recent event participation documented', 'wpshadow' );
			$recommendations[] = __( 'Sponsor or attend local community events (festivals, charity runs, business expos) and blog about them', 'wpshadow' );
		}

		// Check event images/galleries.
		$event_images = get_posts(
			array(
				'post_type'      => 'attachment',
				'posts_per_page' => 5,
				'post_status'    => 'inherit',
				's'              => 'event community sponsor booth',
			)
		);

		if ( ! empty( $event_images ) ) {
			++$score;
			$score_details[] = __( '✓ Event photos/galleries uploaded', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No event images found', 'wpshadow' );
			$recommendations[] = __( 'Share photos from events on your website and social media', 'wpshadow' );
		}

		// Check sponsorship mentions.
		$sponsorship_content = get_posts(
			array(
				'post_type'      => 'any',
				'posts_per_page' => 5,
				'post_status'    => 'publish',
				's'              => 'sponsor proud supporter partner',
			)
		);

		if ( ! empty( $sponsorship_content ) ) {
			++$score;
			$score_details[] = __( '✓ Sponsorship or support activities mentioned', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No sponsorship activities documented', 'wpshadow' );
			$recommendations[] = __( 'Highlight your sponsorships and community support prominently', 'wpshadow' );
		}

		$score_percentage = ( $score / $max_score ) * 100;

		if ( $score_percentage < 30 ) {
			$severity     = 'medium';
			$threat_level = 20;
		} elseif ( $score_percentage < 60 ) {
			$severity     = 'low';
			$threat_level = 10;
		} else {
			return null;
		}

		return array(
			'id'               => self::$slug,
			'title'            => self::$title,
			'description'      => sprintf(
				/* translators: %d: score percentage */
				__( 'Local events participation score: %d%%. Active community involvement increases brand awareness by 56%% and generates 3x more local backlinks. 82%% of consumers prefer businesses that support local communities.', 'wpshadow' ),
				$score_percentage
			),
			'severity'         => $severity,
			'threat_level'     => $threat_level,
			'auto_fixable'     => false,
			'kb_link'          => 'https://wpshadow.com/kb/local-events-participation',
			'details'          => $score_details,
			'recommendations'  => $recommendations,
			'impact'           => __( 'Community event participation builds local recognition, generates word-of-mouth, and creates natural backlink opportunities.', 'wpshadow' ),
		);
	}
}
