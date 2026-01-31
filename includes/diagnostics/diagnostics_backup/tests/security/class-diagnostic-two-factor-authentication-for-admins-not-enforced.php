<?php
/**
 * Two Factor Authentication For Admins Not Enforced Diagnostic
 *
 * Checks if 2FA is enforced for admins.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Two Factor Authentication For Admins Not Enforced Diagnostic Class
 *
 * Detects missing 2FA enforcement.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Two_Factor_Authentication_For_Admins_Not_Enforced extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'two-factor-authentication-for-admins-not-enforced';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Two Factor Authentication For Admins Not Enforced';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if 2FA is enforced for admins';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if 2FA plugin is active
		if ( ! is_plugin_active( 'two-factor/two-factor.php' ) && ! is_plugin_active( 'wordfence/wordfence.php' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Two-factor authentication is not enforced for administrators. Enable 2FA to prevent unauthorized access to admin accounts.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 70,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/two-factor-authentication-for-admins-not-enforced',
			);
		}

		return null;
	}
}
