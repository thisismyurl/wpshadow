<?php
/**
 * Reflected XSS Treatment
 *
 * Detects potential reflected XSS vulnerabilities where user input
 * from URLs or forms is echoed back without proper sanitization.
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
 * Reflected XSS Treatment Class
 *
 * Checks for:
 * - $_GET parameters echoed without escaping
 * - Search functionality output without sanitization
 * - Error message output containing user input
 * - URL parameters reflected in page content
 * - Form field values output unsafely
 *
 * Reflected XSS accounts for approximately 75% of all XSS attacks.
 * According to Acunetix, XSS vulnerabilities are found in 53% of
 * web applications tested.
 *
 * @since 0.6093.1200
 */
class Treatment_Reflected_XSS extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @since 0.6093.1200
	 * @var   string
	 */
	protected static $slug = 'reflected-xss';

	/**
	 * The treatment title
	 *
	 * @since 0.6093.1200
	 * @var   string
	 */
	protected static $title = 'Reflected XSS Vulnerability';

	/**
	 * The treatment description
	 *
	 * @since 0.6093.1200
	 * @var   string
	 */
	protected static $description = 'Detects potential reflected (non-persistent) XSS vulnerabilities';

	/**
	 * The family this treatment belongs to
	 *
	 * @since 0.6093.1200
	 * @var   string
	 */
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * Scans theme and plugin code for patterns indicating
	 * reflected XSS vulnerabilities.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Reflected_XSS' );
	}
}
