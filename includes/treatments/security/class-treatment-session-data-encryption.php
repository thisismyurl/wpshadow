<?php
/**
 * Session Data Encryption Treatment
 *
 * Detects unencrypted sensitive data in sessions and cookies
 * that could be exposed through cookie theft or session hijacking.
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
 * Session Data Encryption Treatment Class
 *
 * Checks for:
 * - Sensitive data stored in PHP sessions without encryption
 * - Cookie values containing sensitive information
 * - Session tokens stored in plaintext
 * - Personal data in auth cookies
 * - Credit card or API key data in sessions
 *
 * According to OWASP, sensitive data in sessions is a critical
 * vulnerability because sessions are often stored in shared hosting
 * environments or transmitted over insecure connections.
 *
 * @since 0.6093.1200
 */
class Treatment_Session_Data_Encryption extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @since 0.6093.1200
	 * @var   string
	 */
	protected static $slug = 'session-data-encryption';

	/**
	 * The treatment title
	 *
	 * @since 0.6093.1200
	 * @var   string
	 */
	protected static $title = 'Session Data Encryption';

	/**
	 * The treatment description
	 *
	 * @since 0.6093.1200
	 * @var   string
	 */
	protected static $description = 'Verifies sensitive data in sessions and cookies is encrypted';

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
	 * Analyzes session and cookie usage:
	 * 1. Checks session file permissions
	 * 2. Looks for sensitive data patterns in session vars
	 * 3. Validates cookie security flags
	 * 4. Checks for encryption of stored credentials
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Session_Data_Encryption' );
	}
}
