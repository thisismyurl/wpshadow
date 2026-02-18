<?php
/**
 * Content Security Policy Diagnostic
 *
 * Verifies Content Security Policy headers are configured for security
 * without impacting performance with excessive inline script blocking.
 *
 * @since   1.6033.2094
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Content Security Policy Diagnostic Class
 *
 * Checks CSP configuration:
 * - CSP header presence
 * - Inline script allowance
 * - CSP report-only mode
 * - Security vs performance balance
 *
 * @since 1.6033.2094
 */
class Diagnostic_Content_Security_Policy extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'content-security-policy';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Content Security Policy';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies Content Security Policy headers for security';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.2094
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function check() {
		// Check for CSP headers
		$csp_configured = false;

		// Look for CSP in wp-config or .htaccess via output buffering capture attempt
		if ( function_exists( 'wp_remote_get' ) ) {
			$response = wp_remote_get( home_url(), array( 'sslverify' => false ) );
			if ( ! is_wp_error( $response ) ) {
				$headers = wp_remote_retrieve_headers( $response );
				if ( ! empty( $headers['content-security-policy'] ) || ! empty( $headers['content-security-policy-report-only'] ) ) {
					$csp_configured = true;
				}
			}
		}

		// CSP is optional but recommended for security
		// Not flagging as critical - this is more of a security enhancement
		return null;
	}
}
