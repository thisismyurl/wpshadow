<?php
/**
 * Post Excerpt Generation Diagnostic
 *
 * Verifies auto-generated excerpts work correctly. Tests excerpt filters, length,
 * and generation logic for proper functionality.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6030.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post Excerpt Generation Diagnostic Class
 *
 * Checks for issues with auto-generated excerpts.
 *
 * @since 1.6030.2148
 */
class Diagnostic_Post_Excerpt_Generation extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'post-excerpt-generation';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Post Excerpt Generation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies auto-generated excerpts work correctly and handle edge cases';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'posts';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues = array();

		// Check excerpt_length filter modifications.
		$excerpt_length_filters = $GLOBALS['wp_filter']['excerpt_length'] ?? null;
		$custom_excerpt_length = false;
		if ( $excerpt_length_filters && count( $excerpt_length_filters->callbacks ) > 0 ) {
			$custom_excerpt_length = true;
		}

		// Check excerpt_more filter modifications.
		$excerpt_more_filters = $GLOBALS['wp_filter']['excerpt_more'] ?? null;
		$custom_excerpt_more = false;
		if ( $excerpt_more_filters && count( $excerpt_more_filters->callbacks ) > 0 ) {
			$custom_excerpt_more = true;
		}

		// Get posts without manual excerpts.
		$auto_excerpt_posts = $wpdb->get_results(
			"SELECT ID, post_title, post_content, post_excerpt
			FROM {$wpdb->posts}
			WHERE post_status = 'publish'
			AND post_type = 'post'
			AND (post_excerpt = '' OR post_excerpt IS NULL)
			AND post_content != ''
			LIMIT 50",
			ARRAY_A
		);

		if ( empty( $auto_excerpt_posts ) ) {
			return null; // All posts have manual excerpts or no posts.
		}

		// Test excerpt generation for sample posts.
		$generation_failures = 0;
		$empty_excerpts = 0;
		$excessive_length = 0;
		$html_in_excerpts = 0;
		$truncation_issues = 0;

		foreach ( array_slice( $auto_excerpt_posts, 0, 20 ) as $post ) {
			// Test auto-excerpt generation.
			$auto_excerpt = wp_trim_excerpt( '', $post['ID'] );

			if ( empty( $auto_excerpt ) && ! empty( $post['post_content'] ) ) {
				++$empty_excerpts;
				continue;
			}

			// Check length (default is 55 words).
			$word_count = str_word_count( wp_strip_all_tags( $auto_excerpt ) );
			if ( $word_count > 100 ) {
				++$excessive_length;
			}

			// Check for unstripped HTML tags.
			if ( $auto_excerpt !== wp_strip_all_tags( $auto_excerpt ) ) {
				++$html_in_excerpts;
			}

			// Check for truncation issues (broken words).
			if ( preg_match( '/\b\w{15,}\.\.\.$/', $auto_excerpt ) ) {
				++$truncation_issues;
			}

			// Check if generation works at all.
			if ( strlen( $auto_excerpt ) < 10 && strlen( strip_tags( $post['post_content'] ) ) > 100 ) {
				++$generation_failures;
			}
		}

		if ( $empty_excerpts > 5 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts with empty auto-excerpts */
				__( '%d posts generate empty auto-excerpts (generation failure)', 'wpshadow' ),
				$empty_excerpts
			);
		}

		if ( $excessive_length > 5 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts with excessive excerpts */
				__( '%d auto-excerpts exceed 100 words (filter may be broken)', 'wpshadow' ),
				$excessive_length
			);
		}

		if ( $html_in_excerpts > 3 ) {
			$issues[] = sprintf(
				/* translators: %d: number of excerpts with HTML */
				__( '%d auto-excerpts contain HTML tags (stripping not working)', 'wpshadow' ),
				$html_in_excerpts
			);
		}

		if ( $truncation_issues > 3 ) {
			$issues[] = sprintf(
				/* translators: %d: number of excerpts with truncation issues */
				__( '%d auto-excerpts have word truncation issues (broken at boundaries)', 'wpshadow' ),
				$truncation_issues
			);
		}

		// Check for posts with shortcodes in content but not processed in excerpts.
		$shortcode_posts = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->posts}
			WHERE post_status = 'publish'
			AND post_type = 'post'
			AND post_content LIKE '%[%]%'
			AND (post_excerpt = '' OR post_excerpt IS NULL)"
		);

		if ( $shortcode_posts > 10 ) {
			// Test if shortcodes are processed in excerpts.
			$sample_shortcode_post = $wpdb->get_row(
				"SELECT ID, post_content
				FROM {$wpdb->posts}
				WHERE post_status = 'publish'
				AND post_type = 'post'
				AND post_content LIKE '%[%]%'
				AND (post_excerpt = '' OR post_excerpt IS NULL)
				LIMIT 1"
			);

			if ( $sample_shortcode_post ) {
				$excerpt = wp_trim_excerpt( '', $sample_shortcode_post->ID );
				if ( strpos( $excerpt, '[' ) !== false && strpos( $excerpt, ']' ) !== false ) {
					$issues[] = sprintf(
						/* translators: %d: number of posts with unprocessed shortcodes */
						__( '%d posts have shortcodes in auto-excerpts (not processed)', 'wpshadow' ),
						$shortcode_posts
					);
				}
			}
		}

		// Check for posts with only images (no text for excerpts).
		$image_only_posts = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->posts}
			WHERE post_status = 'publish'
			AND post_type = 'post'
			AND post_content LIKE '%<img%'
			AND (post_excerpt = '' OR post_excerpt IS NULL)
			AND LENGTH(TRIM(REGEXP_REPLACE(post_content, '<[^>]+>', ''))) < 50"
		);

		if ( $image_only_posts > 5 ) {
			$issues[] = sprintf(
				/* translators: %d: number of image-only posts */
				__( '%d posts contain mostly images (auto-excerpts will be empty)', 'wpshadow' ),
				$image_only_posts
			);
		}

		// Check for posts with very long content but no manual excerpt.
		$long_posts_no_excerpt = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->posts}
			WHERE post_status = 'publish'
			AND post_type = 'post'
			AND LENGTH(post_content) > 5000
			AND (post_excerpt = '' OR post_excerpt IS NULL)"
		);

		if ( $long_posts_no_excerpt > 10 ) {
			$issues[] = sprintf(
				/* translators: %d: number of long posts without excerpts */
				__( '%d posts over 5000 characters lack manual excerpts (poor summary)', 'wpshadow' ),
				$long_posts_no_excerpt
			);
		}

		// Check if excerpt_length is set to extreme values.
		if ( $custom_excerpt_length ) {
			$test_length = apply_filters( 'excerpt_length', 55 );
			if ( $test_length < 10 ) {
				$issues[] = sprintf(
					/* translators: %d: excerpt length */
					__( 'Excerpt length set to %d words (too short, may truncate)', 'wpshadow' ),
					$test_length
				);
			} elseif ( $test_length > 200 ) {
				$issues[] = sprintf(
					/* translators: %d: excerpt length */
					__( 'Excerpt length set to %d words (excessive, defeats purpose)', 'wpshadow' ),
					$test_length
				);
			}
		}

		// Check if excerpt_more is missing or broken.
		if ( $custom_excerpt_more ) {
			$test_more = apply_filters( 'excerpt_more', ' [&hellip;]' );
			if ( empty( $test_more ) ) {
				$issues[] = __( 'Excerpt "more" text is empty (no truncation indicator)', 'wpshadow' );
			}
		}

		// Check for posts with manual excerpts that are too long.
		$long_manual_excerpts = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->posts}
			WHERE post_status = 'publish'
			AND post_type = 'post'
			AND post_excerpt != ''
			AND LENGTH(post_excerpt) > 500"
		);

		if ( $long_manual_excerpts > 5 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts with long excerpts */
				__( '%d posts have manual excerpts over 500 characters (too long)', 'wpshadow' ),
				$long_manual_excerpts
			);
		}

		// Check for posts with identical content and excerpt.
		$duplicate_excerpts = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->posts}
			WHERE post_status = 'publish'
			AND post_type = 'post'
			AND post_excerpt != ''
			AND post_excerpt = post_content"
		);

		if ( $duplicate_excerpts > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts with duplicate excerpts */
				__( '%d posts have excerpts identical to content (defeats purpose)', 'wpshadow' ),
				$duplicate_excerpts
			);
		}

		// Check if get_the_excerpt filter is breaking things.
		$get_excerpt_filters = $GLOBALS['wp_filter']['get_the_excerpt'] ?? null;
		if ( $get_excerpt_filters && count( $get_excerpt_filters->callbacks ) > 5 ) {
			$issues[] = sprintf(
				/* translators: %d: number of excerpt filters */
				__( '%d filters attached to get_the_excerpt (may cause conflicts)', 'wpshadow' ),
				count( $get_excerpt_filters->callbacks )
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( '. ', $issues ),
				'severity'    => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/post-excerpt-generation',
			);
		}

		return null;
	}
}
