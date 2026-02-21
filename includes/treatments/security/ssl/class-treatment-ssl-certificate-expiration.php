<?php
/**
 * SSL Certificate Expiration Treatment
 *
 * Checks if SSL certificate is expiring soon.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6035.1545
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SSL Certificate Expiration Treatment Class
 *
 * Monitors SSL certificate validity and expiration.
 * Like checking when your security badge expires.
 *
 * @since 1.6035.1545
 */
class Treatment_Ssl_Certificate_Expiration extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'ssl-certificate-expiration';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'SSL Certificate Expiration';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if SSL certificate is expiring soon';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'ssl';

	/**
	 * Run the SSL certificate expiration treatment check.
	 *
	 * @since  1.6035.1545
	 * @return array|null Finding array if certificate expiration issues detected, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_SSL_Certificate_Expiration' );
	}

	/**
	 * Get SSL certificate information for a domain.
	 *
	 * @since  1.6035.1545
	 * @param  string $domain Domain to check.
	 * @return array|false Certificate info or false on failure.
	 */
	private static function get_certificate_info( $domain ) {
		// Try to get cached certificate info first.
		$cache_key = 'wpshadow_ssl_cert_' . md5( $domain );
		$cached = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		// Use stream context to get certificate.
		$context = stream_context_create(
			array(
				'ssl' => array(
					'capture_peer_cert' => true,
					'verify_peer'       => false,
					'verify_peer_name'  => false,
				),
			)
		);

		$stream = @stream_socket_client(
			'ssl://' . $domain . ':443',
			$errno,
			$errstr,
			30,
			STREAM_CLIENT_CONNECT,
			$context
		);

		if ( ! $stream ) {
			return false;
		}

		$params = stream_context_get_params( $stream );
		fclose( $stream );

		if ( ! isset( $params['options']['ssl']['peer_certificate'] ) ) {
			return false;
		}

		$cert_resource = $params['options']['ssl']['peer_certificate'];
		$cert_data = openssl_x509_parse( $cert_resource );

		if ( ! $cert_data ) {
			return false;
		}

		$cert_info = array(
			'expires' => $cert_data['validTo_time_t'] ?? 0,
			'issuer'  => $cert_data['issuer']['CN'] ?? 'Unknown',
		);

		// Cache for 1 day.
		set_transient( $cache_key, $cert_info, DAY_IN_SECONDS );

		return $cert_info;
	}
}
