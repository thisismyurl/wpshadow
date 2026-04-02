<?php
/**
 * Client-Side Secret Exposure Diagnostic
 *
 * Checks if API keys, secrets, or PII appear in client-side code (HTML/JavaScript).
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Helpers\Diagnostic_HTML_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Client-Side Secret Exposure Diagnostic Class
 *
 * Client-side code is public. Exposing secrets is like writing passwords on sticky notes.
 * API keys stolen from source code result in service abuse and increased bills.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Client_Side_Secret_Exposure extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'client-side-secret-exposure';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Secrets Exposed in HTML or JavaScript';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if API keys, secrets, or PII appear in HTML or JavaScript';

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
		$exposed_secrets = array();

		// Get homepage HTML using WordPress remote request
		// Note: Diagnostic_HTML_Helper doesn't exist, use wp_remote_get instead
		$home_url = home_url( '/' );
		$response = wp_remote_get( $home_url, array( 'timeout' => 5, 'sslverify' => false ) );
		
		if ( is_wp_error( $response ) ) {
			return null;
		}
		
		$html = wp_remote_retrieve_body( $response );
		if ( empty( $html ) ) {
			return null;
		}

		// Check for API keys.
		$api_key_patterns = array(
			'/api[_-]?key\s*[:=]\s*["\']([a-zA-Z0-9_\-]{16,})["\']/' => 'API key',
			'/sk_live_[a-zA-Z0-9]{24,}/' => 'Stripe live key',
			'/pk_live_[a-zA-Z0-9]{24,}/' => 'Stripe publishable key',
			'/AIza[a-zA-Z0-9_\-]{35}/'   => 'Google API key',
			'/access[_-]?token\s*[:=]\s*["\']([a-zA-Z0-9_\-]{20,})["\']/' => 'Access token',
			'/secret[_-]?key\s*[:=]\s*["\']([a-zA-Z0-9_\-]{20,})["\']/' => 'Secret key',
		);

		foreach ( $api_key_patterns as $pattern => $type ) {
			if ( preg_match( $pattern, $html, $matches ) ) {
				$exposed_secrets[] = array(
					'type'  => $type,
					'match' => substr( $matches[0], 0, 50 ) . '...',
				);
			}
		}

		// Check for database credentials.
		if ( preg_match( '/DB_PASSWORD|DB_USER|DB_NAME/i', $html ) ) {
			$exposed_secrets[] = array(
				'type'  => 'Database credentials',
				'match' => 'DB_* constants visible',
			);
		}

		// Check for PII in data attributes.
		$pii_patterns = array(
			'/data-email\s*=\s*["\']([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})["\']/' => 'Email address',
			'/data-phone\s*=\s*["\'][0-9\-\(\)\s]{10,}["\']/' => 'Phone number',
			'/data-ssn\s*=\s*["\'][0-9\-]{9,}["\']/' => 'SSN or sensitive ID',
		);

		foreach ( $pii_patterns as $pattern => $type ) {
			if ( preg_match( $pattern, $html ) ) {
				$exposed_secrets[] = array(
					'type'  => $type,
					'match' => 'Found in data attributes',
				);
			}
		}

		if ( empty( $exposed_secrets ) ) {
			return null; // No exposed secrets.
		}

		$severity     = 'critical';
		$threat_level = 95;

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: number of exposed secrets */
				__( 'Found %d exposed secret(s) in client-side code. Like writing passwords on sticky notes—everyone can see them. Move keys to server-side immediately.', 'wpshadow' ),
				count( $exposed_secrets )
			),
			'severity'     => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/client-side-secret-exposure',
			'meta'         => array(
				'exposed_secrets' => $exposed_secrets,
			),
		);
	}
}
