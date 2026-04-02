<?php
/**
 * XML Sitemap Enabled Diagnostic (Stub)
 *
 * Generated diagnostic stub for post-install hardening checklist item 60.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * XML Sitemap Enabled Diagnostic Class (Stub)
 *
 * TODO: Implement robust, production-safe test logic.
 * TODO: Implement companion treatment after validation.
 * TODO: Add KB article and user-facing remediation guidance.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Xml_Sitemap_Enabled extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'xml-sitemap-enabled';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'XML Sitemap Enabled';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether an XML sitemap is active and accessible to help search engines discover and efficiently index all published content on the site.';

	/**
	 * Gauge family/category for dashboard placement.
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * TODO Test Plan:
	 * Check wp_sitemaps_get_server availability and endpoint response.
	 *
	 * TODO Fix Plan:
	 * Fix by enabling core sitemap or compatible plugin.
	 *
	 * Constraints:
	 * - Must be testable using built-in WordPress functions or PHP checks.
	 * - Must be fixable via hooks/filters/settings/DB/PHP/server setting.
	 * - Must not modify WordPress core files.
	 * - Must improve performance, security, or site success.
	 *
	 * @since  0.6093.1200
	 * @return array|null Return finding array when issue exists, null when healthy.
	 */
	public static function check() {
		// Check if WordPress core sitemap is active (WP 5.5+).
		// The core sitemap can be disabled by the wp_sitemaps_enabled filter.
		$core_sitemap_url = '';
		if ( function_exists( 'wp_sitemaps_get_server' ) ) {
			$server = wp_sitemaps_get_server();
			if ( $server instanceof \WP_Sitemaps && apply_filters( 'wp_sitemaps_enabled', true ) ) {
				// Core sitemap is active.
				return null;
			}
		}

		// Check for known SEO plugins that provide XML sitemaps.
		$sitemap_plugin_options = array(
			'wpseo_xml'              => 'Yoast SEO',
			'rank_math_modules'      => 'Rank Math',
			'seopress_xml_sitemap'   => 'SEOPress',
			'squirrly_seo'           => 'Squirrly SEO',
			'all_in_one_seo_pack'    => 'All in One SEO',
		);

		foreach ( $sitemap_plugin_options as $option => $plugin_name ) {
			if ( false !== get_option( $option, false ) ) {
				return null;
			}
		}

		// Google XML Sitemaps plugin.
		if ( defined( 'GOOGLESITEMAP_VERSION' ) || get_option( 'sm_options', false ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'No XML sitemap was detected. An XML sitemap helps search engines discover and index all pages on your site. WordPress includes a built-in sitemap since version 5.5, or you can use an SEO plugin such as Yoast SEO or Rank Math to generate a more detailed one.', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 35,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/xml-sitemap-enabled',
			'details'      => array(
				'core_sitemap_active' => false,
				'seo_plugin_detected' => false,
			),
		);
	}
}
