<?php
/**
 * Security Headers Implemented Treatment
 *
 * Tests if security headers are properly configured.
 *
 * @since   1.6050.0000
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Security Headers Implemented Treatment Class
 *
 * Checks the front page response headers for common security headers.
 *
 * @since 1.6050.0000
 */
class Treatment_Implements_Security_Headers extends Treatment_Base {

	protected static $slug = 'implements-security-headers';
	protected static $title = 'Security Headers Implemented';
	protected static $description = 'Tests if security headers are properly configured';
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6050.0000
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
				'kb_link'      => 'https://wpshadow.com/kb/security-headers',
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
			'kb_link'      => 'https://wpshadow.com/kb/security-headers',
			'persona'      => 'developer',
		);
	}
}
