<?php
/**
 * CDN For Static Assets Diagnostic
 *
 * Serving images, JS, and CSS from a Content Delivery Network reduces
 * Time to First Byte for geographically distant visitors, offloads
 * bandwidth from the origin server, and often improves Core Web Vitals
 * scores. This diagnostic checks whether a CDN is active for static assets.
 *
 * @package ThisIsMyURL\Shadow
 * @subpackage Diagnostics
 * @since 0.6095
 */

declare(strict_types=1);

namespace ThisIsMyURL\Shadow\Diagnostics;

use ThisIsMyURL\Shadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Cdn_For_Static_Assets Class
 *
 * @since 0.6095
 */
class Diagnostic_Cdn_For_Static_Assets extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'cdn-for-static-assets';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'CDN For Static Assets';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether a CDN is configured to serve static assets, which reduces latency for distant visitors and offloads bandwidth from the origin server.';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Confidence level of this diagnostic.
	 *
	 * @var string
	 */
	protected static $confidence = 'standard';

	/**
	 * Active plugin files that provide CDN integration.
	 */
	private const CDN_PLUGINS = array(
		'cloudflare/cloudflare.php'                             => 'Cloudflare',
		'wp-rocket/wp-rocket.php'                               => 'WP Rocket',
		'litespeed-cache/litespeed-cache.php'                   => 'LiteSpeed Cache',
		'w3-total-cache/w3-total-cache.php'                     => 'W3 Total Cache',
		'wp-fastest-cache/wpFastestCache.php'                   => 'WP Fastest Cache',
		'keycdn-tools/keycdn-tools.php'                         => 'KeyCDN Tools',
		'bunnyCDN/bumyCDN.php'                                  => 'BunnyCDN',
		'cdn-enabler/cdn-enabler.php'                           => 'CDN Enabler',
		'staticize-reloaded/staticize-reloaded.php'             => 'Staticize Reloaded',
		'bunnycdn/bunnycdn.php'                                 => 'BunnyCDN',
	);

	/**
	 * Known CDN hostname patterns in media URLs.
	 */
	private const CDN_URL_PATTERNS = array(
		'cdn.cloudflare.net',
		'.b-cdn.net',          // BunnyCDN
		'.stackpathcdn.com',
		'.kxcdn.com',          // KeyCDN
		'.cloudfront.net',     // AWS CloudFront
		'.azureedge.net',      // Azure CDN
		'cdn.statically.io',
		'.imgix.net',
		'.fastly.net',
	);

	/**
	 * Run the diagnostic check.
	 *
	 * Checks three signals: active CDN plugins, the upload_url option
	 * pointing to a non-origin domain, and whether the WordPress content
	 * URL uses a different host from the site URL.
	 *
	 * @since  0.6095
	 * @return array|null Finding array if issue exists, null if healthy.
	 */
	public static function check() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		// 1. Check for active CDN plugins.
		foreach ( array_keys( self::CDN_PLUGINS ) as $plugin_file ) {
			if ( is_plugin_active( $plugin_file ) ) {
				return null;
			}
		}

		// 2. Check if the uploads URL uses a different host from the site URL.
		$site_host    = (string) wp_parse_url( get_site_url(), PHP_URL_HOST );
		$content_host = (string) wp_parse_url( content_url(), PHP_URL_HOST );

		if ( '' !== $content_host && $content_host !== $site_host ) {
			return null; // Assets served from a different origin (custom CDN or pull zone).
		}

		// 3. Check the upload_url_path option for a CDN URL.
		$upload_url_path = (string) get_option( 'upload_url_path', '' );
		if ( '' !== $upload_url_path ) {
			foreach ( self::CDN_URL_PATTERNS as $pattern ) {
				if ( str_contains( $upload_url_path, $pattern ) ) {
					return null;
				}
			}
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'No CDN configuration was detected. Static assets (images, CSS, JS) are being served directly from the origin server. Visitors far from your server will experience higher latency.', 'thisismyurl-shadow' ),
			'severity'     => 'low',
			'threat_level' => 25,
			'details'      => array(
				'fix' => __( 'Enable a CDN for static assets. The easiest options are: (1) Enable Cloudflare\'s free plan for your domain, which proxies all traffic. (2) Use WP Rocket or LiteSpeed Cache with a CDN pull zone from BunnyCDN or KeyCDN. (3) Use CDN Enabler to rewrite asset URLs to a CDN hostname.', 'thisismyurl-shadow' ),
			),
		);
	}
}
