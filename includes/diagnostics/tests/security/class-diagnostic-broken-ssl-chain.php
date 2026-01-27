<?php
/**
 * Diagnostic: Broken SSL Chain Detection
 *
 * Checks SSL certificate chain for missing intermediate certificates or configuration issues.
 * A broken SSL chain causes browser warnings and security vulnerabilities.
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
 * Class Diagnostic_Broken_Ssl_Chain
 *
 * Validates SSL certificate chain integrity.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Broken_Ssl_Chain extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'broken-ssl-chain';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Broken SSL Chain Detection';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks SSL certificate chain for missing intermediate certificates';

	/**
	 * Check SSL certificate chain integrity.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		// Only check if site uses HTTPS.
		if ( ! is_ssl() ) {
			return null; // No SSL to check.
		}

		$site_url = home_url( '/', 'https' );
		$parsed   = wp_parse_url( $site_url );
		$host     = $parsed['host'] ?? '';
		$port     = 443;

		if ( empty( $host ) ) {
			return null;
		}

		// Attempt to connect and verify certificate chain.
		$context = stream_context_create(
			array(
				'ssl' => array(
					'capture_peer_cert'       => true,
					'capture_peer_cert_chain' => true,
					'verify_peer'             => true,
					'verify_peer_name'        => true,
				),
			)
		);

		// Suppress errors for this check.
		// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		$client = @stream_socket_client(
			"ssl://{$host}:{$port}",
			$errno,
			$errstr,
			30,
			STREAM_CLIENT_CONNECT,
			$context
		);

		if ( ! $client ) {
			// Could not connect - SSL may be misconfigured.
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: 1: Error number, 2: Error message */
					__( 'Could not verify SSL certificate chain: %1$d - %2$s', 'wpshadow' ),
					$errno,
					$errstr
				),
				'severity'    => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/broken_ssl_chain',
				'meta'        => array(
					'host'   => $host,
					'errno'  => $errno,
					'errstr' => $errstr,
				),
			);
		}

		$params = stream_context_get_params( $client );

		// Check if certificate chain was captured.
		if ( empty( $params['options']['ssl']['peer_certificate_chain'] ) ) {
			fclose( $client );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'SSL certificate chain could not be retrieved. This may indicate a configuration issue.', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/broken_ssl_chain',
				'meta'        => array(
					'host' => $host,
				),
			);
		}

		$chain = $params['options']['ssl']['peer_certificate_chain'];
		fclose( $client );

		// A valid chain should have at least 2 certificates (server + intermediate).
		// Single certificate chain (no intermediates) can cause issues.
		if ( count( $chain ) < 2 ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'SSL certificate chain is incomplete. Missing intermediate certificates may cause browser warnings.', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/broken_ssl_chain',
				'meta'        => array(
					'host'          => $host,
					'chain_length'  => count( $chain ),
				),
			);
		}

		// SSL chain appears valid.
		return null;
	}
}
