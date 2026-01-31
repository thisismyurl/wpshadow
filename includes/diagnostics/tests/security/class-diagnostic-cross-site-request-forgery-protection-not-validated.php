<?php
/**
 * Cross-Site Request Forgery Protection Not Validated Diagnostic
 *
 * Checks if CSRF protection is validated.
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
 * Cross-Site Request Forgery Protection Not Validated Diagnostic Class
 *
 * Detects unvalidated CSRF protection.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Cross_Site_Request_Forgery_Protection_Not_Validated extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'cross-site-request-forgery-protection-not-validated';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Cross-Site Request Forgery Protection Not Validated';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if CSRF protection is validated';

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
		// Check if nonce validation is implemented
		if ( ! has_filter( 'wp_ajax_nopriv', 'validate_csrf_token' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'CSRF protection is not validated. Verify that all forms and AJAX requests use WordPress nonces to prevent unauthorized requests.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 70,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/cross-site-request-forgery-protection-not-validated',
			);
		}

		return null;
	}
}
