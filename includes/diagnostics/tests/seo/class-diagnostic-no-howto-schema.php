<?php
/**
 * Diagnostic: No HowTo Schema
 *
 * Detects tutorial posts without HowTo schema markup.
 * HowTo schema enables rich results with step-by-step display.
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
 * No HowTo Schema Diagnostic Class
 *
 * Checks tutorial content for HowTo schema.
 *
 * Detection methods:
 * - Tutorial keyword identification
 * - Numbered steps detection
 * - Schema markup checking
 *
 * @since 0.6093.1200
 */
class Diagnostic_No_HowTo_Schema extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-howto-schema';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'No HowTo Schema';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tutorial posts without HowTo schema miss rich results';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'keyword-strategy';

	/**
	 * Run the diagnostic check.
	 *
	 * Scoring system (4 points):
	 * - 2 points: Schema plugin with HowTo support
	 * - 1 point: Tutorial posts use HowTo schema
	 * - 1 point: <20% tutorial posts missing schema
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$score     = 0;
		$max_score = 4;

		// Check for schema plugins with HowTo support.
		$has_howto_support = (
			is_plugin_active( 'wordpress-seo/wp-seo.php' ) || // Yoast SEO Pro has HowTo block.
			is_plugin_active( 'seo-by-rank-math/rank-math.php' ) || // Rank Math has HowTo schema.
			is_plugin_active( 'schema-and-structured-data-for-wp/structured-data-for-wp.php' ) ||
			is_plugin_active( 'wp-seo-structured-data-schema/wp-seo-structured-data-schema.php' )
		);

		if ( $has_howto_support ) {
			$score += 2;
		}

		// Identify tutorial posts.
		$posts = get_posts(
			array(
				'post_type'      => 'post',
				'posts_per_page' => 100,
			)
		);

		$tutorial_keywords = array(
			'how to',
			'tutorial',
			'guide',
			'step by step',
			'instructions',
			'diy',
			'learn to',
		);

		$tutorial_posts = 0;
		$howto_with_schema = 0;

		foreach ( $posts as $post ) {
			$content = strtolower( $post->post_content );
			$title   = strtolower( $post->post_title );

			// Check for tutorial keywords in title or content.
			$is_tutorial = false;
			foreach ( $tutorial_keywords as $keyword ) {
				if ( stripos( $title, $keyword ) !== false ) {
					$is_tutorial = true;
					break;
				}
			}

			// Also check for numbered steps (Step 1, Step 2, etc.).
			$has_steps = preg_match( '/step\s+\d+/i', $content ) || preg_match( '/\d+\.\s+[A-Z]/', $post->post_content );

			if ( $is_tutorial || $has_steps ) {
				$tutorial_posts++;

				// Check for HowTo schema.
				$has_schema = (
					stripos( $post->post_content, 'HowTo' ) !== false ||
					stripos( $post->post_content, 'wp:yoast-seo/how-to-block' ) !== false ||
					stripos( $post->post_content, 'rank-math-howto-block' ) !== false ||
					stripos( $post->post_content, '"@type":"HowToStep"' ) !== false
				);

				if ( $has_schema ) {
					$howto_with_schema++;
				}
			}
		}

		if ( $tutorial_posts > 0 ) {
			$schema_percent = ( $howto_with_schema / $tutorial_posts ) * 100;
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
			'description' => __( 'HowTo schema markup enables rich results with step-by-step visual display in Google. Benefits: Rich results (expanded view in search), Step-by-step carousel (mobile friendly), Image thumbnails for each step, Estimated time/cost display, Voice search optimization, Higher CTR (visual results stand out). Requirements: Clear steps (3+ numbered steps), Each step has action, Optional: time estimate, tools needed, materials. Implementation: Yoast SEO Pro: HowTo block (auto-schema), Rank Math: HowTo block (free), Schema Pro: HowTo schema type, Manual JSON-LD: Add to post footer. Structure: Name (tutorial title), Description (what users will accomplish), Steps (3-20 steps optimal), Step name + description, Optional: Image per step, Optional: Total time, Tools needed, Materials/supplies. Best practices: 3-20 steps (optimal for rich results), Clear action verbs (Click, Enter, Select, Choose), Keep step descriptions 40-100 words, Add images to key steps, Include time estimate if >5 minutes, List tools/materials upfront. Example: Good: "Step 1: Open WordPress Dashboard. Navigate to Plugins > Add New in the left sidebar menu." Bad: "First thing to do. Go to the place where plugins are."', 'wpshadow' ),
			'severity'    => 'medium',
			'threat_level' => 30,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/no-howto-schema?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'stats'       => array(
				'has_howto_support' => $has_howto_support,
				'tutorial_posts'    => $tutorial_posts,
				'with_schema'       => $howto_with_schema,
				'missing_schema'    => $tutorial_posts - $howto_with_schema,
			),
			'recommendation' => __( 'Install Yoast SEO Pro or Rank Math. Add HowTo block to tutorial posts. Structure as: Title, Intro, Tools needed, Steps (3-20), Conclusion. Test with Google Rich Results Test. Add images to key steps for carousel display.', 'wpshadow' ),
		);
	}
}
