<?php
/**
 * No Competitive Differentiation or Market Positioning Diagnostic
 *
 * Checks if competitive differentiation is defined.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Competitive Differentiation Diagnostic
 *
 * Without clear differentiation, you compete on price and lose.
 * Being different matters more than being better.
 *
 * @since 0.6093.1200
 */
class Diagnostic_No_Competitive_Differentiation_Or_Market_Positioning extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'no-competitive-differentiation-market-positioning';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Competitive Differentiation/Market Positioning';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if competitive differentiation is defined';

	/**
	 * Diagnostic family.
	 *
	 * @var string
	 */
	protected static $family = 'business-performance';

	/**
	 * Run diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		if ( ! self::has_differentiation() ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'No clear competitive differentiation detected. Without differentiation, you compete on price and lose. Being different matters more than being better. Identify: 1) Key competitors (who\'s similar?), 2) What they\'re known for (their strengths), 3) What they miss (gaps in the market), 4) Your unique advantage (what ONLY you do?), 5) Your positioning statement (how would you position differently?). Types of differentiation: Technology (unique tech), Service (better support), Niche (specific market), Approach (different way), Price (cost leader), Experience (amazing UX), Speed (fastest), Ethical (values-driven). Example: "Slack vs email = different approach to communication". Own your differentiation and communicate it everywhere.', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/competitive-differentiation?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'     => array(
					'issue'               => __( 'No clear competitive differentiation detected', 'wpshadow' ),
					'recommendation'      => __( 'Define and communicate competitive differentiation', 'wpshadow' ),
					'business_impact'     => __( 'Forced to compete on price instead of value', 'wpshadow' ),
					'differentiation_types' => self::get_differentiation_types(),
					'positioning_exercise' => self::get_positioning_exercise(),
				),
			);
		}

		return null;
	}

	/**
	 * Check if differentiation exists.
	 *
	 * @since 0.6093.1200
	 * @return bool True if differentiation detected, false otherwise.
	 */
	private static function has_differentiation() {
		// Check for competitive content
		$comp_posts = self::count_posts_by_keywords(
			array(
				'differentiation',
				'positioning',
				'competitive',
				'vs',
				'alternative',
			)
		);

		return $comp_posts > 0;
	}

	/**
	 * Count posts containing specific keywords.
	 *
	 * @since 0.6093.1200
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
	 * Get differentiation types.
	 *
	 * @since 0.6093.1200
	 * @return array Types of differentiation with examples.
	 */
	private static function get_differentiation_types() {
		return array(
			'technology'  => array(
				'type'        => __( 'Technology: Unique technology', 'wpshadow' ),
				'example'     => __( 'Tesla = electric vehicles + autopilot', 'wpshadow' ),
			),
			'service'     => array(
				'type'        => __( 'Service: Superior customer service', 'wpshadow' ),
				'example'     => __( 'Zappos = free shipping + free returns', 'wpshadow' ),
			),
			'niche'       => array(
				'type'        => __( 'Niche: Serve a specific market', 'wpshadow' ),
				'example'     => __( 'Basecamp = project management for small teams', 'wpshadow' ),
			),
			'approach'    => array(
				'type'        => __( 'Approach: Different way of doing things', 'wpshadow' ),
				'example'     => __( 'Slack = messaging instead of email', 'wpshadow' ),
			),
			'price'       => array(
				'type'        => __( 'Price: Lowest cost leader', 'wpshadow' ),
				'example'     => __( 'Walmart = always low prices', 'wpshadow' ),
			),
			'experience'  => array(
				'type'        => __( 'Experience: Amazing user experience', 'wpshadow' ),
				'example'     => __( 'Apple = beautiful design, intuitive', 'wpshadow' ),
			),
			'speed'       => array(
				'type'        => __( 'Speed: Fastest solution', 'wpshadow' ),
				'example'     => __( 'Amazon Prime = 2-day delivery', 'wpshadow' ),
			),
			'values'      => array(
				'type'        => __( 'Values: Ethical/sustainable', 'wpshadow' ),
				'example'     => __( 'Patagonia = environmental responsibility', 'wpshadow' ),
			),
		);
	}

	/**
	 * Get positioning exercise.
	 *
	 * @since 0.6093.1200
	 * @return array Positioning exercise steps.
	 */
	private static function get_positioning_exercise() {
		return array(
			'competitors' => __( '1. List 3 main competitors (who do customers compare you to?)', 'wpshadow' ),
			'strengths'   => __( '2. Strengths: What is each known for? (their advantages)', 'wpshadow' ),
			'gaps'        => __( '3. Gaps: What do they miss? (customer pain points)', 'wpshadow' ),
			'unique'      => __( '4. Unique: What ONLY you do? (defensible advantage)', 'wpshadow' ),
			'statement'   => __( '5. Write positioning: "For [customer], [brand] is [category] that [benefit], unlike [competitor] we [differentiation]"', 'wpshadow' ),
			'validation'  => __( '6. Test: Can customers articulate your difference in 30 seconds?', 'wpshadow' ),
			'execution'   => __( '7. Execute: Use differentiation in all messaging (website, ads, sales)', 'wpshadow' ),
		);
	}
}
