<?php
/**
 * XML Sitemap Configuration Treatment
 *
 * Tests if site properly generates and submits XML sitemaps to search engines.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.7034.1440
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * XML Sitemap Configuration Treatment Class
 *
 * Validates that the site generates XML sitemaps and submits them
 * to search engines for better indexing and discovery.
 *
 * @since 1.7034.1440
 */
class Treatment_XML_Sitemap_Configuration extends Treatment_Base {

	/**
	 * Treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'xml-sitemap-configuration';

	/**
	 * Treatment title
	 *
	 * @var string
	 */
	protected static $title = 'XML Sitemap Configuration';

	/**
	 * Treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if site properly generates and submits XML sitemaps to search engines';

	/**
	 * Treatment family
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the treatment check.
	 *
	 * Tests XML sitemap configuration including generation, index file,
	 * and search engine submission.
	 *
	 * @since  1.7034.1440
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		// Check if WordPress XML sitemap support is enabled.
		$wp_sitemaps_enabled = get_option( 'blog_public' ) === '1';

		// Check for SEO plugin.
		$seo_plugins = array(
			'wordpress-seo/wp-seo.php'                    => array( 'name' => 'Yoast SEO', 'has_sitemap' => true ),
			'all-in-one-seo-pack/all_in_one_seo_pack.php' => array( 'name' => 'All in One SEO', 'has_sitemap' => true ),
			'rank-math-seo/rank-math.php'                 => array( 'name' => 'Rank Math', 'has_sitemap' => true ),
			'wp-seo-structured-data-schema/wp-seo-schema.php' => array( 'name' => 'WP SEO Structured Data', 'has_sitemap' => true ),
			'the-seo-framework/the-seo-framework.php'     => array( 'name' => 'The SEO Framework', 'has_sitemap' => true ),
		);

		$active_seo_plugin = null;
		$seo_plugin_has_sitemap = false;

		foreach ( $seo_plugins as $plugin => $data ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_seo_plugin = $data['name'];
				$seo_plugin_has_sitemap = $data['has_sitemap'];
				break;
			}
		}

		// Check for sitemap files.
		$site_url = get_home_url();
		$has_sitemap_index = false;
		$has_post_sitemap = false;
		$has_page_sitemap = false;

		// Try to fetch sitemap index.
		$response = wp_remote_get( $site_url . '/wp-sitemap.xml', array( 'sslverify' => false ) );
		if ( ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) === 200 ) {
			$has_sitemap_index = true;
			$body = wp_remote_retrieve_body( $response );

			// Check for post and page sitemaps referenced.
			$has_post_sitemap = ( strpos( $body, 'post-sitemap' ) !== false );
			$has_page_sitemap = ( strpos( $body, 'page-sitemap' ) !== false );
		}

		// Check for SEO plugin sitemaps.
		$has_yoast_sitemap = false;
		if ( is_plugin_active( 'wordpress-seo/wp-seo.php' ) ) {
			$yoast_response = wp_remote_get( $site_url . '/sitemap_index.xml', array( 'sslverify' => false ) );
			$has_yoast_sitemap = ! is_wp_error( $yoast_response ) && wp_remote_retrieve_response_code( $yoast_response ) === 200;
		}

		// Check Search Console submission.
		$search_console_connected = get_option( 'gssc_verified_sitemap' ) || get_option( 'aioseo_search_console_verified' );

		// Count published posts and pages.
		global $wpdb;
		$post_count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts}
			 WHERE post_status = 'publish' AND post_type = 'post'"
		);

		$page_count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts}
			 WHERE post_status = 'publish' AND post_type = 'page'"
		);

		// Check robots.txt references sitemap.
		$robots_content = get_option( '_robots_txt' );
		$robots_references_sitemap = ( $robots_content && strpos( $robots_content, 'sitemap' ) !== false );

		// Check for external sitemap submission tools.
		$has_sitemap_submission = false;
		if ( $active_seo_plugin ) {
			if ( $active_seo_plugin === 'Yoast SEO' ) {
				$has_sitemap_submission = get_option( 'wpseo_submits_sitemaps' ) === '1';
			} elseif ( $active_seo_plugin === 'Rank Math' ) {
				$has_sitemap_submission = get_option( 'rank_math_sitemap_submit_search_engines' ) === 'on';
			}
		}

		// Check sitemap freshness.
		$sitemap_age_days = null;
		if ( $has_sitemap_index ) {
			$response = wp_remote_get( $site_url . '/wp-sitemap.xml', array( 'sslverify' => false ) );
			if ( ! is_wp_error( $response ) ) {
				$last_modified = wp_remote_retrieve_header( $response, 'last-modified' );
				if ( $last_modified ) {
					$last_modified_time = strtotime( $last_modified );
					$sitemap_age_days = floor( ( time() - $last_modified_time ) / 86400 );
				}
			}
		}

		// Check for issues.
		$issues = array();

		// Issue 1: No sitemap exists.
		if ( ! $has_sitemap_index && ! $has_yoast_sitemap ) {
			$issues[] = array(
				'type'        => 'no_sitemap',
				'description' => __( 'No XML sitemap found; search engines cannot efficiently discover site content', 'wpshadow' ),
			);
		}

		// Issue 2: WordPress sitemaps enabled but index missing.
		if ( $wp_sitemaps_enabled && ! $has_sitemap_index ) {
			$issues[] = array(
				'type'        => 'sitemaps_disabled',
				'description' => __( 'WordPress XML sitemaps are enabled but not accessible', 'wpshadow' ),
			);
		}

		// Issue 3: Sitemap doesn\'t include all content types.
		if ( $has_sitemap_index && ( ! $has_post_sitemap || ! $has_page_sitemap ) ) {
			$issues[] = array(
				'type'        => 'incomplete_sitemap',
				'description' => __( 'Sitemap exists but missing post or page sitemaps; not all content indexed', 'wpshadow' ),
			);
		}

		// Issue 4: Sitemap not submitted to search engines.
		if ( ( $has_sitemap_index || $has_yoast_sitemap ) && ! $search_console_connected ) {
			$issues[] = array(
				'type'        => 'not_submitted',
				'description' => __( 'Sitemap exists but not submitted to Google Search Console; discovery delayed', 'wpshadow' ),
			);
		}

		// Issue 5: robots.txt doesn\'t reference sitemap.
		if ( ( $has_sitemap_index || $has_yoast_sitemap ) && ! $robots_references_sitemap ) {
			$issues[] = array(
				'type'        => 'robots_no_sitemap',
				'description' => __( 'Sitemap not referenced in robots.txt; search engine crawlers may miss it', 'wpshadow' ),
			);
		}

		// Issue 6: Large site but no SEO plugin.
		if ( $post_count > 100 && ! $active_seo_plugin ) {
			$issues[] = array(
				'type'        => 'large_site_no_plugin',
				'description' => __( 'Site has 100+ posts but no SEO plugin; advanced sitemap features unavailable', 'wpshadow' ),
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'XML sitemap is not properly configured, reducing search engine crawl efficiency and content discovery', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/xml-sitemap-configuration',
				'details'      => array(
					'wp_sitemaps_enabled'     => $wp_sitemaps_enabled,
					'has_sitemap_index'       => $has_sitemap_index,
					'has_post_sitemap'        => $has_post_sitemap,
					'has_page_sitemap'        => $has_page_sitemap,
					'has_yoast_sitemap'       => $has_yoast_sitemap,
					'active_seo_plugin'       => $active_seo_plugin,
					'search_console_connected' => $search_console_connected,
					'post_count'              => absint( $post_count ),
					'page_count'              => absint( $page_count ),
					'robots_references_sitemap' => $robots_references_sitemap,
					'has_sitemap_submission'  => $has_sitemap_submission,
					'sitemap_age_days'        => $sitemap_age_days,
					'issues_detected'         => $issues,
					'recommendation'          => __( 'Enable WordPress sitemaps or install SEO plugin, submit to Google Search Console, reference in robots.txt', 'wpshadow' ),
					'sitemap_urls'            => array(
						'WordPress XML'      => '/wp-sitemap.xml',
						'Yoast SEO'          => '/sitemap_index.xml',
						'Rank Math'          => '/sitemap.xml',
						'Google Search Console' => 'https://search.google.com/search-console',
						'Bing Webmaster'     => 'https://www.bing.com/webmasters',
					),
					'enable_wordpress_sitemaps' => 'Settings > Reading > Discourage search engines = unchecked',
					'robots_txt_example'      => "Sitemap: {$site_url}/wp-sitemap.xml",
					'seo_impact'              => 'Sitemaps improve crawl efficiency by 15-30% for large sites',
					'update_frequency'        => 'Sitemaps should update within 24 hours of content changes',
				),
			);
		}

		return null;
	}
}
