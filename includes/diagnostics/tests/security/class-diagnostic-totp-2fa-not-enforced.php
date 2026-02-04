<?php
/**
 * TOTP 2FA Not Enforced Diagnostic
 *
 * Checks TOTP 2FA.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6033.2033
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_TOTP_2FA_Not_Enforced Class
 *
 * Performs diagnostic check for Totp 2fa Not Enforced.
 *
 * @since 1.6033.2033
 */
class Diagnostic_TOTP_2FA_Not_Enforced extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'totp-2fa-not-enforced';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'TOTP 2FA Not Enforced';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks TOTP 2FA';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.2033
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! get_option( 'totp_2fa_enforced' ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'TOTP 2FA not enforced for admin accounts. Use libraries like PHPGangsta_GoogleAuthenticator to enable time-based one-time passwords.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/totp-2fa-not-enforced',
				'context'      => array(
					'why'            => __( 'TOTP 2FA (Time-based One-Time Passwords) prevents account takeover via phishing, keyloggers, or credential theft. 92% of breaches involve compromised credentials. TOTP adds second factor - even stolen passwords don\'t grant access. Admin accounts especially vulnerable (higher value target). NIST recommends MFA for all accounts; US federal mandate requires FIPS 140-2 compliant MFA for government accounts.', 'wpshadow' ),
					'recommendation' => __( '1. Use PHPGangsta_GoogleAuthenticator library (well-maintained, secure QR generation). 2. Generate 32-byte random seed (bin2hex(random_bytes(32))). 3. Store user seeds encrypted in wp_usermeta. 4. Generate 6-digit TOTP from seed + current time window. 5. Validate TOTP allows ±30 second drift (time sync tolerance). 6. Enforce TOTP for wp-admin access before allowing admin actions. 7. Generate 10 backup codes (4-digit format) when TOTP enabled. 8. Hash backup codes (wp_hash_password()) in database. 9. Log TOTP enable/disable to activity log. 10. Implement grace period (72 hours) for existing admins to enable.', 'wpshadow' ),
				),
			);
		}

		return null;
	}
}
