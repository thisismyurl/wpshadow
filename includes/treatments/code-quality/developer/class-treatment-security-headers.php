<?php
/**
 * Security Headers Treatment
 *
 * Checks if proper HTTP security headers are configured.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Security Headers Treatment Class
 *
 * Verifies that proper HTTP security headers are configured including
 * X-Frame-Options, X-Content-Type-Options, and CSP.
 *
 * @since 0.6093.1200
 */
class Treatment_Security_Headers extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'security-headers';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'HTTP Security Headers';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if proper HTTP security headers are configured';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'developer';

	/**
	 * Run the security headers treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if header issues detected, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Security_Headers' );
	}
}
