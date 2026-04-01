<?php
/**
 * No Account Lockout Protection Diagnostic
 *
 * Detects when account lockout is not configured,
 * leaving brute force attacks unprotected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Security
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: No Account Lockout Protection
 *
 * Checks whether account lockout is configured
 * to prevent brute force attacks.
 *
 * @since 0.6093.1200
 */
class Diagnostic_No_Account_Lockout_Protection extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-account-lockout-protection';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Account Lockout Protection';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether account lockout is configured';

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
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for account lockout plugins
		$has_lockout = is_plugin_active( 'wordfence-security/wordfence.php' ) ||
			is_plugin_active( 'ithemes-security-pro/ithemes-security-pro.php' ) ||
			is_plugin_active( 'wp-limit-login-attempts-reloaded/wp-limit-login-attempts-reloaded.php' );

		if ( ! $has_lockout ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'Account lockout isn\'t configured, which means brute force attacks can attempt unlimited passwords. Lockout strategy: after 5 failed login attempts, lock account for 30 minutes. This makes brute force pointless (would take centuries). Attackers get 0.0000000002 seconds per password guess, lockout makes it 30 minutes per 5 attempts. Best practice: limit attempts to 5, lockout 30-60 minutes, email notification when locked.',
					'wpshadow'
				),
				'severity'      => 'high',
				'threat_level'  => 75,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'Brute Force Attack Prevention',
					'potential_gain' => 'Make brute force mathematically impossible (30min after 5 attempts)',
					'roi_explanation' => 'Account lockout stops brute force attacks by making unlimited guessing impossible.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/account-lockout-protection?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
