<?php
/**
 * Missing XML Sitemap Configuration Diagnostic
 *
 * Tests for XML sitemap availability.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Diagnostics\Helpers\Diagnostic_Request_Helper;
use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Missing XML Sitemap Configuration Diagnostic Class
 *
 * Tests for XML sitemap availability.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Missing_XML_Sitemap_Configuration extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'missing-xml-sitemap-configuration';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Missing XML Sitemap Configuration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests for XML sitemap availability';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if WordPress has XML sitemap support (5.5+).
		if ( ! function_exists( 'wp_sitemaps_get_sitemaps' ) ) {
			$issues[] = __( 'XML Sitemap support not available (requires WordPress 5.5+)', 'wpshadow' );
		}

		// Check if sitemap is accessible.
		$sitemap_url = home_url( 'wp-sitemap.xml' );
		$sitemap_status = Diagnostic_Request_Helper::head_response_code( $sitemap_url, array( 'timeout' => 3 ) );

		if ( null === $sitemap_status ) {
			$issues[] = __( 'XML Sitemap not accessible', 'wpshadow' );
		} elseif ( 200 !== $sitemap_status ) {
			$issues[] = sprintf(
				/* translators: %d: HTTP status code */
				__( 'XML Sitemap returns HTTP %d instead of 200', 'wpshadow' ),
				$sitemap_status
			);
		}

		// Check if SEO plugins have sitemaps.
		$yoast_active = is_plugin_active( 'wordpress-seo/wp-seo.php' );
		$rank_math_active = is_plugin_active( 'rank-math/rank-math.php' );
		$aioseo_active = is_plugin_active( 'all-in-one-seo/all_in_one_seo.php' );

		if ( $yoast_active || $rank_math_active || $aioseo_active ) {
			// SEO plugin provides sitemaps.
		} elseif ( ! function_exists( 'wp_sitemaps_get_sitemaps' ) ) {
			$issues[] = __( 'No XML Sitemap available - install SEO plugin or update WordPress', 'wpshadow' );
		}

		// Check if robots.txt mentions sitemap.
		$robots_url = home_url( 'robots.txt' );
		$robots_response = wp_remote_get( $robots_url, array( 'timeout' => 3 ) );

		if ( ! is_wp_error( $robots_response ) ) {
			$robots_content = wp_remote_retrieve_body( $robots_response );

			if ( strpos( $robots_content, 'sitemap' ) === false ) {
				$issues[] = __( 'robots.txt does not reference XML Sitemap', 'wpshadow' );
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/missing-xml-sitemap-configuration',
			);
		}

		return null;
	}
}
