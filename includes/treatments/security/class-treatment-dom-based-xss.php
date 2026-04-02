<?php
/**
 * DOM-Based XSS Treatment
 *
 * Detects DOM-based XSS vulnerabilities where JavaScript code
 * unsafely manipulates the DOM with user-controllable data.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DOM-Based XSS Treatment Class
 *
 * Checks for:
 * - innerHTML usage with user-controllable data
 * - Unsafe jQuery methods (.html(), .append() with unsafe data)
 * - document.write() with URL parameters
 * - eval() with user input
 * - Event handler attributes set dynamically
 * - location.href manipulation
 *
 * DOM XSS differs from traditional XSS because the vulnerability
 * exists entirely in client-side code. The server never sees the
 * malicious payload, making it harder to detect and prevent.
 *
 * @since 1.6093.1200
 */
class Treatment_DOM_Based_XSS extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $slug = 'dom-based-xss';

	/**
	 * The treatment title
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $title = 'DOM-Based XSS Vulnerability';

	/**
	 * The treatment description
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $description = 'Detects DOM-based XSS vulnerabilities in JavaScript code';

	/**
	 * The family this treatment belongs to
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * Scans JavaScript files for dangerous DOM manipulation patterns.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_DOM_Based_XSS' );
	}
}
