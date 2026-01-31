<?php
/**
 * Cross Site Request Forgery Protection Not Verified Diagnostic
 *
 * Checks if CSRF protection is verified.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2348
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cross Site Request Forgery Protection Not Verified Diagnostic Class
 *
 * Detects unverified CSRF protection.
 *
 * @since 1.2601.2348
 */
class Diagnostic_Cross_Site_Request_Forgery_Protection_Not_Verified extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'cross-site-request-forgery-protection-not-verified';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Cross Site Request Forgery Protection Not Verified';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if CSRF protection is verified';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2348
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for CSRF filter (nonce verification)
		if ( ! has_filter( 'wp_verify_nonce', 'wp_verify_nonce' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'CSRF protection is not verified. Add nonce verification to all forms to prevent cross-site attacks.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 70,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/cross-site-request-forgery-protection-not-verified',
			);
		}

		return null;
	}
}
