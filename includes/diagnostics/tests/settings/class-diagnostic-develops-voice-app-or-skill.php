<?php
/**
 * Voice App or Skill Diagnostic
 *
 * Tests whether the site has developed an Alexa Skill or Google Action for voice
 * assistant integration. Voice apps extend your brand presence to smart speakers
 * and voice assistants, enabling hands-free interactions and building a presence
 * in the growing voice ecosystem.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Develops_Voice_App_Or_Skill Class
 *
 * Diagnostic #38: Voice App or Skill from Specialized & Emerging Success Habits.
 * Checks if the website has developed voice assistant integrations like Alexa Skills
 * or Google Actions to extend brand presence into the voice ecosystem.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Develops_Voice_App_Or_Skill extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'develops-voice-app-or-skill';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Voice App or Skill';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether the site has developed an Alexa Skill or Google Action for voice assistant integration';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'voice-audio-international';

	/**
	 * Run the diagnostic check.
	 *
	 * Voice apps (Alexa Skills, Google Actions) extend your brand to voice assistants.
	 * This diagnostic checks for documentation, API endpoints, skill/action references,
	 * and development evidence.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$score          = 0;
		$max_score      = 5;
		$score_details  = array();
		$recommendations = array();

		// Check 1: Voice skill/action documentation pages.
		$skill_pages = get_posts(
			array(
				'post_type'      => 'page',
				'posts_per_page' => 5,
				'post_status'    => 'publish',
				's'              => 'alexa skill',
			)
		);

		if ( empty( $skill_pages ) ) {
			$skill_pages = get_posts(
				array(
					'post_type'      => 'page',
					'posts_per_page' => 5,
					'post_status'    => 'publish',
					's'              => 'google action',
				)
			);
		}

		if ( ! empty( $skill_pages ) ) {
			++$score;
			$score_details[] = __( '✓ Voice skill/action documentation page exists', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No voice skill documentation found', 'wpshadow' );
			$recommendations[] = __( 'Create a page documenting your Alexa Skill or Google Action', 'wpshadow' );
		}

		// Check 2: Voice skill references in content.
		$skill_posts = get_posts(
			array(
				'post_type'      => 'any',
				'posts_per_page' => 3,
				'post_status'    => 'publish',
				's'              => 'ask alexa',
			)
		);

		if ( empty( $skill_posts ) ) {
			$skill_posts = get_posts(
				array(
					'post_type'      => 'any',
					'posts_per_page' => 3,
					'post_status'    => 'publish',
					's'              => 'hey google',
				)
			);
		}

		if ( ! empty( $skill_posts ) ) {
			++$score;
			$score_details[] = __( '✓ Voice assistant commands referenced in content', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No voice assistant references in content', 'wpshadow' );
			$recommendations[] = __( 'Promote your voice skill with examples like "Ask Alexa to..." or "Hey Google..."', 'wpshadow' );
		}

		// Check 3: Voice skill JSON endpoint or webhook.
		$rest_routes = rest_get_server()->get_routes();
		$has_voice_endpoint = false;

		foreach ( $rest_routes as $route => $handlers ) {
			if ( stripos( $route, 'alexa' ) !== false ||
				 stripos( $route, 'google-assistant' ) !== false ||
				 stripos( $route, 'voice' ) !== false ||
				 stripos( $route, 'dialogflow' ) !== false ) {
				$has_voice_endpoint = true;
				break;
			}
		}

		if ( $has_voice_endpoint ) {
			++$score;
			$score_details[] = __( '✓ Voice assistant API endpoint detected', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No voice assistant API endpoints found', 'wpshadow' );
			$recommendations[] = __( 'Create a REST API endpoint to handle voice assistant requests', 'wpshadow' );
		}

		// Check 4: Skill invocation name or app listing.
		$invocation_posts = get_posts(
			array(
				'post_type'      => 'any',
				'posts_per_page' => 3,
				'post_status'    => 'publish',
				's'              => 'enable skill',
			)
		);

		if ( empty( $invocation_posts ) ) {
			$invocation_posts = get_posts(
				array(
					'post_type'      => 'any',
					'posts_per_page' => 3,
					'post_status'    => 'publish',
					's'              => 'voice app',
				)
			);
		}

		if ( ! empty( $invocation_posts ) ) {
			++$score;
			$score_details[] = __( '✓ Voice app installation instructions found', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No skill installation guidance detected', 'wpshadow' );
			$recommendations[] = __( 'Add instructions for enabling your Alexa Skill or Google Action', 'wpshadow' );
		}

		// Check 5: Voice badge or link to skill store.
		$badge_posts = get_posts(
			array(
				'post_type'      => 'any',
				'posts_per_page' => 10,
				'post_status'    => 'publish',
			)
		);

		$has_skill_badge = false;
		foreach ( $badge_posts as $post ) {
			if ( stripos( $post->post_content, 'amazon.com/skills' ) !== false ||
				 stripos( $post->post_content, 'assistant.google.com' ) !== false ||
				 stripos( $post->post_content, 'alexa skill store' ) !== false ||
				 stripos( $post->post_content, 'available on alexa' ) !== false ) {
				$has_skill_badge = true;
				break;
			}
		}

		if ( $has_skill_badge ) {
			++$score;
			$score_details[] = __( '✓ Link to voice skill store detected', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No skill store links found', 'wpshadow' );
			$recommendations[] = __( 'Add a badge or link to your skill in the Alexa Skill Store or Google Assistant directory', 'wpshadow' );
		}

		// Calculate score percentage.
		$score_percentage = ( $score / $max_score ) * 100;

		// Determine severity based on score.
		if ( $score_percentage < 25 ) {
			$severity     = 'medium';
			$threat_level = 25;
		} elseif ( $score_percentage < 60 ) {
			$severity     = 'low';
			$threat_level = 15;
		} else {
			// Voice skill presence is adequate.
			return null;
		}

		return array(
			'id'               => self::$slug,
			'title'            => self::$title,
			'description'      => sprintf(
				/* translators: %d: score percentage */
				__( 'Voice app/skill presence score: %d%%. Voice assistants are now in 200+ million devices globally. Developing an Alexa Skill or Google Action extends your brand to the voice ecosystem and provides hands-free access to your content and services.', 'wpshadow' ),
				$score_percentage
			),
			'severity'         => $severity,
			'threat_level'     => $threat_level,
			'auto_fixable'     => false,
			'kb_link'          => 'https://wpshadow.com/kb/voice-app-skill',
			'details'          => $score_details,
			'recommendations'  => $recommendations,
			'impact'           => __( 'Voice apps increase brand awareness by 180% and provide a unique competitive differentiator in your market.', 'wpshadow' ),
		);
	}
}
