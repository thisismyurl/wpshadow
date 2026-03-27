<?php
/**
 * Input Validation Client and Server Treatment
 *
 * Issue #4881: Input Validation Only on Client Side (JavaScript Can Be Bypassed)
 * Pillar: 🛡️ Safe by Default
 *
 * Checks if input validation happens on both client and server.
 * Client-side validation is UX. Server-side validation is security.
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
 * Treatment_Input_Validation_Client_Server Class
 *
 * Checks for:
 * - Server-side validation on ALL inputs
 * - Client-side validation for immediate UX feedback
 * - Validation rules match on client and server
 * - No reliance on client-side validation alone
 * - Type validation (email, URL, integer, etc)
 * - Length validation (min/max characters)
 * - Format validation (regex patterns)
 * - Sanitization after validation
 *
 * Why this matters:
 * - Attackers disable JavaScript or modify requests
 * - Client-side validation is for UX only
 * - Server-side validation prevents injection attacks
 * - Inconsistent validation creates security holes
 *
 * @since 1.6093.1200
 */
class Treatment_Input_Validation_Client_Server extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $slug = 'input-validation-client-server';

	/**
	 * The treatment title
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $title = 'Input Validation Only on Client Side (JavaScript Can Be Bypassed)';

	/**
	 * The treatment description
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $description = 'Checks if input validation happens on both client (UX) and server (security)';

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
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Input_Validation_Client_Server' );
	}
}
