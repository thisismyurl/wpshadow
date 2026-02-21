<?php
/**
 * SSL Protocol Version Treatment
 *
 * Checks SSL/TLS protocol version for security.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6035.1452
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SSL Protocol Version Treatment Class
 *
 * Verifies that site is using secure TLS protocols.
 *
 * @since 1.6035.1452
 */
class Treatment_SSL_Protocol_Version extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'ssl-protocol-version';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'SSL Protocol Version';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks SSL/TLS protocol version for security';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'ssl-security';

	/**
	 * Run the SSL protocol version treatment check.
	 *
	 * @since  1.6035.1452
	 * @return array|null Finding array if protocol issue detected, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_SSL_Protocol_Version' );
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
