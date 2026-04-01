<?php
/**
 * Certificate Chain Validation Diagnostic
 *
 * Validates SSL certificate chain and trust.
 *
 * @package    WPShadow
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
 * Certificate Chain Validation Diagnostic Class
 *
 * Verifies that SSL certificate chain is valid and trusted.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Certificate_Chain_Validation extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'certificate-chain-validation';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Certificate Chain Validation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates SSL certificate chain and trust';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'ssl-security';

	/**
	 * Run the certificate chain validation diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if validation issue detected, null otherwise.
	 */
	public static function check() {
		$cache_key = 'wpshadow_ssl_chain';
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

		$validation = self::validate_certificate_chain( $site_url );
		$is_production = self::is_production_environment();

		$result = null;

		if ( ! $validation['valid'] ) {
			if ( $validation['self_signed'] ) {
				if ( $is_production ) {
					$result = array(
						'id'          => self::$slug,
						'title'       => self::$title,
						'description' => __( 'Site is using a self-signed SSL certificate in production. Users will see security warnings.', 'wpshadow' ),
						'severity'    => 'critical',
						'threat_level' => 95,
						'auto_fixable' => false,
						'kb_link'     => 'https://wpshadow.com/kb/self-signed-certificate-production?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
					);
				} else {
					$result = array(
						'id'          => self::$slug,
						'title'       => self::$title,
						'description' => __( 'Site is using a self-signed SSL certificate. This is acceptable in development/staging.', 'wpshadow' ),
						'severity'    => 'low',
						'threat_level' => 20,
						'auto_fixable' => false,
						'kb_link'     => 'https://wpshadow.com/kb/self-signed-certificate-dev?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
					);
				}
			} else {
				$severity = $is_production ? 'high' : 'medium';
				$threat = $is_production ? 85 : 50;

				$result = array(
					'id'          => self::$slug,
					'title'       => self::$title,
					'description' => __( 'SSL certificate chain validation failed. Users may see security warnings.', 'wpshadow' ),
					'severity'    => $severity,
					'threat_level' => $threat,
					'auto_fixable' => false,
					'kb_link'     => 'https://wpshadow.com/kb/ssl-chain-validation?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
					'meta'        => array(
						'error' => $validation['error'],
					),
				);
			}
		}

		set_transient( $cache_key, $result, DAY_IN_SECONDS );

		return $result;
	}

	/**
	 * Validate SSL certificate chain.
	 *
	 * @since 0.6093.1200
	 * @param  string $url Site URL.
	 * @return array Validation result.
	 */
	private static function validate_certificate_chain( string $url ): array {
		$parsed = wp_parse_url( $url );
		$host = $parsed['host'] ?? '';

		if ( ! $host ) {
			return array(
				'valid'       => false,
				'self_signed' => false,
				'error'       => 'Invalid URL',
			);
		}

		// First, try with peer verification enabled.
		$context = stream_context_create(
			array(
				'ssl' => array(
					'capture_peer_cert' => true,
					'verify_peer'       => true,
					'verify_peer_name'  => true,
				),
			)
		);

		$socket = @stream_socket_client(
			'ssl://' . $host . ':443',
			$errno,
			$errstr,
			30,
			STREAM_CLIENT_CONNECT,
			$context
		);

		if ( $socket ) {
			$params = stream_context_get_params( $socket );
			fclose( $socket );

			// Successfully connected with verification - certificate is valid.
			return array(
				'valid'       => true,
				'self_signed' => false,
				'error'       => '',
			);
		}

		// Failed with verification. Try without to check if self-signed.
		$context = stream_context_create(
			array(
				'ssl' => array(
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
			30,
			STREAM_CLIENT_CONNECT,
			$context
		);

		if ( ! $socket ) {
			return array(
				'valid'       => false,
				'self_signed' => false,
				'error'       => $errstr,
			);
		}

		$params = stream_context_get_params( $socket );
		fclose( $socket );

		if ( ! isset( $params['options']['ssl']['peer_certificate'] ) ) {
			return array(
				'valid'       => false,
				'self_signed' => false,
				'error'       => 'No certificate found',
			);
		}

		$cert_info = openssl_x509_parse( $params['options']['ssl']['peer_certificate'] );

		// Check if self-signed (issuer = subject).
		$is_self_signed = false;
		if ( isset( $cert_info['issuer'], $cert_info['subject'] ) ) {
			$is_self_signed = ( $cert_info['issuer'] === $cert_info['subject'] );
		}

		return array(
			'valid'       => false,
			'self_signed' => $is_self_signed,
			'error'       => $is_self_signed ? 'Self-signed certificate' : 'Chain validation failed',
		);
	}

	/**
	 * Detect if this is a production environment.
	 *
	 * @since 0.6093.1200
	 * @return bool True if production, false otherwise.
	 */
	private static function is_production_environment(): bool {
		$site_url = get_site_url();

		// Check WP_DEBUG constant.
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			return false;
		}

		// Check for development/staging indicators in URL.
		$dev_indicators = array( 'localhost', '.local', 'staging', 'dev.', '.dev', '-dev.', 'test.', '.test' );

		foreach ( $dev_indicators as $indicator ) {
			if ( strpos( $site_url, $indicator ) !== false ) {
				return false;
			}
		}

		return true;
	}
}
