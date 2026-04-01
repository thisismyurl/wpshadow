<?php
/**
 * Post Read More Tag Diagnostic
 *
 * Checks if <!--more--> tag works correctly. Tests excerpt vs full content
 * display on archive pages.
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
 * Post Read More Tag Diagnostic Class
 *
 * Checks for issues with the <!--more--> tag functionality.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Post_Read_More_Tag extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'post-read-more-tag';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Post Read More Tag';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates <!--more--> tag functionality and excerpt display';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'posts';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues = array();

		// Find posts with <!--more--> tags.
		$posts_with_more = $wpdb->get_results(
			"SELECT ID, post_title, post_content
			FROM {$wpdb->posts}
			WHERE post_status = 'publish'
			AND post_type = 'post'
			AND post_content LIKE '%<!--more-->%'
			LIMIT 50",
			ARRAY_A
		);

		if ( empty( $posts_with_more ) ) {
			return null; // No more tags to check.
		}

		// Check for malformed more tags.
		$malformed_tags = 0;
		$more_at_start = 0;
		$multiple_more_tags = 0;
		$more_with_custom_text = 0;

		foreach ( $posts_with_more as $post ) {
			$content = $post['post_content'];

			// Check for malformed tags (spaces).
			if ( strpos( $content, '<!-- more -->' ) !== false || strpos( $content, '<! --more-->' ) !== false ) {
				++$malformed_tags;
			}

			// Check if more tag is at the very start.
			if ( strpos( trim( $content ), '<!--more-->' ) === 0 ) {
				++$more_at_start;
			}

			// Check for multiple more tags in same post.
			if ( substr_count( $content, '<!--more-->' ) > 1 ) {
				++$multiple_more_tags;
			}

			// Check for custom more text.
			if ( preg_match( '/<!--more\s+(.+?)-->/', $content ) ) {
				++$more_with_custom_text;
			}
		}

		if ( $malformed_tags > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts with malformed tags */
				__( '%d posts have malformed more tags (spaces, will not work)', 'wpshadow' ),
				$malformed_tags
			);
		}

		if ( $more_at_start > 3 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts with more at start */
				__( '%d posts have more tag at beginning (no excerpt content)', 'wpshadow' ),
				$more_at_start
			);
		}

		if ( $multiple_more_tags > 5 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts with multiple tags */
				__( '%d posts have multiple more tags (only first will work)', 'wpshadow' ),
				$multiple_more_tags
			);
		}

		// Check if theme displays excerpts properly.
		$theme_slug = get_stylesheet();
		$theme_dir = get_stylesheet_directory();
		$archive_files = array( 'archive.php', 'index.php', 'content.php', 'content-archive.php' );

		$has_the_content = false;
		foreach ( $archive_files as $file ) {
			$file_path = $theme_dir . '/' . $file;
			if ( file_exists( $file_path ) ) {
				$file_content = file_get_contents( $file_path );
				if ( strpos( $file_content, 'the_content(' ) !== false ) {
					$has_the_content = true;
					break;
				}
			}
		}

		if ( ! $has_the_content && count( $posts_with_more ) > 10 ) {
			$issues[] = __( 'Theme archive templates may not display more tag content (check theme)', 'wpshadow' );
		}

		// Check for the_content_more_link filter.
		$more_link_filters = $GLOBALS['wp_filter']['the_content_more_link'] ?? null;
		if ( $more_link_filters && count( $more_link_filters->callbacks ) > 3 ) {
			$issues[] = sprintf(
				/* translators: %d: number of filters */
				__( '%d filters on the_content_more_link (may break read more links)', 'wpshadow' ),
				count( $more_link_filters->callbacks )
			);
		}

		// Check for excerpt_more filter.
		$excerpt_more_filters = $GLOBALS['wp_filter']['excerpt_more'] ?? null;
		if ( $excerpt_more_filters ) {
			// This is fine, just checking it exists.
			if ( count( $excerpt_more_filters->callbacks ) > 5 ) {
				$issues[] = sprintf(
					/* translators: %d: number of filters */
					__( '%d filters on excerpt_more (verify read more text)', 'wpshadow' ),
					count( $excerpt_more_filters->callbacks )
				);
			}
		}

		// Check for posts using both more tag and manual excerpt.
		$posts_with_both = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->posts}
			WHERE post_status = 'publish'
			AND post_type = 'post'
			AND post_content LIKE '%<!--more-->%'
			AND post_excerpt != ''"
		);

		if ( $posts_with_both > 10 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts with both */
				__( '%d posts have both more tag and manual excerpt (redundant)', 'wpshadow' ),
				$posts_with_both
			);
		}

		// Check for very short content before more tag.
		$short_teaser = 0;
		foreach ( array_slice( $posts_with_more, 0, 30 ) as $post ) {
			$parts = explode( '<!--more-->', $post['post_content'], 2 );
			if ( isset( $parts[0] ) ) {
				$teaser_length = strlen( strip_tags( $parts[0] ) );
				if ( $teaser_length < 50 ) {
					++$short_teaser;
				}
			}
		}

		if ( $short_teaser > 5 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts with short teasers */
				__( '%d posts have very short teaser text (under 50 characters)', 'wpshadow' ),
				$short_teaser
			);
		}

		// Check for posts with more tag but no content after it.
		$empty_after_more = 0;
		foreach ( array_slice( $posts_with_more, 0, 30 ) as $post ) {
			$parts = explode( '<!--more-->', $post['post_content'], 2 );
			if ( isset( $parts[1] ) ) {
				$after_content = trim( strip_tags( $parts[1] ) );
				if ( strlen( $after_content ) < 20 ) {
					++$empty_after_more;
				}
			}
		}

		if ( $empty_after_more > 3 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts with little content after */
				__( '%d posts have little content after more tag (pointless tag)', 'wpshadow' ),
				$empty_after_more
			);
		}

		// Check if more link is customized globally.
		$more_link_text = get_option( 'more_link_text' );
		if ( empty( $more_link_text ) ) {
			// Default is fine.
		} elseif ( strlen( $more_link_text ) > 50 ) {
			$issues[] = __( 'Read more link text is very long (consider shortening)', 'wpshadow' );
		}

		// Check for posts mixing more tag and nextpage tag.
		$mixed_tags = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->posts}
			WHERE post_status = 'publish'
			AND post_content LIKE '%<!--more-->%'
			AND post_content LIKE '%<!--nextpage-->%'"
		);

		if ( $mixed_tags > 5 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts mixing tags */
				__( '%d posts use both more and nextpage tags (confusing navigation)', 'wpshadow' ),
				$mixed_tags
			);
		}

		// Check if get_the_content is filtered properly.
		$content_filters = $GLOBALS['wp_filter']['get_the_content'] ?? null;
		if ( $content_filters && count( $content_filters->callbacks ) > 5 ) {
			$issues[] = sprintf(
				/* translators: %d: number of filters */
				__( '%d filters on get_the_content (may affect more tag behavior)', 'wpshadow' ),
				count( $content_filters->callbacks )
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( '. ', $issues ),
				'severity'    => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/post-read-more-tag?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
