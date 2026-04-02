<?php
/**
 * HTTP/2 or HTTP/3 Enabled Diagnostic
 *
 * Detects whether the site is served over HTTP/2 or HTTP/3 by making a
 * cURL HEAD request to the home URL and inspecting the protocol version.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Http2_Or_Http3_Enabled Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_Http2_Or_Http3_Enabled extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'http2-or-http3-enabled';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'HTTP/2 or HTTP/3 Enabled';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether the site is served over HTTP/2 or HTTP/3 to take advantage of multiplexing, header compression, and improved connection performance.';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * CURLINFO_HTTP_VERSION values representing HTTP/2 and HTTP/3.
	 * 3 = HTTP/2, 30 = HTTP/3 (as of libcurl 7.66+ reporting).
	 */
	private const HTTP2_VERSION  = 3;
	private const HTTP3_VERSION  = 30;

	/**
	 * Run the diagnostic check.
	 *
	 * Makes a HEAD request to the site home URL via cURL (with HTTP/2 preferred)
	 * and reads back the actual protocol used. Returns null (healthy) if HTTP/2
	 * or higher was negotiated. Returns null if cURL is unavailable or the check
	 * cannot be performed to avoid false positives.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array if issue exists, null if healthy.
	 */
	public static function check() {
		// Cannot detect without cURL.
		if ( ! function_exists( 'curl_init' ) || ! function_exists( 'curl_getinfo' ) ) {
			return null;
		}

		$url = home_url( '/' );

		// Only attempt the check over HTTPS as HTTP/2 is effectively HTTPS-only.
		if ( ! str_starts_with( $url, 'https://' ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Your site is not serving pages over HTTPS. HTTP/2 and HTTP/3 require HTTPS in all major browsers. Upgrading to HTTPS is a prerequisite for protocol-level performance improvements.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/http2-or-http3-enabled?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'fix' => __( 'Enable SSL/TLS on your hosting account and ensure your WordPress Address and Site Address use https://. Use a free certificate from Let\'s Encrypt via your host control panel or a plugin such as Really Simple SSL.', 'wpshadow' ),
				),
			);
		}

		// phpcs:disable WordPress.WP.AlternativeFunctions.curl_curl_init
		$ch = curl_init( $url );
		if ( false === $ch ) {
			return null;
		}

		curl_setopt_array(
			$ch,
			array(
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_HEADER         => false,
				CURLOPT_NOBODY         => true,
				CURLOPT_TIMEOUT        => 8,
				CURLOPT_CONNECTTIMEOUT => 5,
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_SSL_VERIFYHOST => false,
				CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_2_0,
			)
		);

		curl_exec( $ch );
		$http_version = (int) curl_getinfo( $ch, CURLINFO_HTTP_VERSION );
		$curl_error   = curl_error( $ch );
		curl_close( $ch );
		// phpcs:enable

		// If cURL returned an error, skip to avoid false positives.
		if ( $curl_error ) {
			return null;
		}

		if ( $http_version >= self::HTTP2_VERSION ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Your site is being served over HTTP/1.1. HTTP/2 and HTTP/3 provide significant performance improvements through request multiplexing, header compression, and reduced connection overhead, which lower page load times.', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 45,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/http2-or-http3-enabled?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'      => array(
				'detected_version' => $http_version <= 2 ? 'HTTP/1.1' : 'HTTP/1.0',
				'fix'              => __( 'Contact your hosting provider and ask them to enable HTTP/2 (or HTTP/3) for your domain. Most modern hosts support HTTP/2 with one-click activation in their control panel. Alternatively, placing your site behind Cloudflare (free tier) will automatically proxy requests over HTTP/2 and HTTP/3 without server changes.', 'wpshadow' ),
			),
		);
	}
}
