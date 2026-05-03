<?php
/**
 * HTTP/2 or HTTP/3 Enabled Diagnostic
 *
 * Detects whether the site is served over HTTP/2 or HTTP/3 by making a
 * cURL HEAD request to the home URL and inspecting the protocol version.
 *
 * @package ThisIsMyURL\Shadow
 * @subpackage Diagnostics
 * @since 0.6095
 */

declare(strict_types=1);

namespace ThisIsMyURL\Shadow\Diagnostics;

use ThisIsMyURL\Shadow\Core\Diagnostic_Base;
use ThisIsMyURL\Shadow\Diagnostics\Helpers\Diagnostic_Request_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Http2_Or_Http3_Enabled Class
 *
 * @since 0.6095
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
	 * Confidence level of this diagnostic.
	 *
	 * @var string
	 */
	protected static $confidence = 'standard';

	private const HTTP2_VERSION = 2.0;

	/**
	 * Run the diagnostic check.
	 *
	 * Makes a HEAD request to the site home URL via the WordPress HTTP API and
	 * inspects the negotiated protocol version when exposed by the transport.
	 * Returns null (healthy) if HTTP/2 or higher was negotiated. Returns null
	 * when the active transport does not expose protocol version metadata to
	 * avoid false positives. The plugin's readiness registry marks this
	 * diagnostic as beta because transport metadata exposure varies by host.
	 *
	 * @since  0.6095
	 * @return array|null Finding array if issue exists, null if healthy.
	 */
	public static function check() {
		$url = home_url( '/' );

		// Only attempt the check over HTTPS as HTTP/2 is effectively HTTPS-only.
		if ( ! str_starts_with( $url, 'https://' ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Your site is not serving pages over HTTPS. HTTP/2 and HTTP/3 require HTTPS in all major browsers. Upgrading to HTTPS is a prerequisite for protocol-level performance improvements.', 'thisismyurl-shadow' ),
				'severity'     => 'high',
				'threat_level' => 65,
				'details'      => array(
					'fix' => __( 'Enable SSL/TLS on your hosting account and ensure your WordPress Address and Site Address use https://. Use a free certificate from Let\'s Encrypt via your host control panel or a plugin such as Really Simple SSL.', 'thisismyurl-shadow' ),
				),
			);
		}

		$result = Diagnostic_Request_Helper::head_result(
			$url,
			array(
				'timeout'       => 8,
				'redirection'   => 3,
				'sslverify'     => true,
				'guard_purpose' => 'diagnostics_http',
			)
		);

		if ( empty( $result['success'] ) || empty( $result['response'] ) || ! is_array( $result['response'] ) ) {
			return null;
		}

		$http_version = self::detect_protocol_version( $result['response'] );
		if ( null === $http_version ) {
			return null;
		}

		if ( $http_version >= self::HTTP2_VERSION ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Your site is being served over HTTP/1.1. HTTP/2 and HTTP/3 provide significant performance improvements through request multiplexing, header compression, and reduced connection overhead, which lower page load times.', 'thisismyurl-shadow' ),
			'severity'     => 'medium',
			'threat_level' => 45,
			'details'      => array(
				'detected_version' => sprintf( 'HTTP/%s', rtrim( rtrim( number_format( $http_version, 1, '.', '' ), '0' ), '.' ) ),
				'fix'              => __( 'Contact your hosting provider and ask them to enable HTTP/2 (or HTTP/3) for your domain. Most modern hosts support HTTP/2 with one-click activation in their control panel. Alternatively, placing your site behind Cloudflare (free tier) will automatically proxy requests over HTTP/2 and HTTP/3 without server changes.', 'thisismyurl-shadow' ),
			),
		);
	}

	/**
	 * Extract the negotiated HTTP protocol version from a WordPress HTTP response.
	 *
	 * @param array<string, mixed> $response HTTP API response array.
	 * @return float|null
	 */
	private static function detect_protocol_version( array $response ): ?float {
		if ( empty( $response['http_response'] ) || ! is_object( $response['http_response'] ) || ! method_exists( $response['http_response'], 'get_response_object' ) ) {
			return null;
		}

		$response_object = $response['http_response']->get_response_object();
		if ( ! is_object( $response_object ) || ! isset( $response_object->protocol_version ) ) {
			return null;
		}

		$protocol_version = (float) $response_object->protocol_version;
		return $protocol_version > 0 ? $protocol_version : null;
	}
}
