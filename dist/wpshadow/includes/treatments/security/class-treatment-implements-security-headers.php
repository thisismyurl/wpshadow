<?php
/**
 * Security Headers Implemented Treatment
 *
 * Tests if security headers are properly configured.
 *
 * @since 0.6093.1200
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Security Headers Implemented Treatment Class
 *
 * Checks the front page response headers for common security headers.
 *
 * @since 0.6093.1200
 */
class Treatment_Implements_Security_Headers extends Treatment_Base {

	protected static $slug = 'implements-security-headers';
	protected static $title = 'Security Headers Implemented';
	protected static $description = 'Tests if security headers are properly configured';
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Implements_Security_Headers' );
	}
}
