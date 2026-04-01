<?php
/**
 * Security Headers Implemented Diagnostic
 *
 * Tests if security headers are properly configured.
 *
 * @since 0.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Security Headers Implemented Diagnostic Class
 *
 * Checks the front page response headers for common security headers.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Implements_Security_Headers extends Diagnostic_Base {

	protected static $slug = 'implements-security-headers';
	protected static $title = 'Security Headers Implemented';
	protected static $description = 'Tests if security headers are properly configured';
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$headers = wp_remote_retrieve_headers( wp_remote_head( home_url( '/' ) ) );
		if ( is_wp_error( $headers ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Unable to check headers. Ensure the site is reachable and try again.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/security-headers?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'persona'      => 'developer',
			);
		}

		$required = array(
			'x-frame-options',
			'x-content-type-options',
			'referrer-policy',
		);

		$missing = array();
		foreach ( $required as $header ) {
			if ( empty( $headers[ $header ] ) ) {
				$missing[] = $header;
			}
		}

		if ( empty( $missing ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: missing headers */
				__( 'Missing security headers: %s. Add these headers to improve browser-level protection.', 'wpshadow' ),
				implode( ', ', $missing )
			),
			'severity'     => 'medium',
			'threat_level' => 45,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/security-headers?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'persona'      => 'developer',
		);
	}
}
