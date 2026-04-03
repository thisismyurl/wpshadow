<?php
/**
 * Compression Enabled Diagnostic
 *
 * Checks whether HTTP compression (gzip or brotli) is active by inspecting
 * the Content-Encoding header returned for the site homepage.
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
 * Compression Enabled Diagnostic Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_Compression_Enabled extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'compression-enabled';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Compression Enabled';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'HTTP compression (gzip/brotli) does not appear to be active. Enabling compression can reduce page transfer sizes by up to 70%.';

	/**
	 * Gauge family/category for dashboard placement.
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Whether this diagnostic is part of the core trusted set.
	 *
	 * @var bool
	 */
	protected static $is_core = true;

	/**
	 * Confidence level of this diagnostic.
	 *
	 * @var string
	 */
	protected static $confidence = 'high';

	/**
	 * Run the diagnostic check.
	 *
	 * Makes an HTTP request to the homepage with an Accept-Encoding header and
	 * inspects the Content-Encoding response header for gzip or br.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array when compression is absent, null when healthy.
	 */
	public static function check() {
		$home_url = home_url( '/' );
		$response = wp_remote_get( $home_url, array(
			'timeout'    => 7,
			'user-agent' => 'WPShadow-Diagnostic/1.0',
			'sslverify'  => false,
			'headers'    => array(
				'Accept-Encoding' => 'gzip, deflate, br',
			),
		) );

		if ( is_wp_error( $response ) ) {
			return null; // Cannot test; skip to avoid false positives.
		}

		$encoding = wp_remote_retrieve_header( $response, 'content-encoding' );

		if ( ! empty( $encoding ) ) {
			$lower = strtolower( $encoding );
			if ( false !== strpos( $lower, 'gzip' ) || false !== strpos( $lower, 'br' ) || false !== strpos( $lower, 'deflate' ) ) {
				return null; // Compression is active.
			}
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'HTTP compression (gzip or Brotli) does not appear to be enabled on this server. Compression typically reduces HTML, CSS, and JavaScript transfer sizes by 60–80%, significantly improving page load times for visitors. Enable mod_deflate or mod_brotli on Apache, or the ngx_http_gzip_module on Nginx. Caching plugins such as W3 Total Cache and LiteSpeed Cache can also enable this.', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 40,
			'kb_link'      => 'https://wpshadow.com/kb/compression-enabled?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'      => array(
				'content_encoding' => $encoding ?: 'none',
				'checked_url'      => $home_url,
			),
		);
	}
}
