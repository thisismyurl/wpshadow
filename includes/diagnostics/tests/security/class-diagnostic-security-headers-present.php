<?php
/**
 * Security Headers Present Diagnostic
 *
 * Checks whether the site sends essential HTTP security headers such as
 * X-Content-Type-Options, X-Frame-Options, and Referrer-Policy.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Security Headers Present Diagnostic Class
 *
 * Issues a HEAD request to the homepage and audits the response for the
 * presence of recommended HTTP security headers, reporting any that are absent.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Security_Headers_Present extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'security-headers-present';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Security Headers Present';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether the site sends essential HTTP security headers such as X-Content-Type-Options, X-Frame-Options, and Content-Security-Policy.';

	/**
	 * Gauge family/category for dashboard placement.
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * Issues a HEAD request to the homepage and inspects the returned headers
	 * for X-Content-Type-Options, X-Frame-Options, Referrer-Policy, and HSTS,
	 * returning a medium finding that lists any headers that are absent.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array when required headers are missing, null when healthy.
	 */
	public static function check() {
		$home_url = home_url( '/' );
		$response = wp_remote_head( $home_url, array(
			'timeout'    => 5,
			'user-agent' => 'WPShadow-Diagnostic/1.0',
			'sslverify'  => false,
		) );

		if ( is_wp_error( $response ) ) {
			return null; // Cannot test; skip to avoid false positives.
		}

		$headers      = wp_remote_retrieve_headers( $response );
		$is_https     = ( 'https' === wp_parse_url( $home_url, PHP_URL_SCHEME ) );
		$missing      = array();

		if ( empty( $headers->offsetGet( 'x-content-type-options' ) ) ) {
			$missing[] = 'X-Content-Type-Options';
		}

		if ( empty( $headers->offsetGet( 'x-frame-options' ) ) ) {
			// CSP with frame-ancestors is an acceptable substitute.
			$csp = $headers->offsetGet( 'content-security-policy' );
			if ( ! $csp || false === stripos( $csp, 'frame-ancestors' ) ) {
				$missing[] = 'X-Frame-Options';
			}
		}

		if ( empty( $headers->offsetGet( 'referrer-policy' ) ) ) {
			$missing[] = 'Referrer-Policy';
		}

		if ( $is_https && empty( $headers->offsetGet( 'strict-transport-security' ) ) ) {
			$missing[] = 'Strict-Transport-Security (HSTS)';
		}

		if ( empty( $missing ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: list of missing headers */
				__( 'The following recommended HTTP security headers are missing from the site\'s responses: %s. These headers protect visitors against clickjacking, MIME-type sniffing, and cross-site information leakage. Add them via your server configuration, a CDN, or a security/header plugin.', 'wpshadow' ),
				implode( ', ', $missing )
			),
			'severity'     => 'medium',
			'threat_level' => 45,
			'kb_link'      => 'https://wpshadow.com/kb/security-headers?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'      => array(
				'missing_headers' => $missing,
				'checked_url'     => $home_url,
			),
		);
	}
}
