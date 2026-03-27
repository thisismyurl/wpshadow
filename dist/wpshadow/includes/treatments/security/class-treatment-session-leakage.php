<?php
/**
 * Cross-Site Session Leakage Treatment
 *
 * Checks for session fixation via subdomain, domain isolation for cookies,
 * and CORS misconfiguration that could leak sessions.
 *
 * @package    WPShadow
 * @subpackage Treatments\Security
 * @since 1.6093.1200
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
 * Detects cookie and session configuration issues that could allow
 * cross-site session leakage or fixation attacks.
 *
 * @since 1.6093.1200
 */
class Treatment_Session_Leakage extends Treatment_Base {

	/**
	 * The treatment slug.
	 *
	 * @var string
	 */
	protected static $slug = 'prevents_session_leakage';

	/**
	 * The treatment title.
	 *
	 * @var string
	 */
	protected static $title = 'Cross-Site Session Leakage';

	/**
	 * The treatment description.
	 *
	 * @var string
	 */
	protected static $description = 'Verifies session cookies are properly isolated and protected from cross-site leakage';

	/**
	 * The family this treatment belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Session_Leakage' );
	}
}
