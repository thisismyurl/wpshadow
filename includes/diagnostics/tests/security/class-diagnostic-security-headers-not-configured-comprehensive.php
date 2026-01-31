<?php
/**
 * Security Headers Not Configured Comprehensive Diagnostic
 *
 * Checks if all security headers are configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Security Headers Not Configured Comprehensive Diagnostic Class
 *
 * Detects missing security headers.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Security_Headers_Not_Configured_Comprehensive extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'security-headers-not-configured-comprehensive';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Security Headers Not Configured Comprehensive';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if all security headers are configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for comprehensive security headers
		if ( ! has_filter( 'wp_headers', 'add_security_headers' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Security headers are not comprehensively configured. Add X-Frame-Options, X-Content-Type-Options, X-XSS-Protection, Strict-Transport-Security, and Content-Security-Policy headers.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 80,
				'auto_fixable'  => true,
				'kb_link'       => 'https://wpshadow.com/kb/security-headers-not-configured-comprehensive',
			);
		}

		return null;
	}
}
