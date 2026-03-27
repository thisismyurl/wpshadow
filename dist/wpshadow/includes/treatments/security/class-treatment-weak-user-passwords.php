<?php
/**
 * Weak User Passwords Treatment
 *
 * Identifies users with weak or compromised passwords using available
 * password strength indicators and security plugins.
 * Weak passwords = #1 cause of account compromise.
 * "password123" cracked in seconds. Strong passwords = years to crack.
 *
 * **What This Check Does:**
 * - Scans all user accounts for password strength
 * - Checks against common password lists (rockyou.txt, etc)
 * - Validates minimum password requirements enforced
 * - Tests for dictionary words in passwords
 * - Checks password complexity rules
 * - Returns severity for each weak password found
 *
 * **Why This Matters:**
 * Weak password = attacker cracks in minutes via brute force.
 * Strong password (16+ chars, mixed case, symbols) = impractical to crack.
 * Password strength = first line of defense.
 *
 * **Business Impact:**
 * Admin account has password "admin123". Brute force attack tries
 * 100K common passwords. Finds match in 30 minutes. Site compromised.
 * Cost: $500K+. With strong password ("Tr0ub4dor&3XqR9#mK"):
 * brute force takes 1000+ years. Attack abandoned. Site safe.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Passwords meet strength standards
 * - #9 Show Value: Prevents password-based compromises
 * - #10 Beyond Pure: Proactive password auditing
 *
 * **Related Checks:**
 * - 2FA Required (complementary defense)
 * - Login Attempt Limiting (brute force protection)
 * - Password Reset Process Security (related)
 *
 * **Learn More:**
 * Password security: https://wpshadow.com/kb/password-security
 * Video: Creating strong passwords (11min): https://wpshadow.com/training/passwords
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Weak User Passwords Treatment Class
 *
 * Checks for weak password indicators.
 *
 * **Detection Pattern:**
 * 1. Get all user accounts
 * 2. Check if password strength plugin active
 * 3. Test passwords against common password list
 * 4. Validate minimum length (12+ chars)
 * 5. Check complexity requirements
 * 6. Return users with weak passwords
 *
 * **Real-World Scenario:**
 * Security scan finds 3 admin accounts with passwords in top 1000
 * common list ("password", "qwerty123", "welcome1"). Admin forced
 * to reset all 3. New passwords: 16+ chars, mixed case, symbols.
 * Brute force risk eliminated.
 *
 * **Implementation Notes:**
 * - Checks password strength configuration
 * - Validates against common password databases
 * - Tests complexity requirements
 * - Severity: critical (admin weak password), high (user weak)
 * - Treatment: force password reset with strength requirements
 *
 * @since 1.6093.1200
 */
class Treatment_Weak_User_Passwords extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'weak-user-passwords';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Weak User Passwords';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Identifies weak password indicators for user accounts';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Weak_User_Passwords' );
	}
}
