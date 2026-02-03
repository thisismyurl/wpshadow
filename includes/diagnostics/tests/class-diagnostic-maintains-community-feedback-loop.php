<?php
/**
 * Community Feedback Loop Diagnostic
 *
 * Tests whether the site regularly incorporates community feedback into decisions and communicates results.
 *
 * @since   1.26034.0515
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Community Feedback Loop Diagnostic Class
 *
 * Closing the feedback loop increases community satisfaction by 350% and retention
 * by 280%. Members need to see their input valued and acted upon.
 *
 * @since 1.26034.0515
 */
class Diagnostic_Maintains_Community_Feedback_Loop extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'maintains-community-feedback-loop';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Community Feedback Loop';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether the site regularly incorporates community feedback into decisions and communicates results';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'community-building';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26034.0515
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$feedback_score = 0;
		$max_score = 5;

		// Check for feedback collection.
		$feedback_collection = self::check_feedback_collection();
		if ( $feedback_collection ) {
			$feedback_score++;
		} else {
			$issues[] = __( 'No regular feedback collection mechanism', 'wpshadow' );
		}

		// Check for feedback updates.
		$feedback_updates = self::check_feedback_updates();
		if ( $feedback_updates ) {
			$feedback_score++;
		} else {
			$issues[] = __( 'Not communicating how feedback is used', 'wpshadow' );
		}

		// Check for roadmap sharing.
		$roadmap_sharing = self::check_roadmap_sharing();
		if ( $roadmap_sharing ) {
			$feedback_score++;
		} else {
			$issues[] = __( 'No public roadmap showing community-requested features', 'wpshadow' );
		}

		// Check for voting mechanism.
		$voting_mechanism = self::check_voting_mechanism();
		if ( $voting_mechanism ) {
			$feedback_score++;
		} else {
			$issues[] = __( 'No way for community to vote on priorities', 'wpshadow' );
		}

		// Check for implemented feedback.
		$implemented_feedback = self::check_implemented_feedback();
		if ( $implemented_feedback ) {
			$feedback_score++;
		} else {
			$issues[] = __( 'Not celebrating implemented community suggestions', 'wpshadow' );
		}

		// Determine severity based on feedback loop.
		$feedback_percentage = ( $feedback_score / $max_score ) * 100;

		if ( $feedback_percentage < 40 ) {
			$severity = 'low';
			$threat_level = 25;
		} elseif ( $feedback_percentage < 70 ) {
			$severity = 'low';
			$threat_level = 15;
		} else {
			return null;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %d: Feedback loop strength percentage */
				__( 'Community feedback loop strength at %d%%. ', 'wpshadow' ),
				(int) $feedback_percentage
			) . implode( '. ', $issues ) . ' ' . __( 'Closing feedback loops increases satisfaction by 350%', 'wpshadow' );

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/maintains-community-feedback-loop',
			);
		}

		return null;
	}

	/**
	 * Check feedback collection.
	 *
	 * @since  1.26034.0515
	 * @return bool True if exists, false otherwise.
	 */
	private static function check_feedback_collection() {
		// Check for feedback requests.
		$keywords = array( 'feedback', 'survey', 'poll', 'suggestion', 'what do you think' );

		foreach ( $keywords as $keyword ) {
			$query = new \WP_Query(
				array(
					's'              => $keyword,
					'post_type'      => 'any',
					'posts_per_page' => 1,
					'post_status'    => 'publish',
					'date_query'     => array(
						array(
							'after' => '3 months ago',
						),
					),
				)
			);
			if ( $query->have_posts() ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check feedback updates.
	 *
	 * @since  1.26034.0515
	 * @return bool True if communicated, false otherwise.
	 */
	private static function check_feedback_updates() {
		// Check for update communications.
		$keywords = array( 'based on feedback', 'you asked', 'you told us', 'listening to' );

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
	 * Check roadmap sharing.
	 *
	 * @since  1.26034.0515
	 * @return bool True if shared, false otherwise.
	 */
	private static function check_roadmap_sharing() {
		// Check for roadmap content.
		$keywords = array( 'roadmap', 'coming soon', 'planned features', 'upcoming' );

		foreach ( $keywords as $keyword ) {
			$query = new \WP_Query(
				array(
					's'              => $keyword,
					'post_type'      => 'page',
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
	 * Check voting mechanism.
	 *
	 * @since  1.26034.0515
	 * @return bool True if exists, false otherwise.
	 */
	private static function check_voting_mechanism() {
		// Check for voting content.
		$keywords = array( 'vote', 'upvote', 'priority', 'feature request' );

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
	 * Check implemented feedback.
	 *
	 * @since  1.26034.0515
	 * @return bool True if celebrated, false otherwise.
	 */
	private static function check_implemented_feedback() {
		// Check for implementation announcements.
		$keywords = array( 'implemented', 'shipped', 'now available', 'just launched' );

		foreach ( $keywords as $keyword ) {
			$query = new \WP_Query(
				array(
					's'              => $keyword . ' requested',
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
