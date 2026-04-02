<?php
/**
 * No Brand Messaging or Value Proposition Diagnostic
 *
 * Checks if brand messaging is clearly defined.
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
 * Brand Messaging/Value Proposition Diagnostic
 *
 * Companies with clear value propositions get 2-3x more engagement.
 * Confused messaging kills conversions.
 *
 * @since 1.6093.1200
 */
class Diagnostic_No_Brand_Messaging_Or_Value_Proposition extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'no-brand-messaging-value-proposition';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Brand Messaging/Value Proposition';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if brand messaging is clearly defined';

	/**
	 * Diagnostic family.
	 *
	 * @var string
	 */
	protected static $family = 'business-performance';

	/**
	 * Run diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		if ( ! self::has_brand_messaging() ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'No clear brand messaging or value proposition detected. Confused messaging = killed conversions. Companies with clear value propositions get 2-3x more engagement. Your message should answer: 1) What do we do? (one sentence, simple), 2) Who is it for? (specific person, not everyone), 3) Why choose us? (unique advantage), 4) What\'s the benefit? (emotional outcome they want). Example: "Stripe makes online payments simple for developers" = clear audience (developers), clear benefit (simplicity), clear product (payment platform). Test: Show your homepage to 10 strangers. Can they explain in 30 seconds what you do? If not, messaging is unclear. Good messaging: Benefits (speed, ease, reliability) not features (API, webhooks, integrations).', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/brand-messaging-value-proposition',
				'details'     => array(
					'issue'               => __( 'No clear brand messaging or value proposition detected', 'wpshadow' ),
					'recommendation'      => __( 'Define and communicate clear brand value proposition', 'wpshadow' ),
					'business_impact'     => __( 'Missing 2-3x engagement improvement from clarity', 'wpshadow' ),
					'messaging_components' => self::get_messaging_components(),
					'brand_elements'      => self::get_brand_elements(),
				),
			);
		}

		return null;
	}

	/**
	 * Check if brand messaging exists.
	 *
	 * @since 1.6093.1200
	 * @return bool True if messaging detected, false otherwise.
	 */
	private static function has_brand_messaging() {
		// Check for messaging-related content
		$messaging_posts = self::count_posts_by_keywords(
			array(
				'value proposition',
				'brand message',
				'tagline',
				'mission',
				'about us',
			)
		);

		return $messaging_posts > 0;
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
	 * Get messaging components.
	 *
	 * @since 1.6093.1200
	 * @return array Key messaging components.
	 */
	private static function get_messaging_components() {
		return array(
			'what'     => array(
				'element'     => __( 'What: What do you do? (one clear sentence)', 'wpshadow' ),
				'example'     => __( '"We help small businesses manage customers easier"', 'wpshadow' ),
				'requirement' => __( 'Simple, specific, benefits-focused (not feature-focused)', 'wpshadow' ),
			),
			'who'      => array(
				'element'     => __( 'Who: Who is it for? (specific person)', 'wpshadow' ),
				'example'     => __( '"For B2B SaaS companies" not "for everyone"', 'wpshadow' ),
				'requirement' => __( 'Specific audience, named problem, clear fit', 'wpshadow' ),
			),
			'why'      => array(
				'element'     => __( 'Why: Why choose us? (unique advantage)', 'wpshadow' ),
				'example'     => __( '"Only solution with AI-powered forecasting"', 'wpshadow' ),
				'requirement' => __( 'Defensible difference vs competitors', 'wpshadow' ),
			),
			'benefit'  => array(
				'element'     => __( 'Benefit: What\'s the emotional outcome? (why should they care?)', 'wpshadow' ),
				'example'     => __( '"Sleep better knowing your business is running smoothly"', 'wpshadow' ),
				'requirement' => __( 'Emotional, aspirational, outcome-focused', 'wpshadow' ),
			),
		);
	}

	/**
	 * Get brand elements to define.
	 *
	 * @since 1.6093.1200
	 * @return array Brand elements to define.
	 */
	private static function get_brand_elements() {
		return array(
			'mission'           => __( 'Mission: Why does your company exist? (bigger purpose)', 'wpshadow' ),
			'vision'            => __( 'Vision: Where are you going? (5-10 year goal)', 'wpshadow' ),
			'values'            => __( 'Values: What do you believe? (how you operate)', 'wpshadow' ),
			'positioning'       => __( 'Positioning: How are you different? (vs competitors)', 'wpshadow' ),
			'target_persona'    => __( 'Target Persona: Who is your ideal customer? (detailed profile)', 'wpshadow' ),
			'tone_of_voice'     => __( 'Tone of Voice: How do you talk? (professional, casual, friendly)', 'wpshadow' ),
			'visual_identity'   => __( 'Visual Identity: Logo, colors, fonts (consistent look)', 'wpshadow' ),
			'brand_story'       => __( 'Brand Story: Why did you start? (founder story, origin)', 'wpshadow' ),
		);
	}
}
