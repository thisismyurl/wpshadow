<?php
/**
 * No Market Positioning Statement Diagnostic
 *
 * Checks if clear market positioning statement exists.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Market Positioning Statement Diagnostic
 *
 * Clear positioning increases conversion rates by 20-30% by making
 * the value proposition immediately obvious.
 *
 * @since 1.6035.0000
 */
class Diagnostic_No_Market_Positioning_Statement extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'no-market-positioning-statement';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Market Positioning Statement';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if clear market positioning statement exists';

	/**
	 * Diagnostic family.
	 *
	 * @var string
	 */
	protected static $family = 'marketing';

	/**
	 * Run diagnostic check.
	 *
	 * @since  1.6035.0000
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		if ( ! self::has_positioning_statement() ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'No market positioning statement detected. Visitors can\'t quickly understand what you do and why it matters. Clear positioning increases conversion by 20-30%. Your positioning should answer: 1) WHO you serve (target customer), 2) WHAT problem you solve (pain point), 3) HOW you solve it differently (unique value), 4) WHY you\'re credible (proof). Format: "For [target customer] who [problem], [product] is a [category] that [benefit]. Unlike [competitor], we [differentiation]." Clarity wins.', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/market-positioning-statement',
				'details'     => array(
					'issue'               => __( 'No clear market positioning statement detected', 'wpshadow' ),
					'recommendation'      => __( 'Create positioning statement and place prominently on homepage/key pages', 'wpshadow' ),
					'business_impact'     => __( 'Losing 20-30% conversions due to unclear value proposition', 'wpshadow' ),
					'positioning_formula' => self::get_positioning_formula(),
					'examples'            => self::get_positioning_examples(),
				),
			);
		}

		return null;
	}

	/**
	 * Check if positioning statement exists.
	 *
	 * @since  1.6035.0000
	 * @return bool True if positioning detected, false otherwise.
	 */
	private static function has_positioning_statement() {
		// Check for positioning-related content
		$positioning_posts = self::count_posts_by_keywords(
			array(
				'positioning statement',
				'value proposition',
				'what we do',
				'who we serve',
				'our mission',
			)
		);

		// Low threshold - we're checking if it's well-defined
		return $positioning_posts >= 2;
	}

	/**
	 * Count posts containing specific keywords.
	 *
	 * @since  1.6035.0000
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
	 * Get positioning statement formula.
	 *
	 * @since  1.6035.0000
	 * @return array Formula components.
	 */
	private static function get_positioning_formula() {
		return array(
			'target'         => __( 'WHO: Define your ideal customer (be specific)', 'wpshadow' ),
			'problem'        => __( 'WHAT: State the pain point or desire', 'wpshadow' ),
			'solution'       => __( 'HOW: Your unique solution approach', 'wpshadow' ),
			'benefit'        => __( 'VALUE: The outcome or transformation', 'wpshadow' ),
			'differentiation' => __( 'UNLIKE: Why you\'re different/better', 'wpshadow' ),
			'proof'          => __( 'CREDIBILITY: Evidence that it works', 'wpshadow' ),
		);
	}

	/**
	 * Get positioning statement examples.
	 *
	 * @since  1.6035.0000
	 * @return array Example positioning statements.
	 */
	private static function get_positioning_examples() {
		return array(
			'uber'     => __( '"For urban professionals who need reliable transportation, Uber is a ride-sharing service that provides on-demand pickup. Unlike taxis, you get transparent pricing and cashless payment."', 'wpshadow' ),
			'slack'    => __( '"For teams who waste time in email, Slack is a messaging platform that organizes conversations by topic. Unlike email, everything is searchable and integrated with your tools."', 'wpshadow' ),
			'shopify'  => __( '"For entrepreneurs who want to sell online, Shopify is an ecommerce platform that lets you launch a store in hours. Unlike building custom, you get everything you need out of the box."', 'wpshadow' ),
			'generic'  => __( '"For [target] who [problem], [product] is a [category] that [benefit]. Unlike [alternative], we [differentiation]."', 'wpshadow' ),
		);
	}
}
