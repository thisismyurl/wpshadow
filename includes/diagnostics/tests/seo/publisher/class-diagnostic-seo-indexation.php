<?php
/**
 * SEO Indexation Diagnostic
 *
 * Checks if Google can index all content pages without issues.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.1300
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SEO Indexation Diagnostic Class
 *
 * Verifies that content pages are indexable by search engines and not
 * blocked by robots.txt, meta tags, or other settings.
 *
 * @since 1.6035.1300
 */
class Diagnostic_SEO_Indexation extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'seo-indexation';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'SEO Indexation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if Google can index all content pages without issues';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'publisher';

	/**
	 * Run the SEO indexation diagnostic check.
	 *
	 * @since  1.6035.1300
	 * @return array|null Finding array if indexation issues detected, null otherwise.
	 */
	public static function check() {
		$issues    = array();
		$warnings  = array();
		$stats     = array();

		// Check if site is set to discourage search engines.
		$discourage_search_engines = get_option( 'blog_public' );
		$stats['blog_public'] = $discourage_search_engines;

		if ( $discourage_search_engines === '0' ) {
			$issues[] = __( 'Site set to discourage search engine indexation', 'wpshadow' );
		}

		// Check robots.txt.
		$robots_txt_path = ABSPATH . 'robots.txt';
		$robots_txt_exists = file_exists( $robots_txt_path );
		$stats['robots_txt_exists'] = $robots_txt_exists;

		if ( $robots_txt_exists ) {
			$robots_content = file_get_contents( $robots_txt_path );
			
			// Check for overly restrictive robots.txt.
			if ( preg_match( '/Disallow: \/$/', $robots_content ) ) {
				$issues[] = __( 'robots.txt blocks entire site from indexation', 'wpshadow' );
			}

			// Check for missing Sitemap directive.
			if ( strpos( $robots_content, 'Sitemap:' ) === false ) {
				$warnings[] = __( 'robots.txt missing Sitemap directive', 'wpshadow' );
			}

			// Check for noindex.
			if ( preg_match( '/Disallow:.*index\.php/', $robots_content ) ) {
				$warnings[] = __( 'robots.txt may be blocking index pages', 'wpshadow' );
			}
		}

		// Check meta robots tags.
		$meta_robots = get_option( 'blog_norobots', '0' );
		if ( $meta_robots === '1' ) {
			$issues[] = __( 'Site has noindex meta tag set globally', 'wpshadow' );
		}

		// Check for noindex on content pages.
		$posts = get_posts( array(
			'posts_per_page' => 10,
			'post_type'      => 'post',
			'post_status'    => 'publish',
		) );

		$noindex_posts = 0;
		foreach ( $posts as $post ) {
			$robots = get_post_meta( $post->ID, '_yoast_wpseo_meta-robots', true );
			if ( $robots && strpos( $robots, 'noindex' ) !== false ) {
				$noindex_posts++;
			}
		}

		$stats['noindex_posts'] = $noindex_posts;

		if ( $noindex_posts > 0 ) {
			$warnings[] = sprintf(
				/* translators: %d: number of posts */
				__( '%d posts have noindex set - they won\'t appear in search results', 'wpshadow' ),
				$noindex_posts
			);
		}

		// Check for canonical URLs.
		$has_canonical = false;
		$theme = wp_get_theme();
		$theme_dir = $theme->get_stylesheet_directory();
		$header_file = $theme_dir . '/header.php';

		if ( file_exists( $header_file ) ) {
			$header_content = file_get_contents( $header_file );
			if ( strpos( $header_content, 'canonical' ) !== false ) {
				$has_canonical = true;
			}
		}

		$stats['canonical_urls'] = $has_canonical;

		if ( ! $has_canonical ) {
			$warnings[] = __( 'No canonical URL tag found - add for duplicate content prevention', 'wpshadow' );
		}

		// Check for XML sitemap.
		$sitemap_url = home_url( '/sitemap.xml' );
		$sitemap_response = wp_remote_head( $sitemap_url, array(
			'sslverify' => false,
		) );

		$has_sitemap = ! is_wp_error( $sitemap_response ) && 
					   wp_remote_retrieve_response_code( $sitemap_response ) === 200;
		
		$stats['xml_sitemap'] = $has_sitemap;

		if ( ! $has_sitemap ) {
			$warnings[] = __( 'XML sitemap not found - create one for better indexation', 'wpshadow' );
		}

		// Check for search engine plugins.
		$seo_plugins = array(
			'wordpress-seo/wp-seo.php',
			'all-in-one-seo-pack/all_in_one_seo_pack.php',
			'rank-math-seo/rank-math-seo.php',
		);

		$has_seo_plugin = false;
		foreach ( $seo_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_seo_plugin = true;
				break;
			}
		}

		$stats['seo_plugin'] = $has_seo_plugin;

		if ( ! $has_seo_plugin ) {
			$warnings[] = __( 'No SEO plugin active - consider one to help with indexation', 'wpshadow' );
		}

		// Check if site is behind authentication.
		if ( is_multisite() && is_subdirectory_install() ) {
			$warnings[] = __( 'Site is multisite subdirectory - ensure main site is public', 'wpshadow' );
		}

		// Check for password protection.
		$password_protected = get_option( 'password_protected_visibility' );
		if ( $password_protected === '1' ) {
			$warnings[] = __( 'Some content may be password-protected - verify indexation settings', 'wpshadow' );
		}

		// Check published content count.
		$post_count = wp_count_posts();
		$total_published = $post_count->publish ?? 0;
		$stats['total_published_posts'] = $total_published;

		if ( $total_published === 0 ) {
			$warnings[] = __( 'No published posts found - nothing to index', 'wpshadow' );
		} elseif ( $total_published > 1000 ) {
			$warnings[] = sprintf(
				/* translators: %d: number */
				__( 'Large number of posts (%d) - ensure sitemap is regularly updated', 'wpshadow' ),
				$total_published
			);
		}

		// If critical issues found.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'SEO indexation has critical issues: ', 'wpshadow' ) . implode( ', ', $issues ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/seo-indexation',
				'context'      => array(
					'stats'    => $stats,
					'issues'   => $issues,
					'warnings' => $warnings,
				),
			);
		}

		// If only warnings.
		if ( ! empty( $warnings ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'SEO indexation has recommendations: ', 'wpshadow' ) . implode( ', ', $warnings ),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/seo-indexation',
				'context'      => array(
					'stats'    => $stats,
					'warnings' => $warnings,
				),
			);
		}

		return null; // SEO indexation is good.
	}
}
