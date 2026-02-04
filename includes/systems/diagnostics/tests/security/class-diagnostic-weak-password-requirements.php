<?php
/**
 * Weak Password Requirements Diagnostic
 *
 * Detects when password requirements are not strict enough,
 * leaving accounts vulnerable to brute force attacks.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Security
 * @since      1.6035.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: Weak Password Requirements
 *
 * Checks whether strong password requirements are enforced
 * for user account security.
 *
 * @since 1.6035.2148
 */
class Diagnostic_Weak_Password_Requirements extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'weak-password-requirements';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Weak Password Requirements';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether strong password requirements are enforced';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Whether this diagnostic is auto-fixable
	 *
	 * @var bool
	 */
	protected static $auto_fixable = false;

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6035.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for strong password enforcement plugins
		$has_password_enforcement = is_plugin_active( 'force-strong-passwords/force-strong-passwords.php' ) ||
			is_plugin_active( 'advanced-access-manager/advanced-access-manager.php' );

		// Check if custom password rules exist
		$has_custom_password_rules = get_option( 'wpshadow_strong_password_requirements' );

		// Check default WordPress settings (allows weak passwords)
		if ( ! $has_password_enforcement && ! $has_custom_password_rules ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'WordPress allows weak passwords by default, which is like having a house with a lock that opens to "password". Strong passwords (12+ characters, mixed case, numbers, symbols) are exponentially harder to crack: a 6-character password takes minutes, an 8-character password hours, a 12-character password years. Force all users to use strong passwords—especially admin accounts, which are the crown jewels.',
					'wpshadow'
				),
				'severity'      => 'high',
				'threat_level'  => 70,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'Account Security',
					'potential_gain' => 'Prevent account compromise',
					'roi_explanation' => 'Strong password requirements reduce account takeover risk by 99.9%, preventing access loss and data breaches.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/strong-password-requirements',
			);
		}

		return null;
	}
}
