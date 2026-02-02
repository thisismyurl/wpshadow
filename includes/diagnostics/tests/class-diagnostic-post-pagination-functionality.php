<?php
/**
 * Post Pagination Functionality Diagnostic
 *
 * Tests if paginated posts (<!--nextpage-->) work correctly. Verifies navigation
 * and multi-page post display.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post Pagination Functionality Diagnostic Class
 *
 * Checks for issues with multi-page post pagination.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Post_Pagination_Functionality extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'post-pagination-functionality';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Post Pagination Functionality';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates multi-page post pagination works correctly with <!--nextpage--> tags';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'posts';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues = array();

		// Find posts with pagination tags.
		$paginated_posts = $wpdb->get_results(
			"SELECT ID, post_title, post_content
			FROM {$wpdb->posts}
			WHERE post_status = 'publish'
			AND post_type IN ('post', 'page')
			AND (
				post_content LIKE '%<!--nextpage-->%'
				OR post_content LIKE '%<!-- nextpage -->%'
				OR post_content LIKE '%<! --nextpage-->%'
			)
			LIMIT 50",
			ARRAY_A
		);

		if ( empty( $paginated_posts ) ) {
			return null; // No paginated posts to check.
		}

		// Check for malformed pagination tags.
		$malformed_tags = 0;
		$excessive_pages = 0;
		$empty_pages = 0;

		foreach ( $paginated_posts as $post ) {
			$content = $post['post_content'];

			// Check for malformed tags (spaces, typos).
			if ( strpos( $content, '<!-- nextpage -->' ) !== false || strpos( $content, '<! --nextpage-->' ) !== false ) {
				++$malformed_tags;
			}

			// Count pages.
			$page_count = substr_count( $content, '<!--nextpage-->' ) + 1;
			
			if ( $page_count > 20 ) {
				++$excessive_pages;
			}

			// Check for empty pages (nextpage tags with no content between).
			$pages = explode( '<!--nextpage-->', $content );
			foreach ( $pages as $page ) {
				$page_text = trim( strip_tags( $page ) );
				if ( strlen( $page_text ) < 10 ) {
					++$empty_pages;
					break;
				}
			}
		}

		if ( $malformed_tags > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts with malformed tags */
				__( '%d posts have malformed nextpage tags (pagination broken)', 'wpshadow' ),
				$malformed_tags
			);
		}

		if ( $excessive_pages > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts with excessive pages */
				__( '%d posts split into 20+ pages (poor user experience)', 'wpshadow' ),
				$excessive_pages
			);
		}

		if ( $empty_pages > 3 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts with empty pages */
				__( '%d posts have empty pagination pages (broken content)', 'wpshadow' ),
				$empty_pages
			);
		}

		// Check if wp_link_pages() is being used in theme.
		$theme_slug = get_stylesheet();
		$theme_dir = get_stylesheet_directory();
		$template_files = array( 'single.php', 'page.php', 'content.php', 'content-single.php', 'content-page.php' );
		
		$has_link_pages = false;
		foreach ( $template_files as $file ) {
			$file_path = $theme_dir . '/' . $file;
			if ( file_exists( $file_path ) ) {
				$file_content = file_get_contents( $file_path );
				if ( strpos( $file_content, 'wp_link_pages' ) !== false ) {
					$has_link_pages = true;
					break;
				}
			}
		}

		if ( ! $has_link_pages && count( $paginated_posts ) > 0 ) {
			$issues[] = __( 'Theme templates missing wp_link_pages() (pagination links not displayed)', 'wpshadow' );
		}

		// Check for posts with pagination but very short content.
		$unnecessary_pagination = 0;
		foreach ( array_slice( $paginated_posts, 0, 20 ) as $post ) {
			$content_length = strlen( strip_tags( $post['post_content'] ) );
			$page_count = substr_count( $post['post_content'], '<!--nextpage-->' ) + 1;
			
			// If total content is less than 1000 chars but split into pages, it's unnecessary.
			if ( $content_length < 1000 && $page_count > 2 ) {
				++$unnecessary_pagination;
			}
		}

		if ( $unnecessary_pagination > 3 ) {
			$issues[] = sprintf(
				/* translators: %d: number of unnecessarily paginated posts */
				__( '%d short posts unnecessarily paginated (under 1000 characters)', 'wpshadow' ),
				$unnecessary_pagination
			);
		}

		// Check if pagination is SEO-friendly (canonical tags).
		$sample_post_id = ! empty( $paginated_posts ) ? (int) $paginated_posts[0]['ID'] : 0;
		
		if ( $sample_post_id > 0 ) {
			$permalink = get_permalink( $sample_post_id );
			$page_2_url = trailingslashit( $permalink ) . '2/';
			
			// Check if pagination URLs are accessible (would need to test, so just warn if structure looks wrong).
			global $wp_rewrite;
			if ( ! $wp_rewrite->using_permalinks() ) {
				$issues[] = __( 'Plain permalinks may break post pagination (use pretty permalinks)', 'wpshadow' );
			}
		}

		// Check for wp_link_pages filter modifications.
		$link_pages_filters = $GLOBALS['wp_filter']['wp_link_pages'] ?? null;
		if ( $link_pages_filters && count( $link_pages_filters->callbacks ) > 3 ) {
			$issues[] = sprintf(
				/* translators: %d: number of filters */
				__( '%d filters on wp_link_pages (may break pagination display)', 'wpshadow' ),
				count( $link_pages_filters->callbacks )
			);
		}

		// Check if posts have nextpage tags in HTML comments (will be stripped).
		$comment_nextpage = 0;
		foreach ( array_slice( $paginated_posts, 0, 20 ) as $post ) {
			if ( preg_match( '/<!--.*<!--nextpage-->.*-->/', $post['post_content'] ) ) {
				++$comment_nextpage;
			}
		}

		if ( $comment_nextpage > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts with nested comments */
				__( '%d posts have nextpage tags inside HTML comments (will not work)', 'wpshadow' ),
				$comment_nextpage
			);
		}

		// Check for posts using both nextpage and more tag.
		$mixed_pagination = 0;
		foreach ( array_slice( $paginated_posts, 0, 20 ) as $post ) {
			if ( strpos( $post['post_content'], '<!--nextpage-->' ) !== false && 
			     strpos( $post['post_content'], '<!--more-->' ) !== false ) {
				++$mixed_pagination;
			}
		}

		if ( $mixed_pagination > 3 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts mixing pagination types */
				__( '%d posts use both nextpage and more tags (confusing navigation)', 'wpshadow' ),
				$mixed_pagination
			);
		}

		// Check for paginated posts with very uneven page lengths.
		$uneven_pagination = 0;
		foreach ( array_slice( $paginated_posts, 0, 20 ) as $post ) {
			$pages = explode( '<!--nextpage-->', $post['post_content'] );
			$page_lengths = array_map( function( $page ) {
				return strlen( strip_tags( $page ) );
			}, $pages );
			
			if ( count( $page_lengths ) > 1 ) {
				$max_length = max( $page_lengths );
				$min_length = min( $page_lengths );
				
				// If one page is 5x longer than another, it's uneven.
				if ( $min_length > 0 && ( $max_length / $min_length ) > 5 ) {
					++$uneven_pagination;
				}
			}
		}

		if ( $uneven_pagination > 3 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts with uneven pages */
				__( '%d posts have very uneven page lengths (poor pagination)', 'wpshadow' ),
				$uneven_pagination
			);
		}

		// Check if page numbers in URLs are properly handled.
		$rewrite_rules = get_option( 'rewrite_rules' );
		$has_pagination_rule = false;
		
		if ( is_array( $rewrite_rules ) ) {
			foreach ( $rewrite_rules as $pattern => $replacement ) {
				if ( strpos( $pattern, '/page/' ) !== false || strpos( $pattern, 'paged=' ) !== false ) {
					$has_pagination_rule = true;
					break;
				}
			}
		}

		if ( ! $has_pagination_rule && count( $paginated_posts ) > 5 ) {
			$issues[] = __( 'Rewrite rules missing pagination patterns (may cause 404 errors)', 'wpshadow' );
		}

		// Check for pagination on post types that don't support it well.
		$paginated_cpts = $wpdb->get_results(
			"SELECT post_type, COUNT(*) as paginated_count
			FROM {$wpdb->posts}
			WHERE post_status = 'publish'
			AND post_type NOT IN ('post', 'page')
			AND post_content LIKE '%<!--nextpage-->%'
			GROUP BY post_type
			HAVING paginated_count > 5",
			ARRAY_A
		);

		if ( ! empty( $paginated_cpts ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of custom post types */
				__( '%d custom post types use pagination (may not be supported)', 'wpshadow' ),
				count( $paginated_cpts )
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( '. ', $issues ),
				'severity'    => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/post-pagination-functionality',
			);
		}

		return null;
	}
}
