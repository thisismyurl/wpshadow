<?php
/**
 * SSL Domain Validity Treatment
 *
 * Checks if SSL certificate matches the domain.
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
 * SSL Domain Validity Treatment Class
 *
 * Verifies SSL certificate is valid for the current domain.
 * Like checking that your security badge has the right name on it.
 *
 * @since 1.6035.1545
 */
class Treatment_Ssl_Domain_Validity extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'ssl-domain-validity';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'SSL Domain Validity';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if SSL certificate matches the domain';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'ssl';

	/**
	 * Run the SSL domain validity treatment check.
	 *
	 * @since  1.6035.1545
	 * @return array|null Finding array if domain validity issues detected, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Ssl_Domain_Validity' );
	}

	/**
	 * Get domains covered by SSL certificate.
	 *
	 * @since  1.6035.1545
	 * @param  string $domain Domain to check.
	 * @return array|false Array of covered domains or false on failure.
	 */
	private static function get_certificate_domains( $domain ) {
		// Try to get cached certificate domains first.
		$cache_key = 'wpshadow_ssl_domains_' . md5( $domain );
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

		$domains = array();

		// Get CN (Common Name).
		if ( isset( $cert_data['subject']['CN'] ) ) {
			$domains[] = $cert_data['subject']['CN'];
		}

		// Get SANs (Subject Alternative Names).
		if ( isset( $cert_data['extensions']['subjectAltName'] ) ) {
			$san_string = $cert_data['extensions']['subjectAltName'];
			$san_parts = explode( ',', $san_string );
			foreach ( $san_parts as $san ) {
				$san = trim( str_replace( 'DNS:', '', $san ) );
				if ( ! empty( $san ) && ! in_array( $san, $domains, true ) ) {
					$domains[] = $san;
				}
			}
		}

		// Cache for 1 day.
		set_transient( $cache_key, $domains, DAY_IN_SECONDS );

		return $domains;
	}
}
