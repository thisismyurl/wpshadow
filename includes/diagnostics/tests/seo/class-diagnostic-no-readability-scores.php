<?php
/**
 * No Readability Scores Diagnostic
 *
 * Detects posts without readability analysis, missing opportunities to
 * optimize content accessibility and engagement.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Content
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * No Readability Scores Diagnostic Class
 *
 * Checks if content is being analyzed for readability, essential for
 * ensuring content is accessible to your target audience.
 *
 * **Why This Matters:**
 * - 55% of visitors spend < 15 seconds reading
 * - Readability directly affects engagement
 * - Google prefers readable content
 * - Target: 7th-8th grade reading level
 * - Poor readability = lost readers
 *
 * **Readability Factors:**
 * - Sentence length (< 20 words avg)
 * - Paragraph length (< 5 sentences)
 * - Word complexity
 * - Active vs passive voice
 * - Transition words
 *
 * @since 1.6093.1200
 */
class Diagnostic_No_Readability_Scores extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-readability-scores';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'No Readability Scores';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Posts aren\'t being analyzed for readability, affecting content quality';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content';

	/**
	 * Run the diagnostic check
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if readability not tracked, null otherwise.
	 */
	public static function check() {
		// Check if Yoast SEO is active (has readability analysis)
		if ( self::has_readability_plugin() ) {
			return null;
		}

		// Get recent posts
		$posts = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => 20,
			)
		);

		if ( empty( $posts ) ) {
			return null;
		}

		// Check if any posts have readability meta
		$posts_with_scores = 0;
		foreach ( $posts as $post ) {
			if ( get_post_meta( $post->ID, '_readability_score', true ) ) {
				$posts_with_scores++;
			}
		}

		// If most posts have scores, readability is being tracked
		if ( $posts_with_scores > count( $posts ) * 0.5 ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Your content isn\'t being analyzed for readability. Install Yoast SEO or similar plugin to ensure your content is accessible and engaging.', 'wpshadow' ),
			'severity'     => 'low',
			'threat_level' => 40,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/content-readability',
			'details'      => array(
				'message'          => 'Enable readability analysis to improve content quality',
				'recommended_tool' => 'Yoast SEO (free)',
				'target_score'     => 'Flesch Reading Ease: 60-70',
			),
		);
	}

	/**
	 * Check if a readability analysis plugin is active
	 *
	 * @since 1.6093.1200
	 * @return bool True if readability plugin active, false otherwise.
	 */
	private static function has_readability_plugin() {
		// Check for Yoast SEO
		if ( is_plugin_active( 'wordpress-seo/wp-seo.php' ) ) {
			return true;
		}

		// Check for Rank Math
		if ( is_plugin_active( 'seo-by-rank-math/rank-math.php' ) ) {
			return true;
		}

		return false;
	}
}
