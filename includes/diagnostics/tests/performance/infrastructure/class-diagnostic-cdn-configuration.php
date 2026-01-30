<?php
/**
 * Diagnostic: CDN Configuration Detection
 *
 * Detects if a CDN (Content Delivery Network) is configured for the site.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_CDN_Configuration
 *
 * Identifies CDN usage by checking HTTP headers and provides recommendations
 * for sites that could benefit from CDN deployment.
 *
 * @since 1.2601.2148
 */
class Diagnostic_CDN_Configuration extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'cdn-configuration';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'CDN Configuration Detection';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Detect if a CDN (Content Delivery Network) is configured for the site';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks for common CDN signatures in HTTP headers and environment.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if no CDN, null if CDN detected.
	 */
	public static function check() {
		$cdn_detected = false;
		$cdn_name = '';

		// Check common CDN headers from $_SERVER
		$cdn_headers = array(
			'HTTP_CF_RAY' => 'Cloudflare',
			'HTTP_X_AKAMAI_TRANSFORMED' => 'Akamai',
			'HTTP_X_CACHE' => 'Various CDN',
			'HTTP_X_CDN' => 'Generic CDN',
			'HTTP_X_EDGE_LOCATION' => 'AWS CloudFront',
			'HTTP_X_FASTLY_REQUEST_ID' => 'Fastly',
			'HTTP_SERVER_TIMING' => 'StackPath/MaxCDN',
		);

		foreach ( $cdn_headers as $header => $cdn ) {
			if ( ! empty( $_SERVER[ $header ] ) ) {
				$cdn_detected = true;
				$cdn_name = $cdn;
				break;
			}
		}

		// Check for CDN plugins
		if ( ! $cdn_detected ) {
			$cdn_plugins = array(
				'w3-total-cache/w3-total-cache.php' => 'W3 Total Cache CDN',
				'wp-super-cache/wp-super-cache.php' => 'WP Super Cache CDN',
				'cloudflare/cloudflare.php' => 'Cloudflare',
				'cdn-enabler/cdn-enabler.php' => 'CDN Enabler',
			);

			if ( ! function_exists( 'is_plugin_active' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			foreach ( $cdn_plugins as $plugin_file => $plugin_name ) {
				if ( is_plugin_active( $plugin_file ) ) {
					$cdn_detected = true;
					$cdn_name = $plugin_name;
					break;
				}
			}
		}

		// Check WP constants for CDN usage
		if ( ! $cdn_detected ) {
			if ( defined( 'WP_CONTENT_URL' ) && defined( 'WP_SITEURL' ) ) {
				$content_url = WP_CONTENT_URL;
				$site_url = WP_SITEURL;
				
				// If content URL is on different domain, likely using CDN
				$content_host = wp_parse_url( $content_url, PHP_URL_HOST );
				$site_host = wp_parse_url( $site_url, PHP_URL_HOST );
				
				if ( $content_host !== $site_host ) {
					$cdn_detected = true;
					$cdn_name = __( 'Custom CDN (different content domain)', 'wpshadow' );
				}
			}
		}

		if ( $cdn_detected ) {
			// CDN is configured - this is good
			return null;
		}

		// No CDN detected - provide recommendation
		$description = __( 'No Content Delivery Network (CDN) detected. A CDN dramatically improves site performance by serving content from locations near your users. CDNs reduce server load, improve page load times (especially for international visitors), and provide redundancy. Popular options include Cloudflare (free tier available), AWS CloudFront, Fastly, and StackPath. Most sites with regular traffic benefit significantly from CDN deployment.', 'wpshadow' );

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => $description,
			'severity'    => 'info',
			'threat_level' => 30,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/infrastructure-cdn-configuration',
			'meta'        => array(
				'cdn_detected' => false,
				'recommendation' => 'Consider implementing CDN for improved performance',
			),
		);
	}
}
