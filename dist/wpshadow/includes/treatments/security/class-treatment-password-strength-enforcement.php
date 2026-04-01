<?php
/**
 * Password Strength Enforcement Treatment
 *
 * Issue #4886: No Minimum Password Strength Requirements
 * Pillar: 🛡️ Safe by Default
 *
 * Checks if password strength is enforced on user accounts.
 * Weak passwords lead to account compromise and brute force success.
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
 * Treatment_Password_Strength_Enforcement Class
 *
 * Checks for:
 * - Minimum password length (12+ characters recommended)
 * - Password complexity requirements (uppercase, lowercase, numbers, symbols)
 * - Password strength meter visible
 * - Prevents common passwords (password123, qwerty)
 * - Forces password change on first login
 * - Password history (prevent reuse of last 5)
 * - Password expiration policy (90-180 days)
 * - Admin accounts require stronger passwords
 *
 * @since 0.6093.1200
 */
class Treatment_Password_Strength_Enforcement extends Treatment_Base {

	protected static $slug = 'password-strength-enforcement';
	protected static $title = 'No Minimum Password Strength Requirements';
	protected static $description = 'Checks if password strength is enforced to prevent weak passwords';
	protected static $family = 'security';

	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Password_Strength_Enforcement' );
	}
}
