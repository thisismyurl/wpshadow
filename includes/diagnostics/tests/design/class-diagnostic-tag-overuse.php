<?php
/**
 * Diagnostic: Tag Overuse
 *
 * Detects excessive tags creating thin duplicate pages.
 * 500+ tags creates indexing issues and thin content.
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
 * Tag Overuse Diagnostic Class
 *
 * Checks for tag bloat and thin tag pages.
 *
 * Detection methods:
 * - Total tag count
 * - Tags with single posts
 * - Tag-to-post ratio
 *
 * @since 0.6093.1200
 */
class Diagnostic_Tag_Overuse extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'tag-overuse';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Tag Overuse';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = '500+ tags creates thin duplicate pages hurting SEO';

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
	 * - 1 point: <100 total tags
	 * - 1 point: <30% tags with only 1 post
	 * - 1 point: Tag-to-post ratio <1:5
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$score     = 0;
		$max_score = 3;

		$tags = get_tags( array( 'hide_empty' => false ) );
		$total_tags = count( $tags );

		if ( $total_tags === 0 ) {
			return null;
		}

		// Count tags with only 1 post.
		$single_post_tags = 0;
		foreach ( $tags as $tag ) {
			if ( $tag->count === 1 ) {
				$single_post_tags++;
			}
		}

		// Get total posts.
		$posts_count = wp_count_posts( 'post' );
		$total_posts = $posts_count->publish ?? 0;

		$tag_to_post_ratio = $total_posts > 0 ? $total_tags / $total_posts : 0;
		$single_tag_percent = ( $single_post_tags / $total_tags ) * 100;

		// Scoring.
		if ( $total_tags <= 100 ) {
			$score++;
		}
		if ( $single_tag_percent < 30 ) {
			$score++;
		}
		if ( $tag_to_post_ratio <= 0.2 ) { // 1 tag per 5 posts.
			$score++;
		}

		// Pass if score is high.
		if ( $score >= ( $max_score * 0.67 ) ) {
			return null;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => __( 'Tags should connect related content, NOT be keywords stuffing. Tag overuse problems: Thin content (tag page with 1 post = no value), Duplicate content (similar tags split same posts), Crawl budget waste (Google crawls 500 useless tag pages), Diluted internal linking (too many tag pages compete), Confusing navigation (users overwhelmed by choices). Optimal tag strategy: Maximum 50-100 tags total, Minimum 3-5 posts per tag, General topics (not ultra-specific), Natural groupings (related posts), Clear purpose (helps users/SEO). Tag vs Category difference: Categories = broad chapters (email marketing, social media, SEO), Tags = specific topics within (subject lines, automation, open rates). Example: Good tags: "email automation" (15 posts), "lead generation" (12 posts), "copywriting" (20 posts). Bad tags: "email automation software review 2024" (1 post), "how to write subject lines" (2 posts). Cleanup strategy: Delete tags with <3 posts, Merge similar tags (email-marketing + email_marketing), Noindex tag archives in Yoast if keeping, Use categories for main navigation.', 'wpshadow' ),
			'severity'    => 'medium',
			'threat_level' => 30,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/tag-overuse?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'stats'       => array(
				'total_tags'         => $total_tags,
				'single_post_tags'   => $single_post_tags,
				'single_tag_percent' => round( $single_tag_percent, 1 ),
				'total_posts'        => $total_posts,
				'tag_to_post_ratio'  => round( $tag_to_post_ratio, 2 ),
			),
			'recommendation' => __( 'Audit tags in Posts > Tags. Delete tags with <3 posts. Merge similar tags. Limit to 50-100 tags max. Each tag needs 3+ posts. Use categories (not tags) for main topics. Noindex tag archives if cleanup not feasible.', 'wpshadow' ),
		);
	}
}
