<?php
/**
 * No Competitive Benchmarking or Market Intelligence Diagnostic
 *
 * Checks if competitive benchmarking is performed.
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
 * Competitive Benchmarking Diagnostic
 *
 * Know your competitive landscape or be surprised.
 * Benchmarking reveals opportunities and threats.
 *
 * @since 0.6093.1200
 */
class Diagnostic_No_Competitive_Benchmarking_Or_Market_Intelligence extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'no-competitive-benchmarking-market-intelligence';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Competitive Benchmarking/Market Intelligence';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if competitive benchmarking is performed';

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
		if ( ! self::has_benchmarking() ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'No competitive benchmarking detected. Know your competitive landscape or be surprised. Quarterly competitive analysis: 1) Identify 3-5 key competitors, 2) Visit their websites, sign up, use product, 3) Compare: features, pricing, messaging, positioning, 4) Read reviews (Capterra, G2) - what do customers love/hate?, 5) Track pricing changes, 6) Monitor job postings (expansion signal), 7) Follow on social media (campaigns, strategy). Document: Feature comparison matrix, pricing chart, positioning vs. them, customer perception gap. This reveals: Opportunities (gaps they don\'t fill), Threats (better solutions), Benchmarks (are we catching up?). Share quarterly with team.', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/competitive-benchmarking?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'     => array(
					'issue'          => __( 'No competitive benchmarking detected', 'wpshadow' ),
					'recommendation' => __( 'Implement quarterly competitive benchmarking', 'wpshadow' ),
					'business_impact' => __( 'Flying blind to competitive threats and opportunities', 'wpshadow' ),
					'analysis_areas' => self::get_analysis_areas(),
				),
			);
		}

		return null;
	}

	/**
	 * Check if benchmarking exists.
	 *
	 * @since 0.6093.1200
	 * @return bool True if benchmarking detected, false otherwise.
	 */
	private static function has_benchmarking() {
		$benchmark_posts = self::count_posts_by_keywords(
			array(
				'competitor',
				'benchmark',
				'comparison',
				'market analysis',
				'competitive intelligence',
			)
		);

		return $benchmark_posts > 0;
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
	 * Get analysis areas.
	 *
	 * @since 0.6093.1200
	 * @return array Analysis areas to track.
	 */
	private static function get_analysis_areas() {
		return array(
			'features'     => __( '1. Feature Comparison: What do they offer that we don\'t? (create matrix)', 'wpshadow' ),
			'pricing'      => __( '2. Pricing: Price points, structure (usage-based, tiered), promotions', 'wpshadow' ),
			'messaging'    => __( 'Messaging: Tagline, value prop, positioning statements', 'wpshadow' ),
			'ux'           => __( '3. UX/Product: Ease of use, speed, design, integration breadth', 'wpshadow' ),
			'go_to_market' => __( '4. Go-to-Market: Marketing channels, partnerships, distribution', 'wpshadow' ),
			'reviews'      => __( '5. Customer Reviews: Read Capterra/G2 - what do customers love/hate?', 'wpshadow' ),
			'hiring'       => __( '6. Hiring & Growth: Job postings indicate expansion areas (signal growth)', 'wpshadow' ),
			'content'      => __( '7. Content: Blog topics, videos, webinars (what are they teaching?)', 'wpshadow' ),
		);
	}
}
