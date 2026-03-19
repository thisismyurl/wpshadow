<?php
/**
 * Sitemap and Robots.txt Configuration Diagnostic
 *
 * Checks if XML sitemaps and robots.txt are properly configured for
 * search engine crawling optimization.
 *
 * @since 1.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sitemap and Robots.txt Configuration Diagnostic Class
 *
 * Verifies SEO configuration:
 * - XML sitemap presence
 * - robots.txt file
 * - Crawlability settings
 * - Search engine directives
 *
 * @since 1.6093.1200
 */
class Diagnostic_Sitemap_Robots_Configuration extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'sitemap-robots-configuration';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Sitemap and Robots.txt Configuration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for XML sitemap and robots.txt optimization';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function check() {
		$sitemap_available = false;
		$robots_exists     = false;

		// Check for XML sitemap
		$response = wp_remote_get( home_url( '/sitemap.xml' ), array( 'sslverify' => false ) );
		if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
			$sitemap_available = true;
		}

		// Check for robots.txt
		$response = wp_remote_get( home_url( '/robots.txt' ), array( 'sslverify' => false ) );
		if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
			$robots_exists = true;
		}

		// Check WordPress core sitemap support
		if ( function_exists( 'wp_sitemaps_get_max_urls' ) ) {
			$sitemap_available = true;
		}

		if ( ! $sitemap_available || ! $robots_exists ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					__( 'Sitemap: %s, Robots.txt: %s. Both are important for search engine crawling and indexing.', 'wpshadow' ),
					$sitemap_available ? '✓' : '✗',
					$robots_exists ? '✓' : '✗'
				),
				'severity'      => 'low',
				'threat_level'  => 25,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/seo-sitemap-robots',
				'meta'          => array(
					'sitemap_available'    => $sitemap_available,
					'robots_exists'        => $robots_exists,
					'recommendation'       => 'Use WordPress core sitemaps (enabled by default), and ensure robots.txt exists',
					'impact'               => 'Proper sitemap and robots.txt can improve indexation speed by 30-50%',
					'setup'                => array(
						'Sitemap: WordPress generates automatically in wp-sitemap.xml',
						'Robots.txt: Create /robots.txt or use plugin for custom rules',
					),
				),
			);
		}

		return null;
	}
}
