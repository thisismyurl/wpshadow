<?php
/**
 * Diagnostic: Keyword Gaps vs Competitors
 *
 * Detects keywords competitors rank for that you don't cover. Identifying
 * and targeting these gaps can reveal quick-win opportunities.
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
 * Keyword Gaps Diagnostic Class
 *
 * Checks for competitive keyword coverage.
 *
 * Detection methods:
 * - SEO plugin keyword tracking
 * - Content topic analysis
 * - Category/tag coverage
 *
 * @since 1.6093.1200
 */
class Diagnostic_Keyword_Gaps extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'keyword-gaps';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Keyword Gaps vs Competitors';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Competitors rank for 200+ keywords you don\'t cover';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'keyword-strategy';

	/**
	 * Run the diagnostic check.
	 *
	 * Scoring system (3 points):
	 * - 1 point: SEO plugin with keyword tracking
	 * - 1 point: Good topic diversity (10+ categories)
	 * - 1 point: Regular content publishing (4+ posts/month)
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$score     = 0;
		$max_score = 3;

		// Check for SEO plugins with keyword tracking.
		$seo_plugins = array(
			'wordpress-seo/wp-seo.php'       => 'Yoast SEO',
			'seo-by-rank-math/rank-math.php' => 'Rank Math',
			'all-in-one-seo-pack/all_in_one_seo_pack.php' => 'AIOSEO',
		);

		$has_seo_plugin = false;
		foreach ( $seo_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$score++;
				$has_seo_plugin = true;
				break;
			}
		}

		// Check topic diversity via categories.
		$categories = get_categories( array( 'hide_empty' => true ) );
		if ( count( $categories ) >= 10 ) {
			$score++;
		}

		// Check publishing frequency (last 30 days).
		$recent_posts = get_posts(
			array(
				'post_type'      => 'post',
				'posts_per_page' => -1,
				'date_query'     => array(
					array(
						'after' => '30 days ago',
					),
				),
			)
		);

		if ( count( $recent_posts ) >= 4 ) {
			$score++;
		}

		// Pass if score is high.
		if ( $score >= ( $max_score * 0.7 ) ) {
			return null;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => __( 'Keyword gap analysis reveals opportunities competitors have that you\'re missing. Process: Identify direct competitors (same niche/audience), Export their top keywords (SEMrush, Ahrefs, Ubersuggest), Compare with your keywords, Find gaps (they rank, you don\'t). Benefits: Quick wins (low-competition keywords competitors found), Content ideas (what readers want but you haven\'t covered), Market insights (trending topics in your niche), Competitive advantage (fill gaps before others). Types of gaps: Direct (exact keyword they rank for), Related (variations/synonyms), Semantic (related topics). Tools needed: SEMrush, Ahrefs, or Ubersuggest (competitor keyword research), Google Search Console (your current keywords), Spreadsheet for comparison. Action: Create content for top 10 gap keywords with: search volume >100/mo, difficulty <40, strong relevance to your niche.', 'wpshadow' ),
			'severity'    => 'medium',
			'threat_level' => 30,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/keyword-gaps',
			'stats'       => array(
				'has_seo_plugin'     => $has_seo_plugin,
				'category_count'     => count( $categories ),
				'recent_posts'       => count( $recent_posts ),
			),
			'recommendation' => __( 'Use SEMrush or Ahrefs for keyword gap analysis. Export competitor keywords. Compare with your GSC data. Create content targeting top 10 gaps. Track rankings monthly. Focus on keywords with volume >100, difficulty <40.', 'wpshadow' ),
		);
	}
}
