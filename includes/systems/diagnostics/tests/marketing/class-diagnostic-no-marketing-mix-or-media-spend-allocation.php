<?php
/**
 * No Marketing Mix Or Media Spend Allocation Diagnostic
 *
 * Checks if marketing budget is allocated across channels.
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
 * Marketing Mix Diagnostic
 *
 * Businesses without planned allocation of marketing budget underutilize
 * channels and get lower ROI than optimized strategies (often 40-50% lower).
 *
 * @since 0.6093.1200
 */
class Diagnostic_No_Marketing_Mix_Or_Media_Spend_Allocation extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'no-marketing-mix-media-spend-allocation';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Marketing Mix/Media Spend Allocation';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if marketing budget is allocated across channels';

	/**
	 * Diagnostic family.
	 *
	 * @var string
	 */
	protected static $family = 'marketing';

	/**
	 * Run diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		if ( ! self::has_marketing_plan() ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'No documented marketing budget allocation detected. Doing marketing without a planned mix is like cooking without a recipe—results are random. Smart allocation across channels multiplies ROI by 40-50%. Plan: 1) Total budget (annual marketing spend), 2) Channel mix (% to email, content, paid, events, etc.), 3) Seasonal peaks (Q4 higher, Q1 lower), 4) Test budget (10-15% for new channels), 5) Core budget (85-90% to proven channels), 6) Attribution model (know which channels drive actual customers), 7) Adjust quarterly (double winners, cut losers). The rule: 70% core + 20% optimization + 10% experimental.', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/marketing-mix-media-spend-allocation?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'     => array(
					'issue'               => __( 'No documented marketing budget allocation detected', 'wpshadow' ),
					'recommendation'      => __( 'Create documented marketing budget allocation across channels', 'wpshadow' ),
					'business_impact'     => __( 'Missing 40-50% ROI improvement from optimized allocation', 'wpshadow' ),
					'channel_examples'    => self::get_channel_examples(),
					'budget_framework'    => self::get_budget_framework(),
				),
			);
		}

		return null;
	}

	/**
	 * Check if marketing plan exists.
	 *
	 * @since 0.6093.1200
	 * @return bool True if plan detected, false otherwise.
	 */
	private static function has_marketing_plan() {
		// Check for marketing budget/plan content
		$plan_posts = self::count_posts_by_keywords(
			array(
				'marketing budget',
				'marketing plan',
				'media spend',
				'budget allocation',
				'marketing strategy',
			)
		);

		return $plan_posts > 0;
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
	 * Get channel allocation examples.
	 *
	 * @since 0.6093.1200
	 * @return array Channel examples with typical allocations.
	 */
	private static function get_channel_examples() {
		return array(
			'email'        => array(
				'name'       => __( 'Email Marketing', 'wpshadow' ),
				'typical_pct' => __( '20-30% of budget', 'wpshadow' ),
				'reason'     => __( 'Highest ROI ($36-45 per $1), owned channel', 'wpshadow' ),
			),
			'content'      => array(
				'name'       => __( 'Content Marketing (Blog, Video)', 'wpshadow' ),
				'typical_pct' => __( '20-30% of budget', 'wpshadow' ),
				'reason'     => __( 'Long-term organic traffic, builds authority', 'wpshadow' ),
			),
			'paid_ads'     => array(
				'name'       => __( 'Paid Ads (Google, Facebook, LinkedIn)', 'wpshadow' ),
				'typical_pct' => __( '20-30% of budget', 'wpshadow' ),
				'reason'     => __( 'Immediate traffic, measurable ROI', 'wpshadow' ),
			),
			'events'       => array(
				'name'       => __( 'Events & Partnerships', 'wpshadow' ),
				'typical_pct' => __( '10-20% of budget', 'wpshadow' ),
				'reason'     => __( 'Builds relationships, PR value', 'wpshadow' ),
			),
			'tools'        => array(
				'name'       => __( 'Tools & Infrastructure', 'wpshadow' ),
				'typical_pct' => __( '10-20% of budget', 'wpshadow' ),
				'reason'     => __( 'Analytics, marketing automation, design', 'wpshadow' ),
			),
		);
	}

	/**
	 * Get budget framework rules.
	 *
	 * @since 0.6093.1200
	 * @return array Budget allocation framework.
	 */
	private static function get_budget_framework() {
		return array(
			'core'          => array(
				'pct'         => __( '70% of budget', 'wpshadow' ),
				'purpose'     => __( 'Proven channels (replicate what works)', 'wpshadow' ),
				'examples'    => __( 'Email, content, paid ads, existing partnerships', 'wpshadow' ),
			),
			'optimization'  => array(
				'pct'         => __( '20% of budget', 'wpshadow' ),
				'purpose'     => __( 'Improving existing channels (A/B testing, better creative)', 'wpshadow' ),
				'examples'    => __( 'Better email sequences, improved landing pages, better audience targeting', 'wpshadow' ),
			),
			'experimental'  => array(
				'pct'         => __( '10% of budget', 'wpshadow' ),
				'purpose'     => __( 'Testing new channels and ideas', 'wpshadow' ),
				'examples'    => __( 'New social platform, new partnership, new content type', 'wpshadow' ),
			),
		);
	}
}
