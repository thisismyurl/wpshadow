<?php
/**
 * Diagnostic: Thin Category Pages
 *
 * Detects category pages with only post lists (missed ranking opportunities).
 * Category descriptions boost rankings for category keywords.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.7030.1517
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Thin Category Pages Diagnostic Class
 *
 * Checks category pages for description content.
 *
 * Detection methods:
 * - Category description checking
 * - Description length validation
 * - Category post count
 *
 * @since 1.7030.1517
 */
class Diagnostic_Thin_Category_Pages extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'thin-category-pages';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Thin Category Pages';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Category pages with only post lists = missed ranking opportunities';

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
	 * - 1 point: <20% categories missing descriptions
	 * - 1 point: <40% categories missing descriptions
	 * - 1 point: Avg description length >200 words
	 *
	 * @since  1.7030.1517
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$score     = 0;
		$max_score = 3;

		$categories = get_categories( array( 'hide_empty' => true ) );

		if ( empty( $categories ) ) {
			return null;
		}

		$total_categories = count( $categories );
		$missing_descriptions = 0;
		$total_description_words = 0;
		$categories_with_descriptions = 0;
		$thin_categories = array();

		foreach ( $categories as $category ) {
			$description = $category->description;

			if ( empty( $description ) ) {
				$missing_descriptions++;
				$thin_categories[] = array(
					'name' => $category->name,
					'posts' => $category->count,
				);
			} else {
				$word_count = str_word_count( wp_strip_all_tags( $description ) );
				$total_description_words += $word_count;
				$categories_with_descriptions++;

				if ( $word_count < 100 ) {
					$thin_categories[] = array(
						'name' => $category->name,
						'words' => $word_count,
						'posts' => $category->count,
					);
				}
			}
		}

		$missing_percent = ( $missing_descriptions / $total_categories ) * 100;
		$avg_description_words = $categories_with_descriptions > 0 ? $total_description_words / $categories_with_descriptions : 0;

		if ( $missing_percent < 20 ) {
			$score++;
		}
		if ( $missing_percent < 40 ) {
			$score++;
		}
		if ( $avg_description_words >= 200 ) {
			$score++;
		}

		// Pass if score is high.
		if ( $score >= ( $max_score * 0.67 ) ) {
			return null;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => __( 'Category pages = ranking opportunities for topic keywords. Most sites waste them with just post lists. Opportunity: Category pages can rank for competitive keywords (your site.com/category/email-marketing/ can rank for "email marketing"). Add 300-500 word description at top of category page. Formula: What this category covers (overview), Key topics you\'ll find (specific), Who it\'s for (target audience), Why read these posts (benefit). Structure: H1: Category name, Intro paragraph (100-150 words), Key topics covered (bullet list), Featured posts (hand-picked best 3-5), All posts (standard list). SEO benefits: Internal linking hub (all posts in category link here), Keyword targeting (optimize for category keyword), Freshness (updates when new posts published), User experience (helps visitors find content). Use Yoast SEO to optimize category meta title/description. Tools: Yoast allows category optimization, Category Description Editor plugins, Custom code in category.php template.', 'wpshadow' ),
			'severity'    => 'medium',
			'threat_level' => 30,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/thin-category-pages',
			'stats'       => array(
				'total_categories'      => $total_categories,
				'missing_descriptions'  => $missing_descriptions,
				'missing_percent'       => round( $missing_percent, 1 ),
				'avg_description_words' => round( $avg_description_words, 0 ),
				'thin_categories'       => count( $thin_categories ),
			),
			'recommendation' => __( 'Add 300-500 word descriptions to main categories. Include: What category covers, key topics, target audience, benefit. Optimize category meta titles/descriptions in Yoast. Feature best 3-5 posts manually. Update descriptions quarterly with new content highlights.', 'wpshadow' ),
		);
	}
}
