<?php
/**
 * Missing Security Headers Diagnostic
 *
 * Detects when essential security headers are not configured,
 * leaving the site vulnerable to common web attacks.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Security
 * @since      1.6035.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: Missing Security Headers
 *
 * Checks whether critical security headers are configured
 * to protect against XSS, clickjacking, and other attacks.
 *
 * @since 1.6035.2148
 */
class Diagnostic_Missing_Security_Headers extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'missing-security-headers';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Missing Security Headers';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether security headers are configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Whether this diagnostic is auto-fixable
	 *
	 * @var bool
	 */
	protected static $auto_fixable = false;

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6035.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check headers on homepage
		$homepage = wp_remote_head( home_url() );
		if ( is_wp_error( $homepage ) ) {
			return null; // Can't check
		}

		$headers = wp_remote_retrieve_headers( $homepage );
		$missing_headers = array();

		// Check for critical security headers
		$required_headers = array(
			'X-Content-Type-Options',
			'X-Frame-Options',
			'X-XSS-Protection',
			'Strict-Transport-Security',
		);

		foreach ( $required_headers as $header ) {
			if ( ! isset( $headers[ strtolower( $header ) ] ) ) {
				$missing_headers[] = $header;
			}
		}

		if ( ! empty( $missing_headers ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					__(
						'Your site is missing important security headers: %s. Think of security headers as instructions to browsers to protect against specific attacks. For example, X-Content-Type-Options prevents browsers from guessing file types (blocking a sneaky attack technique), X-Frame-Options prevents your site from being loaded in frames (stopping clickjacking), and Strict-Transport-Security forces HTTPS (protecting credentials). These are quick wins that significantly improve security.',
						'wpshadow'
					),
					implode( ', ', $missing_headers )
				),
				'severity'      => 'high',
				'threat_level'  => 70,
				'auto_fixable'  => false,
				'missing_headers' => $missing_headers,
				'business_impact' => array(
					'metric'         => 'Attack Surface Reduction',
					'potential_gain' => 'Prevent common browser-based attacks',
					'roi_explanation' => 'Security headers are the easiest security improvement with the highest impact. Each header prevents specific attack vectors.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/security-headers',
			);
		}

		return null;
	}
}
