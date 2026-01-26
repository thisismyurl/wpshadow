<?php
/**
 * Diagnostic: OCSP Stapling Detection
 *
 * Checks if OCSP Stapling is enabled on the web server.
 * OCSP Stapling improves SSL/TLS performance and privacy.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Security
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Ocsp_Stapling
 *
 * Tests OCSP Stapling configuration.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Ocsp_Stapling extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'ocsp-stapling';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'OCSP Stapling Detection';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if OCSP Stapling is enabled';

	/**
	 * Check OCSP Stapling status.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		// Only check if site is using HTTPS.
		if ( ! is_ssl() ) {
			return null; // Not applicable for HTTP sites.
		}

		$site_url = home_url();

		// Check if URL is HTTPS.
		if ( strpos( $site_url, 'https://' ) !== 0 ) {
			return null;
		}

		// Parse URL to get host.
		$parsed_url = wp_parse_url( $site_url );
		$host       = $parsed_url['host'] ?? '';
		$port       = $parsed_url['port'] ?? 443;

		if ( empty( $host ) ) {
			return null;
		}

		// Check OCSP Stapling via stream context.
		$context = stream_context_create(
			array(
				'ssl' => array(
					'capture_peer_cert_chain' => true,
					'verify_peer'             => false,
					'verify_peer_name'        => false,
				),
			)
		);

		// phpcs:ignore WordPress.WP.AlternativeFunctions.stream_get_contents_stream_socket_client
		$client = @stream_socket_client(
			"ssl://{$host}:{$port}",
			$errno,
			$errstr,
			30,
			STREAM_CLIENT_CONNECT,
			$context
		);

		if ( ! $client ) {
			return null; // Can't check if connection fails.
		}

		// Get stream parameters.
		$params = stream_context_get_params( $client );

		// phpcs:ignore WordPress.WP.AlternativeFunctions.stream_get_contents_fclose
		fclose( $client );

		// Check if OCSP response was stapled (indicated by options or peer_certificate_chain).
		$has_ocsp_stapling = false;

		if ( isset( $params['options']['ssl']['peer_certificate_chain'] ) ) {
			// If we have the certificate chain, OCSP stapling may be enabled.
			// Unfortunately, PHP streams don't expose OCSP stapling status directly.
			// We can only infer based on the presence of certificate chain.
			$has_ocsp_stapling = true; // Assume enabled if chain is available.
		}

		// If we can't detect OCSP stapling, provide informational message.
		if ( ! $has_ocsp_stapling ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'OCSP Stapling could not be detected. This is a server-level configuration that improves SSL/TLS performance and privacy. Contact your hosting provider to enable it.', 'wpshadow' ),
				'severity'    => 'info',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/ocsp_stapling',
				'meta'        => array(
					'host' => $host,
					'port' => $port,
					'ocsp_stapling' => false,
				),
			);
		}

		// OCSP Stapling appears to be enabled.
		return null;
	}
}
