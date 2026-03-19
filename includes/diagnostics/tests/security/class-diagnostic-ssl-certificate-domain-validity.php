<?php
/**
 * SSL Certificate Domain Validity Diagnostic
 *
 * Validates that the SSL certificate covers the site domain.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Diagnostics\Helpers\Diagnostic_URL_And_Pattern_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SSL Certificate Domain Validity Diagnostic Class
 *
 * Detects domain mismatches between certificate SAN/CN and site domain.
 *
 * @since 1.6093.1200
 */
class Diagnostic_SSL_Certificate_Domain_Validity extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'ssl-certificate-domain-validity';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'SSL Certificate Domain Validity';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if the SSL certificate matches the site domain and SANs';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$domain = Diagnostic_URL_And_Pattern_Helper::get_domain( home_url() );
		if ( empty( $domain ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Unable to determine site domain for SSL certificate validation.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/ssl-certificate-domain-validity',
			);
		}

		$cert_info = self::get_certificate_info( $domain );
		if ( empty( $cert_info ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Unable to read SSL certificate details. Verify OpenSSL support and certificate installation.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/ssl-certificate-domain-validity',
			);
		}

		$names = self::get_certificate_domains( $cert_info );
		if ( empty( $names ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'No valid SAN or CN entries found in the SSL certificate.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/ssl-certificate-domain-validity',
				'meta'         => array(
					'domain' => $domain,
				),
			);
		}

		foreach ( $names as $name ) {
			if ( self::domain_matches( $domain, $name ) ) {
				return null;
			}
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'SSL certificate does not match the site domain. Visitors will see a certificate warning in browsers.', 'wpshadow' ),
			'severity'     => 'critical',
			'threat_level' => 90,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/ssl-certificate-domain-validity',
			'meta'         => array(
				'domain'         => $domain,
				'certificate_cn' => $cert_info['subject']['CN'] ?? '',
				'san_entries'    => $names,
			),
		);
	}

	/**
	 * Get SSL certificate info for a domain.
	 *
	 * @since 1.6093.1200
	 * @param  string $domain Domain name.
	 * @return array SSL certificate info array.
	 */
	private static function get_certificate_info( string $domain ): array {
		$cache_key = 'wpshadow_ssl_cert_info_' . md5( $domain );
		$cached = get_transient( $cache_key );
		if ( is_array( $cached ) ) {
			return $cached;
		}

		if ( ! function_exists( 'openssl_x509_parse' ) ) {
			return array();
		}

		$context = stream_context_create(
			array(
				'ssl' => array(
					'capture_peer_cert' => true,
					'verify_peer'       => false,
					'verify_peer_name'  => false,
				),
			)
		);

		$client = @stream_socket_client(
			'ssl://' . $domain . ':443',
			$errno,
			$errstr,
			10,
			STREAM_CLIENT_CONNECT,
			$context
		);

		if ( ! $client ) {
			return array();
		}

		$params = stream_context_get_params( $client );
		$cert = $params['options']['ssl']['peer_certificate'] ?? null;

		if ( ! $cert ) {
			return array();
		}

		$info = openssl_x509_parse( $cert );
		if ( ! is_array( $info ) ) {
			return array();
		}

		set_transient( $cache_key, $info, 12 * HOUR_IN_SECONDS );

		return $info;
	}

	/**
	 * Extract SAN and CN domains from certificate info.
	 *
	 * @since 1.6093.1200
	 * @param  array $cert_info Certificate info array.
	 * @return array List of domain names.
	 */
	private static function get_certificate_domains( array $cert_info ): array {
		$names = array();

		$cn = $cert_info['subject']['CN'] ?? '';
		if ( ! empty( $cn ) ) {
			$names[] = $cn;
		}

		$san = $cert_info['extensions']['subjectAltName'] ?? '';
		if ( ! empty( $san ) ) {
			$entries = explode( ',', $san );
			foreach ( $entries as $entry ) {
				$entry = trim( $entry );
				if ( 0 === stripos( $entry, 'DNS:' ) ) {
					$names[] = trim( substr( $entry, 4 ) );
				}
			}
		}

		return array_unique( array_filter( $names ) );
	}

	/**
	 * Check if a certificate domain matches the site domain.
	 *
	 * @since 1.6093.1200
	 * @param  string $domain Site domain.
	 * @param  string $cert_domain Certificate domain entry.
	 * @return bool True when domain matches.
	 */
	private static function domain_matches( string $domain, string $cert_domain ): bool {
		$domain = strtolower( $domain );
		$cert_domain = strtolower( $cert_domain );

		if ( $domain === $cert_domain ) {
			return true;
		}

		if ( 0 === strpos( $cert_domain, '*.' ) ) {
			$base = substr( $cert_domain, 2 );
			return $domain === $base || ( '.' . $base === substr( $domain, -strlen( $base ) - 1 ) );
		}

		return false;
	}
}
