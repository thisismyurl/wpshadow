<?php
/**
 * SSL Protocol Version Diagnostic
 *
 * Checks SSL/TLS protocol version for security.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.1452
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SSL Protocol Version Diagnostic Class
 *
 * Verifies that site is using secure TLS protocols.
 *
 * @since 1.6035.1452
 */
class Diagnostic_SSL_Protocol_Version extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'ssl-protocol-version';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'SSL Protocol Version';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks SSL/TLS protocol version for security';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'ssl-security';

	/**
	 * Run the SSL protocol version diagnostic check.
	 *
	 * @since  1.6035.1452
	 * @return array|null Finding array if protocol issue detected, null otherwise.
	 */
	public static function check() {
		$cache_key = 'wpshadow_ssl_protocol';
		$cached = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$site_url = get_site_url();
		
		// Only check if site is using HTTPS.
		if ( strpos( $site_url, 'https://' ) !== 0 ) {
			set_transient( $cache_key, null, DAY_IN_SECONDS );
			return null;
		}

		$protocol_info = self::get_ssl_protocol( $site_url );

		if ( ! $protocol_info || empty( $protocol_info['protocol'] ) ) {
			set_transient( $cache_key, null, DAY_IN_SECONDS );
			return null;
		}

		$protocol = $protocol_info['protocol'];
		$result = null;

		// Check for insecure protocols.
		if ( in_array( $protocol, array( 'SSLv2', 'SSLv3' ), true ) ) {
			$result = array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %s: protocol version */
					__( 'Site is using insecure %s protocol. This has known security vulnerabilities.', 'wpshadow' ),
					$protocol
				),
				'severity'    => 'critical',
				'threat_level' => 100,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/insecure-ssl-protocol',
				'meta'        => array(
					'protocol' => $protocol,
				),
			);
		} elseif ( in_array( $protocol, array( 'TLSv1.0', 'TLSv1.1' ), true ) ) {
			$result = array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %s: protocol version */
					__( 'Site is using deprecated %s protocol. Upgrade to TLS 1.2 or 1.3 for better security.', 'wpshadow' ),
					$protocol
				),
				'severity'    => 'medium',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/upgrade-tls-protocol',
				'meta'        => array(
					'protocol' => $protocol,
				),
			);
		}

		set_transient( $cache_key, $result, DAY_IN_SECONDS );

		return $result;
	}

	/**
	 * Get SSL/TLS protocol version.
	 *
	 * @since  1.6035.1452
	 * @param  string $url Site URL.
	 * @return array|null Protocol information or null on failure.
	 */
	private static function get_ssl_protocol( string $url ) {
		$parsed = wp_parse_url( $url );
		$host = $parsed['host'] ?? '';

		if ( ! $host ) {
			return null;
		}

		// Try to detect protocol version by attempting connections.
		$protocols_to_test = array( 'tls', 'tlsv1.2', 'tlsv1.3', 'tlsv1.1', 'tlsv1.0', 'sslv3' );

		foreach ( $protocols_to_test as $test_protocol ) {
			$context = stream_context_create(
				array(
					'ssl' => array(
						'crypto_method'     => self::get_crypto_method( $test_protocol ),
						'capture_peer_cert' => true,
						'verify_peer'       => false,
						'verify_peer_name'  => false,
					),
				)
			);

			$socket = @stream_socket_client(
				'ssl://' . $host . ':443',
				$errno,
				$errstr,
				10,
				STREAM_CLIENT_CONNECT,
				$context
			);

			if ( $socket ) {
				fclose( $socket );
				
				// Map generic 'tls' to likely version.
				if ( 'tls' === $test_protocol ) {
					// Modern PHP defaults to TLS 1.2+.
					return array( 'protocol' => 'TLSv1.2+' );
				}

				return array( 'protocol' => strtoupper( str_replace( 'v', ' ', $test_protocol ) ) );
			}
		}

		return null;
	}

	/**
	 * Get crypto method constant for protocol.
	 *
	 * @since  1.6035.1452
	 * @param  string $protocol Protocol name.
	 * @return int Crypto method constant.
	 */
	private static function get_crypto_method( string $protocol ): int {
		switch ( $protocol ) {
			case 'tlsv1.3':
				return defined( 'STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT' ) ? STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT : STREAM_CRYPTO_METHOD_TLS_CLIENT;
			case 'tlsv1.2':
				return defined( 'STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT' ) ? STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT : STREAM_CRYPTO_METHOD_TLS_CLIENT;
			case 'tlsv1.1':
				return defined( 'STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT' ) ? STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT : STREAM_CRYPTO_METHOD_TLS_CLIENT;
			case 'tlsv1.0':
				return defined( 'STREAM_CRYPTO_METHOD_TLSv1_0_CLIENT' ) ? STREAM_CRYPTO_METHOD_TLSv1_0_CLIENT : STREAM_CRYPTO_METHOD_TLS_CLIENT;
			case 'sslv3':
				return defined( 'STREAM_CRYPTO_METHOD_SSLv3_CLIENT' ) ? STREAM_CRYPTO_METHOD_SSLv3_CLIENT : STREAM_CRYPTO_METHOD_SSLv23_CLIENT;
			default:
				return STREAM_CRYPTO_METHOD_TLS_CLIENT;
		}
	}
}
