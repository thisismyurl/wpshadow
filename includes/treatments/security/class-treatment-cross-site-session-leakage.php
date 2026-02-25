<?php
/**
 * Cross-Site Session Leakage Treatment
 *
 * Detects session data leakage across different domains, subdomains,
 * or security contexts.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.2033.2108
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cross-Site Session Leakage Treatment Class
 *
 * Checks for:
 * - Cookie domain scope too broad
 * - Session cookies shared across subdomains
 * - SameSite attribute not set
 * - CORS headers exposing credentials
 * - Session data in cross-origin requests
 * - Referer header leaking session data
 *
 * Cross-site session leakage exposes authentication tokens and
 * sensitive session data to untrusted domains.
 *
 * @since 1.2033.2108
 */
class Treatment_Cross_Site_Session_Leakage extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @since 1.2033.2108
	 * @var   string
	 */
	protected static $slug = 'cross-site-session-leakage';

	/**
	 * The treatment title
	 *
	 * @since 1.2033.2108
	 * @var   string
	 */
	protected static $title = 'Cross-Site Session Leakage';

	/**
	 * The treatment description
	 *
	 * @since 1.2033.2108
	 * @var   string
	 */
	protected static $description = 'Detects session data leakage to cross-origin contexts';

	/**
	 * The family this treatment belongs to
	 *
	 * @since 1.2033.2108
	 * @var   string
	 */
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * Validates cross-site session security.
	 *
	 * @since  1.2033.2108
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Cross_Site_Session_Leakage' );
	}
}
