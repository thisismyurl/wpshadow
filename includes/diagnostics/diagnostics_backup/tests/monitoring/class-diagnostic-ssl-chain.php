<?php
/**
 * SSL Certificate Chain Validation Diagnostic
 *
 * Verifies SSL certificate, intermediate certificates, and root
 * form a valid trust chain without breaks.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5029.1045
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SSL Certificate Chain Validation Class
 *
 * Validates SSL certificate chain integrity.
 * Broken chains cause browser warnings and user distrust.
 *
 * @since 1.5029.1045
 */
class Diagnostic_SSL_Chain extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'ssl-certificate-chain';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'SSL Certificate Chain Validation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies SSL certificate chain is valid and complete';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'monitoring';

	/**
	 * Run the diagnostic check.
	 *
	 * Tests SSL certificate chain validity using stream_context_create()
	 * and fsockopen() to retrieve and validate certificate chain.
	 *
	 * @since  1.5029.1045
	 * @return array|null Finding array if chain issues detected, null otherwise.
	 */
	public static function check() {
		$cache_key = 'wpshadow_ssl_chain_check';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		// Check if site is using HTTPS.
		if ( ! is_ssl() ) {
			set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
			return null;
		}

		$site_url = wp_parse_url( home_url(), PHP_URL_HOST );
		if ( empty( $site_url ) ) {
			set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
			return null;
		}

		$issues = array();

		// Test SSL connection using WordPress HTTP API.
		$response = wp_remote_get( home_url(), array(
			'timeout'   => 10,
			'sslverify' => true,
		) );

		if ( is_wp_error( $response ) ) {
			$error_message = $response->get_error_message();
			
			// Check for common SSL errors.
			if ( strpos( $error_message, 'SSL' ) !== false || strpos( $error_message, 'certificate' ) !== false ) {
				$issues[] = sprintf(
					/* translators: %s: error message */
					__( 'SSL connection error: %s', 'wpshadow' ),
					$error_message
				);
			}
		}

		// Check certificate expiration using get_option (may be stored by SSL monitoring).
		$ssl_expiry = get_option( 'wpshadow_ssl_expiry_date' );
		if ( $ssl_expiry ) {
			$days_until_expiry = ( strtotime( $ssl_expiry ) - time() ) / DAY_IN_SECONDS;
			if ( $days_until_expiry < 30 ) {
				$issues[] = sprintf(
					/* translators: %d: days until expiration */
					__( 'SSL certificate expires in %d days', 'wpshadow' ),
					(int) $days_until_expiry
				);
			}
		}

		// Use PHP's stream context to get SSL certificate info.
		$cert_info = self::get_ssl_certificate_info( $site_url );
		
		if ( is_wp_error( $cert_info ) ) {
			$issues[] = $cert_info->get_error_message();
		} elseif ( is_array( $cert_info ) ) {
			// Check certificate validity.
			if ( isset( $cert_info['valid_to'] ) ) {
				$days_remaining = ( $cert_info['valid_to'] - time() ) / DAY_IN_SECONDS;
				if ( $days_remaining < 30 ) {
					$issues[] = sprintf(
						/* translators: %d: days remaining */
						__( 'Certificate expires in %d days', 'wpshadow' ),
						(int) $days_remaining
					);
				}
			}

			// Check for self-signed certificate.
			if ( isset( $cert_info['self_signed'] ) && $cert_info['self_signed'] ) {
				$issues[] = __( 'Certificate is self-signed (not trusted by browsers)', 'wpshadow' );
			}
		}

		// If issues found, flag it.
		if ( ! empty( $issues ) ) {
			$threat_level = 50;
			if ( count( $issues ) >= 2 ) {
				$threat_level = 65;
			}

			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of issues */
					__( 'SSL certificate chain has %d issues. Users may see browser warnings.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => $threat_level > 60 ? 'high' : 'medium',
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/monitoring-ssl-chain',
				'data'         => array(
					'issues'    => $issues,
					'cert_info' => $cert_info,
				),
			);

			set_transient( $cache_key, $result, 12 * HOUR_IN_SECONDS );
			return $result;
		}

		set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
		return null;
	}

	/**
	 * Get SSL certificate information.
	 *
	 * @since  1.5029.1045
	 * @param  string $host Hostname to check.
	 * @return array|\WP_Error Certificate info or error.
	 */
	private static function get_ssl_certificate_info( $host ) {
		$context = stream_context_create( array(
			'ssl' => array(
				'capture_peer_cert' => true,
				'verify_peer'       => false,
				'verify_peer_name'  => false,
			),
		) );

		$socket = @stream_socket_client( // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			"ssl://{$host}:443",
			$errno,
			$errstr,
			30,
			STREAM_CLIENT_CONNECT,
			$context
		);

		if ( ! $socket ) {
			return new \WP_Error( 'ssl_connect_failed', __( 'Unable to connect to SSL port', 'wpshadow' ) );
		}

		$params = stream_context_get_params( $socket );
		fclose( $socket );

		if ( ! isset( $params['options']['ssl']['peer_certificate'] ) ) {
			return new \WP_Error( 'no_certificate', __( 'No certificate found', 'wpshadow' ) );
		}

		$cert_resource = $params['options']['ssl']['peer_certificate'];
		$cert_data     = openssl_x509_parse( $cert_resource );

		if ( ! $cert_data ) {
			return new \WP_Error( 'parse_failed', __( 'Certificate parse failed', 'wpshadow' ) );
		}

		return array(
			'valid_from'  => $cert_data['validFrom_time_t'] ?? null,
			'valid_to'    => $cert_data['validTo_time_t'] ?? null,
			'issuer'      => $cert_data['issuer']['CN'] ?? '',
			'subject'     => $cert_data['subject']['CN'] ?? '',
			'self_signed' => ( $cert_data['issuer']['CN'] ?? '' ) === ( $cert_data['subject']['CN'] ?? '' ),
		);
	}
}
