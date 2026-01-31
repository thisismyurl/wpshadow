<?php
/**
 * HTTP Security Headers Configuration Diagnostic
 *
 * Validates security headers are configured to prevent common attacks.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26028.1905
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * HTTP Security Headers Configuration Class
 *
 * Tests security headers.
 *
 * @since 1.26028.1905
 */
class Diagnostic_Http_Security_Headers_Configuration extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'http-security-headers-configuration';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'HTTP Security Headers Configuration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates security headers are configured to prevent common attacks';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26028.1905
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$headers_check = self::check_security_headers();
		
		if ( ! empty( $headers_check['missing_headers'] ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of missing headers */
					__( 'Missing %d critical security headers (clickjacking, XSS, MIME-sniffing protection)', 'wpshadow' ),
					count( $headers_check['missing_headers'] )
				),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/http-security-headers-configuration',
				'meta'         => array(
					'missing_headers' => $headers_check['missing_headers'],
					'present_headers' => $headers_check['present_headers'],
				),
			);
		}

		return null;
	}

	/**
	 * Check security headers.
	 *
	 * @since  1.26028.1905
	 * @return array Check results.
	 */
	private static function check_security_headers() {
		$check = array(
			'missing_headers' => array(),
			'present_headers' => array(),
		);

		// Get homepage response.
		$response = wp_remote_get( get_home_url(), array( 'timeout' => 10 ) );
		
		if ( is_wp_error( $response ) ) {
			return $check;
		}

		$headers = wp_remote_retrieve_headers( $response );

		// Critical security headers.
		$required_headers = array(
			'X-Frame-Options',
			'X-Content-Type-Options',
			'Content-Security-Policy',
			'X-XSS-Protection',
			'Referrer-Policy',
		);

		foreach ( $required_headers as $header ) {
			$header_lower = strtolower( str_replace( '-', '_', $header ) );
			
			if ( ! isset( $headers[ $header ] ) && ! isset( $headers[ $header_lower ] ) ) {
				$check['missing_headers'][] = $header;
			} else {
				$check['present_headers'][] = $header;
			}
		}

		return $check;
	}
}
