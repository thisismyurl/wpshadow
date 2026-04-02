<?php
/**
 * No Two-Factor Authentication Diagnostic
 *
 * Detects when 2FA is not implemented,
 * leaving user accounts vulnerable to password-based attacks.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Security
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: No Two-Factor Authentication
 *
 * Checks whether two-factor authentication is available
 * for user account protection.
 *
 * @since 1.6093.1200
 */
class Diagnostic_No_Two_Factor_Authentication extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-two-factor-authentication';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'No Two-Factor Authentication';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether 2FA is available for user accounts';

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
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for 2FA plugins
		$has_2fa = is_plugin_active( 'two-factor/two-factor.php' ) ||
			is_plugin_active( 'wordfence-security/wordfence.php' ) ||
			is_plugin_active( 'jetpack/jetpack.php' );

		if ( ! $has_2fa ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'Two-factor authentication isn\'t enabled, which means passwords alone protect your accounts. 2FA is like having a door with two locks: someone needs both your password AND your phone (or authenticator app) to get in. Even if someone gets your password through a data breach or phishing, they can\'t access your account. 2FA reduces account compromise risk by 99.9%. It takes 5 minutes to set up and is the single most effective security improvement.',
					'wpshadow'
				),
				'severity'      => 'critical',
				'threat_level'  => 90,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'Account Security',
					'potential_gain' => '99.9% reduction in compromised accounts',
					'roi_explanation' => '2FA prevents 99.9% of account compromises. Setup takes 5 minutes. Single highest-impact security improvement.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/two-factor-authentication',
			);
		}

		return null;
	}
}
