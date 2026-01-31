<?php
/**
 * XSS Content Security Policy Not Configured Diagnostic
 *
 * Checks if CSP is configured.
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
 * XSS Content Security Policy Not Configured Diagnostic Class
 *
 * Detects missing CSP headers.
 *
 * @since 1.2601.2352
 */
class Diagnostic_XSS_Content_Security_Policy_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'xss-content-security-policy-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'XSS Content Security Policy Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if CSP is configured';

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
		// Check if CSP headers are set
		if ( ! has_action( 'send_headers', 'send_csp_headers' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Content Security Policy is not configured. Add CSP headers to prevent cross-site scripting (XSS) attacks.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 65,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/xss-content-security-policy-not-configured',
			);
		}

		return null;
	}
}
