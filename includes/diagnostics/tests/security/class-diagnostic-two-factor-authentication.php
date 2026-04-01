<?php
/**
 * Two-Factor Authentication Diagnostic
 *
 * Issue #4887: No Two-Factor Authentication Option
 * Pillar: 🛡️ Safe by Default
 *
 * Checks if 2FA is available for user accounts.
 * 2FA prevents account compromise even when password is stolen.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Two_Factor_Authentication Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_Two_Factor_Authentication extends Diagnostic_Base {

	protected static $slug = 'two-factor-authentication';
	protected static $title = 'No Two-Factor Authentication Option';
	protected static $description = 'Checks if 2FA is available to protect accounts';
	protected static $family = 'security';

	public static function check() {
		// Check if popular 2FA plugins are active
		$has_2fa = false;

		if ( is_plugin_active( 'two-factor/two-factor.php' ) ||
		     is_plugin_active( 'google-authenticator/google-authenticator.php' ) ||
		     is_plugin_active( 'wordfence/wordfence.php' ) ) {
			$has_2fa = true;
		}

		if ( ! $has_2fa ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Two-Factor Authentication adds a second layer of security. Even if passwords are stolen, attackers cannot access accounts without the second factor.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 80,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/two-factor-authentication?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'2fa_types'               => 'TOTP (Google Authenticator), SMS, Email, Backup codes',
					'account_takeover_stats'  => '99.9% of account takeovers prevented by 2FA (Microsoft)',
					'recommended_plugins'     => 'Two-Factor, Wordfence, iThemes Security',
				),
			);
		}

		return null;
	}
}
