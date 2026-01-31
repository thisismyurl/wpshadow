<?php
/**
 * Seo Framework Sitemap Generation Diagnostic
 *
 * Seo Framework Sitemap Generation configuration issues.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.707.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Seo Framework Sitemap Generation Diagnostic Class
 *
 * @since 1.707.0000
 */
class Diagnostic_SeoFrameworkSitemapGeneration extends Diagnostic_Base {

	protected static $slug = 'seo-framework-sitemap-generation';
	protected static $title = 'Seo Framework Sitemap Generation';
	protected static $description = 'Seo Framework Sitemap Generation configuration issues';
	protected static $family = 'functionality';

	public static function check() {
		// Check if SEO Framework is installed
		if ( ! function_exists( 'the_seo_framework' ) ) {
			return null;
		}

		$issues = array();
		$threat_level = 0;

		// Check if sitemap is enabled
		$sitemap_enabled = the_seo_framework()->get_option( 'sitemaps_output' );
		if ( ! $sitemap_enabled ) {
			$issues[] = 'sitemap_disabled';
			$threat_level += 20;
			return $this->build_finding( $issues, $threat_level );
		}

		// Check ping settings
		$ping_google = the_seo_framework()->get_option( 'ping_google' );
		$ping_bing = the_seo_framework()->get_option( 'ping_bing' );
		if ( ! $ping_google || ! $ping_bing ) {
			$issues[] = 'search_engine_ping_disabled';
			$threat_level += 10;
		}

		// Check post types included
		$post_types = the_seo_framework()->get_option( 'sitemaps_post_types' );
		if ( empty( $post_types ) || ! in_array( 'post', $post_types, true ) ) {
			$issues[] = 'posts_not_in_sitemap';
			$threat_level += 15;
		}

		// Check modified timestamp
		$modified_timestamp = the_seo_framework()->get_option( 'sitemaps_modified' );
		if ( ! $modified_timestamp ) {
			$issues[] = 'modified_time_disabled';
			$threat_level += 5;
		}

		// Check priority settings
		$priority = the_seo_framework()->get_option( 'sitemaps_priority' );
		if ( ! $priority ) {
			$issues[] = 'priority_disabled';
			$threat_level += 5;
		}

		// Test sitemap accessibility
		$sitemap_url = home_url( '/sitemap.xml' );
		$response = wp_remote_get( $sitemap_url, array( 'timeout' => 5 ) );
		if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
			$issues[] = 'sitemap_not_accessible';
			$threat_level += 20;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of sitemap issues */
				__( 'SEO Framework sitemap has configuration problems: %s. This prevents search engines from discovering and indexing your content properly.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) )
			);

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => $description,
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/seo-framework-sitemap-generation',
			);
		}
		
		return null;
	}
}
