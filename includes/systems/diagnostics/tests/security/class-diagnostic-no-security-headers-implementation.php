<?php
/**
 * No Security Headers Implementation Diagnostic
 *
 * Detects when security headers are missing,
 * leaving site vulnerable to XSS and clickjacking.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Security
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: No Security Headers Implementation
 *
 * Checks whether security headers are configured
 * for XSS and clickjacking protection.
 *
 * @since 1.6093.1200
 */
class Diagnostic_No_Security_Headers_Implementation extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-security-headers-implementation';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Security Headers Implementation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether security headers are set';

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
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check homepage headers
		$homepage = wp_remote_head( home_url() );
		if ( is_wp_error( $homepage ) ) {
			return null;
		}

		$headers = wp_remote_retrieve_headers( $homepage );
		
		// Check critical security headers
		$missing_headers = array();
		
		if ( ! isset( $headers['x-frame-options'] ) ) {
			$missing_headers[] = 'X-Frame-Options';
		}
		if ( ! isset( $headers['x-content-type-options'] ) ) {
			$missing_headers[] = 'X-Content-Type-Options';
		}
		if ( ! isset( $headers['x-xss-protection'] ) ) {
			$missing_headers[] = 'X-XSS-Protection';
		}
		if ( ! isset( $headers['content-security-policy'] ) ) {
			$missing_headers[] = 'Content-Security-Policy';
		}

		if ( count( $missing_headers ) > 0 ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					__(
						'Your site is missing %d security headers, which leaves you vulnerable. Missing: %s. What they do: X-Frame-Options prevents clickjacking (embedding your site in iframe to trick users), X-Content-Type-Options prevents MIME sniffing attacks, X-XSS-Protection enables browser XSS filters, Content-Security-Policy prevents XSS and data injection. Add headers in .htaccess, PHP, or via security plugin.',
						'wpshadow'
					),
					count( $missing_headers ),
					implode( ', ', $missing_headers )
				),
				'severity'      => 'high',
				'threat_level'  => 70,
				'auto_fixable'  => false,
				'missing_headers' => $missing_headers,
				'business_impact' => array(
					'metric'         => 'XSS & Clickjacking Protection',
					'potential_gain' => 'Block common browser-based attacks',
					'roi_explanation' => 'Security headers provide free browser-level protection against XSS, clickjacking, and MIME sniffing attacks.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/security-headers-implementation',
			);
		}

		return null;
	}
}
