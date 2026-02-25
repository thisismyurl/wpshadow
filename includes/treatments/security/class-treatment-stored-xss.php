<?php
/**
 * Stored XSS Treatment
 *
 * Detects potential stored (persistent) XSS vulnerabilities where
 * malicious scripts are saved to the database and executed when viewed.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.2033.2102
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Stored XSS Treatment Class
 *
 * Checks for:
 * - Unescaped output in post content rendering
 * - Comment display without proper sanitization
 * - Custom field output without escaping
 * - User profile field rendering
 * - Plugin/theme code that echoes database content unsafely
 *
 * Stored XSS is more dangerous than reflected XSS because the
 * malicious script persists in the database and affects all users
 * who view the content, not just the victim who clicked a link.
 *
 * @since 1.2033.2102
 */
class Treatment_Stored_XSS extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @since 1.2033.2102
	 * @var   string
	 */
	protected static $slug = 'stored-xss';

	/**
	 * The treatment title
	 *
	 * @since 1.2033.2102
	 * @var   string
	 */
	protected static $title = 'Stored XSS Vulnerability';

	/**
	 * The treatment description
	 *
	 * @since 1.2033.2102
	 * @var   string
	 */
	protected static $description = 'Detects potential stored (persistent) XSS vulnerabilities';

	/**
	 * The family this treatment belongs to
	 *
	 * @since 1.2033.2102
	 * @var   string
	 */
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * Scans theme and plugin code for patterns that indicate
	 * potential stored XSS vulnerabilities.
	 *
	 * @since  1.2033.2102
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Stored_XSS' );
	}
}
