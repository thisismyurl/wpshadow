<?php
/**
 * Post Excerpt Truncation Diagnostic
 *
 * Verifies post excerpts aren't being truncated incorrectly.
 * Checks excerpt length limits and encoding.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Tests
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post Excerpt Truncation Diagnostic Class
 *
 * Checks for issues with post excerpt generation and truncation
 * that may cause content loss or display problems.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Post_Excerpt_Truncation extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'post-excerpt-truncation';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Post Excerpt Truncation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies post excerpts are not being truncated incorrectly';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'posts';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues = array();

		// Check for posts with very long custom excerpts (might exceed limits).
		$long_excerpts = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->posts}
			WHERE post_status = 'publish'
			AND post_type IN ('post', 'page')
			AND LENGTH(post_excerpt) > 1000"
		);

		if ( $long_excerpts > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts */
				__( '%d posts have very long custom excerpts (>1000 chars)', 'wpshadow' ),
				$long_excerpts
			);
		}

		// Check excerpt_length filter (default is 55 words).
		$excerpt_length = apply_filters( 'excerpt_length', 55 );
		if ( $excerpt_length < 20 ) {
			$issues[] = sprintf(
				/* translators: %d: word count */
				__( 'Excerpt length set very short (%d words)', 'wpshadow' ),
				$excerpt_length
			);
		} elseif ( $excerpt_length > 200 ) {
			$issues[] = sprintf(
				/* translators: %d: word count */
				__( 'Excerpt length set very long (%d words)', 'wpshadow' ),
				$excerpt_length
			);
		}

		// Check for posts with empty content but custom excerpt (might be truncated).
		$empty_content_with_excerpt = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->posts}
			WHERE post_status = 'publish'
			AND post_type IN ('post', 'page')
			AND (post_content = '' OR post_content IS NULL)
			AND post_excerpt != ''"
		);

		if ( $empty_content_with_excerpt > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts */
				__( '%d posts have excerpts but no content', 'wpshadow' ),
				$empty_content_with_excerpt
			);
		}

		// Check for multibyte character issues in excerpts.
		$posts_with_excerpts = $wpdb->get_results(
			"SELECT ID, post_excerpt
			FROM {$wpdb->posts}
			WHERE post_status = 'publish'
			AND post_type IN ('post', 'page')
			AND post_excerpt != ''
			LIMIT 100"
		);

		$encoding_issues = 0;
		foreach ( $posts_with_excerpts as $post ) {
			// Check if excerpt is valid UTF-8.
			if ( ! mb_check_encoding( $post->post_excerpt, 'UTF-8' ) ) {
				++$encoding_issues;
			}
		}

		if ( $encoding_issues > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts */
				__( '%d post excerpts have encoding issues', 'wpshadow' ),
				$encoding_issues
			);
		}

		// Check for excerpts with broken HTML tags (from truncation).
		$broken_html_excerpts = 0;
		foreach ( $posts_with_excerpts as $post ) {
			if ( preg_match( '/<[^>]*$/', $post->post_excerpt ) ) {
				++$broken_html_excerpts;
			}
		}

		if ( $broken_html_excerpts > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts */
				__( '%d excerpts contain broken HTML tags', 'wpshadow' ),
				$broken_html_excerpts
			);
		}

		// Check excerpt_more filter.
		$excerpt_more = apply_filters( 'excerpt_more', ' [&hellip;]' );
		if ( empty( $excerpt_more ) ) {
			$issues[] = __( 'Excerpt "more" indicator is empty (users won\'t know text is truncated)', 'wpshadow' );
		}

		// Check for posts where auto-generated excerpt might be poor quality.
		$posts_without_excerpt = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->posts}
			WHERE post_status = 'publish'
			AND post_type = 'post'
			AND (post_excerpt = '' OR post_excerpt IS NULL)
			AND LENGTH(post_content) < 100"
		);

		if ( $posts_without_excerpt > 10 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts */
				__( '%d posts have very short content and no custom excerpt', 'wpshadow' ),
				$posts_without_excerpt
			);
		}

		// Check for posts with shortcodes in excerpt that might not render.
		$shortcode_in_excerpt = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->posts}
			WHERE post_status = 'publish'
			AND post_type IN ('post', 'page')
			AND post_excerpt REGEXP '\\[.+\\]'"
		);

		if ( $shortcode_in_excerpt > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts */
				__( '%d excerpts contain shortcodes (may not display properly)', 'wpshadow' ),
				$shortcode_in_excerpt
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/post-excerpt-truncation',
			);
		}

		return null;
	}
}
