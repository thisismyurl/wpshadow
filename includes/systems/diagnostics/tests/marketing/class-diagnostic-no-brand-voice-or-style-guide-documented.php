<?php
/**
 * No Brand Voice or Style Guide Documented Diagnostic
 *
 * Checks if brand voice/style guide is documented.
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
 * Brand Voice/Style Guide Documented Diagnostic
 *
 * Consistent brand voice increases brand recognition by 33% and builds trust.
 * Without a style guide, messaging is inconsistent across channels and team members.
 *
 * @since 1.6093.1200
 */
class Diagnostic_No_Brand_Voice_Or_Style_Guide_Documented extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'no-brand-voice-style-guide';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Brand Voice or Style Guide Documented';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if brand voice or style guide is documented';

	/**
	 * Diagnostic family.
	 *
	 * @var string
	 */
	protected static $family = 'marketing';

	/**
	 * Run diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		if ( ! self::has_style_guide() ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'No brand voice or style guide detected. Your messaging is inconsistent across website, emails, social media, and team members. Consistent brand voice increases recognition by 33% and builds trust. Document: 1) Brand personality (3-5 adjectives: professional, friendly, authoritative), 2) Tone guidelines (formal vs casual, serious vs playful), 3) Writing style (active voice, sentence length, jargon rules), 4) Voice examples (dos and don\'ts), 5) Visual style (colors, fonts, imagery), 6) Terminology (product names, customer titles). Style guide ensures consistency.', 'wpshadow' ),
				'severity'    => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/brand-voice-style-guide',
				'details'     => array(
					'issue'               => __( 'No brand voice or style guide documented', 'wpshadow' ),
					'recommendation'      => __( 'Create brand style guide documenting voice, tone, and writing standards', 'wpshadow' ),
					'business_impact'     => __( 'Inconsistent messaging reducing brand recognition by up to 33%', 'wpshadow' ),
					'guide_sections'      => self::get_guide_sections(),
					'voice_attributes'    => self::get_voice_attributes(),
				),
			);
		}

		return null;
	}

	/**
	 * Check if style guide exists.
	 *
	 * @since 1.6093.1200
	 * @return bool True if guide detected, false otherwise.
	 */
	private static function has_style_guide() {
		// Check for style guide content
		$guide_posts = self::count_posts_by_keywords(
			array(
				'style guide',
				'brand voice',
				'brand guidelines',
				'tone of voice',
				'writing style',
				'editorial guidelines',
				'brand standards',
			)
		);

		return $guide_posts > 0;
	}

	/**
	 * Count posts containing specific keywords.
	 *
	 * @since 1.6093.1200
	 * @param  array $keywords Keywords to search for.
	 * @return int Number of matching posts.
	 */
	private static function count_posts_by_keywords( $keywords ) {
		$total = 0;

		foreach ( $keywords as $keyword ) {
			$posts = get_posts(
				array(
					's'              => $keyword,
					'posts_per_page' => 1,
					'post_type'      => array( 'post', 'page' ),
					'post_status'    => 'publish',
					'fields'         => 'ids',
				)
			);

			if ( ! empty( $posts ) ) {
				++$total;
			}
		}

		return $total;
	}

	/**
	 * Get style guide sections.
	 *
	 * @since 1.6093.1200
	 * @return array Guide sections with descriptions.
	 */
	private static function get_guide_sections() {
		return array(
			'brand_personality' => __( 'Core personality traits (professional, friendly, innovative, authoritative)', 'wpshadow' ),
			'tone_guidelines'   => __( 'Tone spectrum (formal to casual, serious to playful)', 'wpshadow' ),
			'writing_rules'     => __( 'Grammar and style (active voice, sentence length, contractions)', 'wpshadow' ),
			'vocabulary'        => __( 'Terminology standards (product names, industry jargon, customer titles)', 'wpshadow' ),
			'voice_examples'    => __( 'Before/after examples showing correct brand voice', 'wpshadow' ),
			'visual_standards'  => __( 'Colors, fonts, logo usage, imagery style', 'wpshadow' ),
			'channel_guidance'  => __( 'How voice adapts across email, social, website, support', 'wpshadow' ),
		);
	}

	/**
	 * Get voice attributes to define.
	 *
	 * @since 1.6093.1200
	 * @return array Voice attributes with examples.
	 */
	private static function get_voice_attributes() {
		return array(
			'formality'    => __( 'Formal vs Casual: "We recommend" vs "We think you\'ll love"', 'wpshadow' ),
			'enthusiasm'   => __( 'Reserved vs Enthusiastic: "Good results" vs "Amazing results!"', 'wpshadow' ),
			'expertise'    => __( 'Authoritative vs Friendly: "Studies show" vs "Here\'s what we\'ve found"', 'wpshadow' ),
			'humor'        => __( 'Serious vs Playful: Straight facts vs occasional jokes', 'wpshadow' ),
			'complexity'   => __( 'Technical vs Simple: Jargon-heavy vs plain language', 'wpshadow' ),
			'perspective'  => __( 'Company vs Customer: "Our solution" vs "Your success"', 'wpshadow' ),
		);
	}
}
