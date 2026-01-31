<?php
/**
 * User Two Factor Authentication Not Enforced Diagnostic
 *
 * Checks if two-factor authentication is enforced.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2310
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * User Two Factor Authentication Not Enforced Diagnostic Class
 *
 * Detects missing 2FA enforcement.
 *
 * @since 1.2601.2310
 */
class Diagnostic_User_Two_Factor_Authentication_Not_Enforced extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'user-two-factor-authentication-not-enforced';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'User Two Factor Authentication Not Enforced';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if 2FA is enforced';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2310
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for 2FA plugins
		$twofa_plugins = array(
			'two-factor/two-factor.php',
			'wordfence/wordfence.php',
			'iThemes-Security-Pro/iThemes-Security-Pro.php',
		);

		$twofa_active = false;
		foreach ( $twofa_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$twofa_active = true;
				break;
			}
		}

		if ( ! $twofa_active ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Two-factor authentication is not enforced. Enable 2FA for admin accounts to prevent unauthorized access.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 75,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/user-two-factor-authentication-not-enforced',
			);
		}

		return null;
	}
}
