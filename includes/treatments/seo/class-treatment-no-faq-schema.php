<?php
/**
 * Treatment: No FAQ Schema
 *
 * Detects FAQ sections without proper schema markup.
 * FAQ schema enables featured snippets and rich results.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.7030.1519
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * No FAQ Schema Treatment Class
 *
 * Checks FAQ content for schema markup.
 *
 * Detection methods:
 * - FAQ section identification (headings with ?)
 * - Schema markup checking
 * - Plugin detection
 *
 * @since 1.7030.1519
 */
class Treatment_No_FAQ_Schema extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-faq-schema';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'No FAQ Schema';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'FAQ sections without schema miss featured snippet opportunities';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'keyword-strategy';

	/**
	 * Run the treatment check.
	 *
	 * Scoring system (4 points):
	 * - 2 points: Schema plugin installed
	 * - 1 point: FAQ content uses schema markup
	 * - 1 point: <20% FAQ posts missing schema
	 *
	 * @since  1.7030.1519
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$score     = 0;
		$max_score = 4;

		// Check for schema plugins.
		$schema_plugins = array(
			'schema-and-structured-data-for-wp/structured-data-for-wp.php',
			'wp-seo-structured-data-schema/wp-seo-structured-data-schema.php',
			'all-in-one-schemaorg-rich-snippets/index.php',
			'schema/schema.php',
		);

		$has_schema_plugin = false;
		foreach ( $schema_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_schema_plugin = true;
				$score += 2;
				break;
			}
		}

		// Check for Yoast/Rank Math (have FAQ blocks).
		if ( ! $has_schema_plugin ) {
			if ( is_plugin_active( 'wordpress-seo/wp-seo.php' ) || is_plugin_active( 'seo-by-rank-math/rank-math.php' ) ) {
				$has_schema_plugin = true;
				$score++;
			}
		}

		// Identify posts with FAQ content.
		$posts = get_posts(
			array(
				'post_type'      => 'post',
				'posts_per_page' => 100,
			)
		);

		$faq_posts = 0;
		$faq_with_schema = 0;

		foreach ( $posts as $post ) {
			$content = $post->post_content;

			// Check for FAQ patterns: headings with question marks.
			$question_count = preg_match_all( '/<h[2-6][^>]*>.*?\?.*?<\/h[2-6]>/i', $content, $matches );

			// Or FAQ keywords.
			$has_faq_keywords = (
				stripos( $content, 'frequently asked' ) !== false ||
				stripos( $content, 'common questions' ) !== false ||
				stripos( $content, 'Q:' ) !== false ||
				stripos( $content, 'Q&A' ) !== false
			);

			if ( $question_count >= 3 || $has_faq_keywords ) {
				$faq_posts++;

				// Check for schema markup.
				$has_schema = (
					stripos( $content, 'FAQPage' ) !== false ||
					stripos( $content, 'wp:yoast-seo/faq-block' ) !== false ||
					stripos( $content, 'rank-math-faq-block' ) !== false ||
					stripos( $content, '"@type":"Question"' ) !== false
				);

				if ( $has_schema ) {
					$faq_with_schema++;
				}
			}
		}

		if ( $faq_posts > 0 ) {
			$schema_percent = ( $faq_with_schema / $faq_posts ) * 100;
			if ( $schema_percent >= 80 ) {
				$score += 2;
			} elseif ( $schema_percent >= 50 ) {
				$score++;
			}
		}

		// Pass if score is high.
		if ( $score >= ( $max_score * 0.75 ) ) {
			return null;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => __( 'FAQ schema markup (FAQPage) enables rich results in Google search. Benefits: Featured snippets (appear above organic results), Expandable Q&A in search results, Voice search optimization (Alexa/Google Home), Higher CTR (rich results stand out), Mobile SERP real estate (takes up more space). Requirements: Minimum 2-3 questions, Questions must be common/frequently asked (not promotional), Answers must be factual (not opinions), Format: Question → Answer pairs. Implementation: Yoast SEO Pro: FAQ block in editor (auto-adds schema), Rank Math: FAQ block (free in Rank Math), Schema Pro plugin: FAQ schema type, Manual JSON-LD code in footer. Best practices: 5-10 questions per page (sweet spot), Front-load with common questions, Keep answers 300-500 characters, Include target keywords naturally, Update based on actual customer questions. Tools: Schema.org FAQPage spec, Google Rich Results Test, Schema Markup Validator. Example questions: "How long does shipping take?", "What\'s your return policy?", "Do you offer bulk discounts?", "Is this suitable for beginners?"', 'wpshadow' ),
			'severity'    => 'medium',
			'threat_level' => 30,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/no-faq-schema',
			'stats'       => array(
				'has_schema_plugin' => $has_schema_plugin,
				'faq_posts'         => $faq_posts,
				'with_schema'       => $faq_with_schema,
				'missing_schema'    => $faq_posts - $faq_with_schema,
			),
			'recommendation' => __( 'Install Yoast SEO Pro or Rank Math. Add FAQ block to posts with Q&A sections. Include 5-10 common questions per post. Test with Google Rich Results Test tool. Update quarterly with new customer questions from support tickets.', 'wpshadow' ),
		);
	}
}
