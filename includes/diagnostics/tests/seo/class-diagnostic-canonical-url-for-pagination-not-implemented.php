<?php
/**
 * Canonical URL For Pagination Not Implemented Diagnostic
 *
 * Checks if canonical URLs for pagination are implemented.
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
 * Canonical URL For Pagination Not Implemented Diagnostic Class
 *
 * Detects missing pagination canonical tags.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Canonical_URL_For_Pagination_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'canonical-url-for-pagination-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Canonical URL For Pagination Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if canonical URLs for pagination are implemented';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for SEO plugins that handle canonicals.
		$seo_plugins = array(
			'wordpress-seo/wp-seo.php'                   => 'Yoast SEO',
			'wordpress-seo-premium/wp-seo-premium.php'   => 'Yoast SEO Premium',
			'all-in-one-seo-pack/all_in_one_seo_pack.php' => 'All in One SEO',
			'seo-by-rank-math/rank-math.php'             => 'Rank Math',
			'autodescription/autodescription.php'        => 'The SEO Framework',
		);

		$seo_plugin_detected = false;
		$seo_plugin_name     = '';

		foreach ( $seo_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$seo_plugin_detected = true;
				$seo_plugin_name     = $name;
				break;
			}
		}

		// Check for custom canonical implementation.
		$has_custom_canonical = has_action( 'wp_head', 'rel_canonical' ) || 
		                         has_filter( 'wpseo_canonical' ) ||
		                         has_filter( 'aioseop_canonical_url' );

		// Check if site has paginated content.
		global $wpdb;
		$paginated_posts = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} 
			 WHERE post_status = 'publish' 
			 AND post_type = 'post'
			 AND post_content LIKE '%<!--nextpage-->%'"
		);

		// Check for archive pages with pagination.
		$post_count = wp_count_posts( 'post' );
		$published_posts = $post_count->publish;
		$posts_per_page = get_option( 'posts_per_page', 10 );
		$has_paginated_archives = $published_posts > $posts_per_page;

		// Check if theme supports pagination.
		$has_pagination_support = current_theme_supports( 'html5', 'navigation-widgets' );

		// If no SEO plugin and site has pagination.
		if ( ! $seo_plugin_detected && ! $has_custom_canonical && ( $paginated_posts > 0 || $has_paginated_archives ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Canonical URLs for pagination not implemented. Paginated content may be treated as duplicate by search engines. Install Yoast SEO or Rank Math to automatically add proper canonical tags and rel="next"/rel="prev" links.', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/canonical-pagination',
				'details'     => array(
					'seo_plugin_detected'    => false,
					'paginated_posts'        => (int) $paginated_posts,
					'has_paginated_archives' => $has_paginated_archives,
					'posts_per_page'         => $posts_per_page,
					'total_posts'            => $published_posts,
					'recommendation'         => __( 'Install Yoast SEO (free, 5M+ installs) or Rank Math (free, advanced). Both automatically handle canonical URLs for paginated content.', 'wpshadow' ),
					'pagination_types'       => array(
						'paged_posts' => 'Posts split with <!--nextpage-->',
						'archive_pagination' => 'Blog archives with /page/2/',
						'category_pagination' => 'Category archives with multiple pages',
					),
					'seo_impact'             => array(
						'without_canonical' => 'Page 2, 3, 4 may compete with page 1',
						'with_canonical'    => 'Clear signal which page is primary',
						'google_guidance'   => 'Use rel="canonical" and rel="next"/rel="prev"',
					),
					'example_implementation' => array(
						'page_1' => '<link rel="canonical" href="https://site.com/article/" />',
						'page_2' => '<link rel="canonical" href="https://site.com/article/2/" />',
						'archive' => '<link rel="canonical" href="https://site.com/page/2/" />',
					),
				),
			);
		}

		// No pagination issues.
		if ( $paginated_posts === 0 && ! $has_paginated_archives ) {
			return null; // No pagination exists.
		}

		// No issues - SEO plugin handles it.
		return null;
	}
}
