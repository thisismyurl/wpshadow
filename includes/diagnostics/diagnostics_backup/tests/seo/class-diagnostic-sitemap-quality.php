<?php
/**
 * Sitemap Quality Check Diagnostic
 *
 * Verifies XML sitemap exists, is valid, and includes
 * all public content for search engine indexing.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5029.1045
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sitemap Quality Check Class
 *
 * Validates sitemap presence, structure, and completeness.
 * Search engines rely on sitemaps for efficient crawling.
 *
 * @since 1.5029.1045
 */
class Diagnostic_Sitemap_Quality extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'sitemap-quality';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Sitemap Quality Check';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates XML sitemap quality and completeness';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * Fetches and analyzes sitemap.xml using wp_remote_get().
	 * Validates XML structure and content coverage.
	 *
	 * @since  1.5029.1045
	 * @return array|null Finding array if sitemap issues found, null otherwise.
	 */
	public static function check() {
		$cache_key = 'wpshadow_sitemap_quality_check';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$issues = array();

		// Try common sitemap URLs.
		$sitemap_urls = array(
			home_url( '/sitemap.xml' ),
			home_url( '/sitemap_index.xml' ),
			home_url( '/wp-sitemap.xml' ),
		);

		$sitemap_found = false;
		$sitemap_content = '';
		$sitemap_url = '';

		foreach ( $sitemap_urls as $url ) {
			$response = wp_remote_get( $url, array( 'timeout' => 10 ) );

			if ( ! is_wp_error( $response ) ) {
				$code = wp_remote_retrieve_response_code( $response );
				if ( 200 === $code ) {
					$sitemap_found = true;
					$sitemap_content = wp_remote_retrieve_body( $response );
					$sitemap_url = $url;
					break;
				}
			}
		}

		if ( ! $sitemap_found ) {
			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'No XML sitemap found. Search engines may have difficulty discovering your content.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/seo-sitemap-quality',
				'data'         => array(
					'sitemap_found' => false,
					'tried_urls'    => $sitemap_urls,
				),
			);

			set_transient( $cache_key, $result, 24 * HOUR_IN_SECONDS );
			return $result;
		}

		// Validate XML structure.
		libxml_use_internal_errors( true );
		$xml = simplexml_load_string( $sitemap_content );
		$xml_errors = libxml_get_errors();
		libxml_clear_errors();

		if ( false === $xml || ! empty( $xml_errors ) ) {
			$issues[] = __( 'Sitemap has invalid XML structure', 'wpshadow' );
		}

		// Count URLs in sitemap.
		$url_count = 0;
		if ( $xml ) {
			// Check if it's a sitemap index or regular sitemap.
			if ( isset( $xml->sitemap ) ) {
				$url_count = count( $xml->sitemap );
			} elseif ( isset( $xml->url ) ) {
				$url_count = count( $xml->url );
			}
		}

		// Compare with published post count using WordPress API (NO $wpdb).
		$published_posts = wp_count_posts( 'post' );
		$published_pages = wp_count_posts( 'page' );
		$total_published = ( $published_posts->publish ?? 0 ) + ( $published_pages->publish ?? 0 );

		if ( $url_count < ( $total_published * 0.8 ) ) {
			$issues[] = sprintf(
				/* translators: 1: sitemap URLs, 2: published content */
				__( 'Sitemap appears incomplete (%1$d URLs vs %2$d published items)', 'wpshadow' ),
				$url_count,
				$total_published
			);
		}

		// Check for sitemap plugins.
		$has_yoast = is_plugin_active( 'wordpress-seo/wp-seo.php' );
		$has_rank_math = is_plugin_active( 'seo-by-rank-math/rank-math.php' );
		$has_sitemap_plugin = $has_yoast || $has_rank_math || is_plugin_active( 'google-sitemap-generator/sitemap.php' );

		if ( ! $has_sitemap_plugin && function_exists( 'wp_sitemaps_get_server' ) ) {
			// WordPress 5.5+ has native sitemaps.
			$has_sitemap_plugin = true;
		}

		if ( ! $has_sitemap_plugin && $url_count === 0 ) {
			$issues[] = __( 'No sitemap plugin detected and sitemap appears empty', 'wpshadow' );
		}

		// If issues found, flag it.
		if ( ! empty( $issues ) ) {
			$threat_level = 35;
			if ( count( $issues ) >= 2 ) {
				$threat_level = 45;
			}

			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of issues */
					__( 'Sitemap has %d quality issues affecting search engine crawling.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'medium',
				'threat_level' => $threat_level,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/seo-sitemap-quality',
				'data'         => array(
					'sitemap_url'        => $sitemap_url,
					'url_count'          => $url_count,
					'published_count'    => $total_published,
					'has_sitemap_plugin' => $has_sitemap_plugin,
					'issues'             => $issues,
				),
			);

			set_transient( $cache_key, $result, 24 * HOUR_IN_SECONDS );
			return $result;
		}

		set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
		return null;
	}
}
