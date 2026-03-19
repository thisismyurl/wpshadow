<?php
/**
 * Meta Robots and Canonical Tags Diagnostic
 *
 * Tests if pages have proper robots directives and canonical URLs set.
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
 * Meta Robots and Canonical Tags Diagnostic Class
 *
 * Validates that pages have proper robots meta tags and canonical URLs
 * to prevent duplicate content and guide search engine crawling.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Meta_Robots_Canonical_Tags extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'meta-robots-canonical-tags';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Meta Robots and Canonical Tags';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if pages have proper robots directives and canonical URLs set';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * Tests meta robots tags and canonical URLs including duplicate
	 * content detection and crawler directives.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		// Check for SEO plugins that add canonical tags.
		$seo_plugins = array(
			'wordpress-seo/wp-seo.php'                    => 'Yoast SEO',
			'all-in-one-seo-pack/all_in_one_seo_pack.php' => 'All in One SEO',
			'rank-math-seo/rank-math.php'                 => 'Rank Math',
			'the-seo-framework/the-seo-framework.php'     => 'The SEO Framework',
		);

		$active_seo_plugin = null;
		foreach ( $seo_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_seo_plugin = $name;
				break;
			}
		}

		// Check if wp_head hook is used in theme.
		$theme_dir = get_template_directory();
		$header_file = $theme_dir . '/header.php';
		$has_wp_head = false;

		if ( file_exists( $header_file ) ) {
			$header_content = file_get_contents( $header_file );
			$has_wp_head = ( strpos( $header_content, 'wp_head()' ) !== false );
		}

		// Check recent posts for canonical tags.
		global $wpdb;
		$posts_without_canonical = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} p
			 LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_yoast_wpseo_canonical'
			 WHERE p.post_status = 'publish'
			 AND pm.meta_id IS NULL
			 LIMIT 20"
		);

		// Check for canonical URL consistency.
		$site_url = get_home_url();
		$ssl_enabled = is_ssl();

		// Check for URL trailing slashes inconsistency.
		$posts = $wpdb->get_results(
			"SELECT ID, guid, post_name FROM {$wpdb->posts}
			 WHERE post_status = 'publish' AND post_type IN ('post', 'page')
			 ORDER BY post_date DESC LIMIT 10",
			ARRAY_A
		);

		$trailing_slash_inconsistent = false;
		$has_slash_posts = 0;
		$no_slash_posts = 0;

		foreach ( $posts as $post ) {
			if ( substr( $post['guid'], -1 ) === '/' ) {
				$has_slash_posts++;
			} else {
				$no_slash_posts++;
			}
		}

		if ( $has_slash_posts > 0 && $no_slash_posts > 0 ) {
			$trailing_slash_inconsistent = true;
		}

		// Check for duplicate content issues (archives enabled).
		$posts_per_page = get_option( 'posts_per_page' );
		$blog_page_id = get_option( 'page_for_posts' );
		$archives_disabled = false; // WordPress has archives by default.

		// Check for robots.txt noindex settings.
		$search_engines_discouraged = get_option( 'blog_public' ) !== '1';

		// Check for noindex on archive pages.
		$noindex_archive = get_option( 'aioseo_noindex_archive' ) === '1' ||
						 get_option( 'wpseo_noindex_archive' ) === '1';

		// Check for multiple canonical issues in header.
		$header_canonical_issues = 0;
		if ( file_exists( $header_file ) ) {
			$header_content = file_get_contents( $header_file );
			preg_match_all( '/rel=["\']canonical["\']/', $header_content, $matches );
			if ( count( $matches[0] ) > 1 ) {
				$header_canonical_issues = count( $matches[0] );
			}
		}

		// Check for paginated page canonical handling.
		$pagination_posts = $wpdb->get_results(
			"SELECT ID FROM {$wpdb->posts}
			 WHERE post_status = 'publish' AND post_type = 'post'
			 AND post_content LIKE '%<!--nextpage-->%'
			 LIMIT 5",
			ARRAY_A
		);

		$paginated_post_count = count( $pagination_posts );

		// Check for issues.
		$issues = array();

		// Issue 1: No SEO plugin for canonical tag management.
		if ( ! $active_seo_plugin ) {
			$issues[] = array(
				'type'        => 'no_seo_plugin',
				'description' => __( 'No SEO plugin installed; canonical tags may not be properly managed', 'wpshadow' ),
			);
		}

		// Issue 2: wp_head() not called in header.
		if ( ! $has_wp_head ) {
			$issues[] = array(
				'type'        => 'no_wp_head',
				'description' => __( 'wp_head() not called in header.php; canonical tags and meta robots not rendered', 'wpshadow' ),
			);
		}

		// Issue 3: Trailing slash inconsistent.
		if ( $trailing_slash_inconsistent ) {
			$issues[] = array(
				'type'        => 'inconsistent_trailing_slashes',
				'description' => __( 'URLs inconsistently use trailing slashes; creates duplicate content issues', 'wpshadow' ),
			);
		}

		// Issue 4: Multiple canonical tags on same page.
		if ( $header_canonical_issues > 1 ) {
			$issues[] = array(
				'type'        => 'multiple_canonical_tags',
				'description' => sprintf(
					/* translators: %d: number of canonical tags */
					__( '%d canonical tags found on page; only one should exist', 'wpshadow' ),
					$header_canonical_issues
				),
			);
		}

		// Issue 5: Paginated content without proper canonical.
		if ( $paginated_post_count > 0 && ! $active_seo_plugin ) {
			$issues[] = array(
				'type'        => 'paginated_no_plugin',
				'description' => sprintf(
					/* translators: %d: number of paginated posts */
					__( '%d posts have multiple pages but no SEO plugin to handle pagination canonical', 'wpshadow' ),
					$paginated_post_count
				),
			);
		}

		// Issue 6: Search engines discouraged globally.
		if ( $search_engines_discouraged ) {
			$issues[] = array(
				'type'        => 'search_discouraged',
				'description' => __( 'Search engines are globally discouraged; site will not be indexed', 'wpshadow' ),
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Meta robots directives and canonical tags are not properly configured, causing duplicate content and search engine confusion', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/meta-robots-canonical-tags',
				'details'      => array(
					'active_seo_plugin'       => $active_seo_plugin,
					'has_wp_head'             => $has_wp_head,
					'posts_without_canonical' => absint( $posts_without_canonical ),
					'trailing_slash_inconsistent' => $trailing_slash_inconsistent,
					'has_slash_posts'         => $has_slash_posts,
					'no_slash_posts'          => $no_slash_posts,
					'search_discouraged'      => $search_engines_discouraged,
					'noindex_archive'         => $noindex_archive,
					'header_canonical_issues' => $header_canonical_issues,
					'paginated_post_count'    => $paginated_post_count,
					'issues_detected'         => $issues,
					'recommendation'          => __( 'Install SEO plugin, ensure wp_head() call, standardize URL format, verify single canonical per page', 'wpshadow' ),
					'canonical_tag_format'    => '<link rel="canonical" href="' . $site_url . '/your-post/" />',
					'meta_robots_examples'    => array(
						'index, follow'       => 'Default - index page and follow links',
						'noindex, follow'     => 'Hide page from search, follow links',
						'index, nofollow'     => 'Index page, don\'t follow links',
						'noindex, nofollow'   => 'Hide page, don\'t pass link value',
					),
					'duplicate_content_prevention' => array(
						'Canonical tags'      => 'Point to preferred version',
						'Trailing slash consistency' => 'Use either all slashes or none',
						'WWW consistency'     => 'Use either www or non-www consistently',
						'URL parameters'      => 'Canonical to clean URL without session IDs',
						'Protocol consistency' => 'Use HTTPS everywhere',
					),
					'pagination_handling'     => array(
						'Rel=next/prev'       => 'Alternative to canonical for series',
						'Canonical first page' => 'Page 1 canonical to itself',
						'Page parameters'     => 'Use ?page=2 consistently',
					),
					'search_impact'           => 'Canonical tags are critical for SEO; duplicate content can cause 30-50% ranking loss',
				),
			);
		}

		return null;
	}
}
