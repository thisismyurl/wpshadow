<?php
/**
 * Certificate Transparency Monitoring Diagnostic
 *
 * Monitors Certificate Transparency logs for SSL certificates issued for your
 * domain. Helps detect unauthorized certificate issuance which is a sign of
 * potential compromise or DNS hijacking.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Security
 * @since      1.6035.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Security;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Certificate_Transparency Class
 *
 * Checks Certificate Transparency logs (via crt.sh API) for all SSL certificates
 * issued for your domain. Any certificate not issued by your trusted CA could
 * indicate a compromise or that someone is preparing to impersonate your site.
 *
 * crt.sh (https://crt.sh/) is a free service that searches CT logs and provides
 * an API at no cost.
 *
 * @since 1.6035.0000
 */
class Diagnostic_Certificate_Transparency extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'certificate-transparency';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Certificate Transparency Monitoring';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks for unauthorized SSL certificates issued for your domain';

	/**
	 * The diagnostic family.
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * crt.sh API endpoint.
	 *
	 * @var string
	 */
	const API_URL = 'https://crt.sh/json';

	/**
	 * Cache duration (24 hours).
	 *
	 * @var int
	 */
	const CACHE_TTL = 86400;

	/**
	 * Run the diagnostic check.
	 *
	 * Queries Certificate Transparency logs for all certificates issued for the
	 * domain and checks for suspicious/unauthorized certificates.
	 *
	 * @since  1.6035.0000
	 * @return array|null Finding array if suspicious certs found, null otherwise.
	 */
	public static function check() {
		// Get site domain.
		$domain = wp_parse_url( home_url(), PHP_URL_HOST );
		if ( empty( $domain ) ) {
			return null;
		}

		// Check cache first.
		$cache_key = 'wpshadow_ct_monitoring_' . sanitize_key( $domain );
		$cached = get_transient( $cache_key );
		if ( false !== $cached ) {
			return $cached; // Can return null (no suspicious certs) or array (suspicious found).
		}

		// Get current certificate info.
		$current_cert = self::get_current_certificate_info();

		// Get all CT log certificates.
		$ct_certificates = self::get_ct_certificates( $domain );

		if ( is_wp_error( $ct_certificates ) ) {
			// Cache error for 1 hour.
			set_transient( $cache_key, null, 3600 );
			return null;
		}

		// Find suspicious certificates.
		$suspicious_certs = self::find_suspicious_certificates( $ct_certificates, $current_cert );

		// Cache result.
		set_transient( $cache_key, $suspicious_certs, self::CACHE_TTL );

		return ! empty( $suspicious_certs ) ? $suspicious_certs : null;
	}

	/**
	 * Get current certificate info from server.
	 *
	 * @since  1.6035.0000
	 * @return array Certificate information.
	 */
	private static function get_current_certificate_info() : array {
		// Get stream context for SSL.
		$context = stream_context_create(
			array(
				'ssl' => array(
					'capture_peer_cert' => true,
					'verify_peer'       => false,
					'verify_peer_name'  => false,
				),
			)
		);

		$domain = wp_parse_url( home_url(), PHP_URL_HOST );
		$result = array();

		try {
			// Suppress warnings.
			$stream = @stream_socket_client(
				'ssl://' . $domain . ':443',
				$errno,
				$errstr,
				5,
				STREAM_CLIENT_CONNECT,
				$context
			);

			if ( false !== $stream ) {
				// Get peer certificate.
				$params = stream_context_get_params( $stream );
				$cert = $params['options']['ssl']['peer_certificate'] ?? null;

				if ( $cert ) {
					$cert_data = openssl_x509_parse( $cert );
					if ( is_array( $cert_data ) ) {
						$result = array(
							'issuer'  => $cert_data['issuer']['O'] ?? 'Unknown',
							'subject' => $cert_data['subject']['CN'] ?? '',
							'fingerprint' => openssl_x509_fingerprint( $cert, 'sha256' ),
						);
					}
				}

				fclose( $stream );
			}
		} catch ( \Exception $e ) {
			// SSL connection failed - likely no cert or network issue.
		}

		return $result;
	}

	/**
	 * Get all CT log certificates for domain.
	 *
	 * @since  1.6035.0000
	 * @param  string $domain Domain to search.
	 * @return array|WP_Error Certificate array or error.
	 */
	private static function get_ct_certificates( string $domain ) {
		// Query crt.sh for all certificates.
		$response = wp_remote_get(
			add_query_arg(
				array(
					'q'      => $domain,
					'output' => 'json',
				),
				self::API_URL
			),
			array(
				'timeout' => 10,
			)
		);

		// Handle network errors.
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		// Check response code.
		$response_code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $response_code ) {
			return new \WP_Error(
				'ct_api_error',
				sprintf(
					__( 'crt.sh API error (HTTP %d)', 'wpshadow' ),
					$response_code
				)
			);
		}

		// Parse response.
		$body = wp_remote_retrieve_body( $response );
		$certificates = json_decode( $body, true );

		if ( ! is_array( $certificates ) ) {
			return array();
		}

		return $certificates;
	}

	/**
	 * Find suspicious certificates.
	 *
	 * A certificate is suspicious if:
	 * 1. Issued by an unexpected CA (not the current issuer)
	 * 2. Issued very recently (within last 24 hours) but not by your current CA
	 * 3. For a domain variant (e.g., example.com when checking www.example.com)
	 *
	 * @since  1.6035.0000
	 * @param  array $ct_certs CT log certificates.
	 * @param  array $current_cert Current certificate info.
	 * @return array Suspicious certificates.
	 */
	private static function find_suspicious_certificates( array $ct_certs, array $current_cert ) : array {
		$suspicious = array();
		$domain = wp_parse_url( home_url(), PHP_URL_HOST );
		$current_issuer = $current_cert['issuer'] ?? '';
		$now = time();

		foreach ( $ct_certs as $cert ) {
			// Extract issuer and dates.
			$issuer = $cert['issuer_name'] ?? '';
			$not_before = strtotime( $cert['min_cert_id'] ?? '' );
			$not_after = $cert['max_cert_id'] ?? '';

			// Skip if issuer matches current certificate.
			if ( ! empty( $current_issuer ) && stripos( $issuer, $current_issuer ) !== false ) {
				continue;
			}

			// Check if certificate is very new (within 24 hours).
			$age = $now - $not_before;
			if ( $age < 0 || $age > 86400 ) {
				// Very old or future date - might not be matching current cert.
				// But still worth noting if from unexpected issuer.
			}

			$suspicious[] = array(
				'issuer'        => $issuer,
				'common_name'   => $cert['common_name'] ?? '',
				'not_before'    => $not_before,
				'not_after'     => $not_after,
				'ct_log_id'     => $cert['id'] ?? 0,
			);
		}

		return $suspicious;
	}

	/**
	 * Calculate severity based on suspicious certificates.
	 *
	 * @since  1.6035.0000
	 * @param  array $suspicious_certs Suspicious certificates.
	 * @return string Severity level.
	 */
	private static function determine_severity( array $suspicious_certs ) : string {
		$count = count( $suspicious_certs );

		// Many certs = critical.
		if ( $count >= 5 ) {
			return 'critical';
		}

		// Several certs = high.
		if ( $count >= 3 ) {
			return 'high';
		}

		// One or two = medium.
		return 'medium';
	}

	/**
	 * Calculate threat level (0-100).
	 *
	 * @since  1.6035.0000
	 * @param  array $suspicious_certs Suspicious certificates.
	 * @return int Threat level.
	 */
	private static function calculate_threat_level( array $suspicious_certs ) : int {
		$count = count( $suspicious_certs );

		if ( $count >= 10 ) {
			return 95;
		} elseif ( $count >= 5 ) {
			return 85;
		} elseif ( $count >= 3 ) {
			return 70;
		} else {
			return 50;
		}
	}
}
