<?php
/**
 * Two-Factor Authentication For Admin Not Required Diagnostic
 *
 * Checks if admin 2FA is required.
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
 * Two-Factor Authentication For Admin Not Required Diagnostic Class
 *
 * Detects missing admin 2FA requirement.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Two_Factor_Authentication_For_Admin_Not_Required extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'two-factor-authentication-for-admin-not-required';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Two-Factor Authentication For Admin Not Required';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if admin 2FA is required';

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
		// Check if 2FA is required for admins
		if ( ! get_option( 'require_admin_2fa' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Two-factor authentication for admin is not required. Enable 2FA for all administrator accounts to prevent unauthorized access.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 70,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/two-factor-authentication-for-admin-not-required',
			);
		}

		return null;
	}
}
