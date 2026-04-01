<?php
/**
 * No Authority Content or Thought Leadership Diagnostic
 *
 * Checks if thought leadership/expert content is being published.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\BusinessPerformance
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Thought Leadership Diagnostic
 *
 * Detects when business isn't establishing authority/thought leadership.
 * Publishing expert content establishes credibility, attracts media attention,
 * and justifies premium pricing. Without thought leadership, you compete on
 * price. With it, you compete on value and expertise.
 *
 * @since 0.6093.1200
 */
class Diagnostic_No_Authority_Content_Or_Thought_Leadership extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-authority-content-thought-leadership';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Thought Leadership & Authority Content Published';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if thought leadership and expert content is being published';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'marketing';

	/**
	 * Run the diagnostic check
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$thought_leadership_score = self::check_thought_leadership();

		if ( $thought_leadership_score < 50 ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'No thought leadership content detected. You\'re competing on price instead of expertise. Publishing expert content establishes credibility, attracts media, commands premium pricing, and drives inbound leads. Publish: 1) Original research (quarterly), 2) Opinion pieces (monthly), 3) Industry insights (2x/month), 4) Expert interviews (monthly), 5) Speaking at conferences, 6) Media appearances. Transform from vendor to authority.', 'wpshadow' ),
				'severity'    => 'high',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/thought-leadership-authority?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'     => array(
					'leadership_score'      => $thought_leadership_score,
					'target_score'          => 75,
					'content_types'         => self::get_leadership_content_types(),
					'business_impact'       => 'Premium pricing, inbound leads, media attention, influencer status',
					'recommendation'        => __( 'Start publishing original research and expert opinions to build authority', 'wpshadow' ),
				),
			);
		}

		return null;
	}

	/**
	 * Check thought leadership score
	 *
	 * @since 0.6093.1200
	 * @return int Score 0-100
	 */
	private static function check_thought_leadership(): int {
		$score = 0;

		// Check for research/studies
		$research_posts = get_posts( array(
			'numberposts' => 20,
			's'           => 'research OR study OR analysis OR data OR findings',
		) );

		if ( ! empty( $research_posts ) ) {
			$score += 20;
		}

		// Check for expert/opinion content
		$opinion_posts = get_posts( array(
			'numberposts' => 20,
			's'           => 'opinion OR perspective OR insight OR trend OR prediction',
		) );

		if ( ! empty( $opinion_posts ) ) {
			$score += 20;
		}

		// Check for author bio/credentials
		$response = wp_remote_get( home_url( '/' ) );

		if ( ! is_wp_error( $response ) ) {
			$body = wp_remote_retrieve_body( $response );

			if ( preg_match( '/expert|author|credentials|bio|about|published|speaker|consultant/i', $body ) ) {
				$score += 15;
			}
		}

		// Check for media mentions
		$media_posts = get_posts( array(
			'numberposts' => 10,
			's'           => 'as seen in OR featured in OR published in OR press OR media',
		) );

		if ( ! empty( $media_posts ) ) {
			$score += 20;
		}

		// Check for speaking engagements
		$speaking_posts = get_posts( array(
			'numberposts' => 10,
			's'           => 'speaking OR conference OR presentation OR seminar OR webinar',
		) );

		if ( ! empty( $speaking_posts ) ) {
			$score += 20;
		}

		return min( 100, $score );
	}

	/**
	 * Get leadership content types
	 *
	 * @since 0.6093.1200
	 * @return array Array of content types
	 */
	private static function get_leadership_content_types(): array {
		return array(
			array(
				'type'        => 'Original Research',
				'frequency'   => 'Quarterly',
				'effort'      => 'High (survey, analyze, publish)',
				'impact'      => 'Highest authority, media pick-up, earned PR',
				'example'     => 'Annual State of Industry report with original data',
			),
			array(
				'type'        => 'Expert Opinion/Perspective',
				'frequency'   => 'Monthly',
				'effort'      => 'Medium (1-2 hours writing)',
				'impact'      => 'High authority, thought leader positioning',
				'example'     => 'React to news: "Why Company X\'s decision was wrong"',
			),
			array(
				'type'        => 'Industry Insights',
				'frequency'   => '2x per month',
				'effort'      => 'Medium (research + analysis)',
				'impact'      => 'Medium authority, expert positioning',
				'example'     => 'Deep dive: "3 trends shaping industry in 2026"',
			),
			array(
				'type'        => 'Expert Interviews',
				'frequency'   => 'Monthly',
				'effort'      => 'Medium (scheduling + production)',
				'impact'      => 'High authority, audience expansion via guests',
				'example'     => 'Interview industry leader, publish Q&A',
			),
			array(
				'type'        => 'Conference Speaking',
				'frequency'   => '2-4 times/year',
				'effort'      => 'High (preparation)',
				'impact'      => 'Very high authority, audience building',
				'example'     => 'Keynote or breakout session at industry conference',
			),
			array(
				'type'        => 'Media Appearances',
				'frequency'   => 'As opportunities arise',
				'effort'      => 'Medium (relationship building)',
				'impact'      => 'Very high authority, brand expansion',
				'example'     => 'Quote in Forbes article, podcast interview',
			),
		);
	}
}
