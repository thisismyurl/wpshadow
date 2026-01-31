<?php
/**
 * Password Strength Requirements Not Enforced Diagnostic
 *
 * Checks if password strength is enforced.
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
 * Password Strength Requirements Not Enforced Diagnostic Class
 *
 * Detects missing password strength enforcement.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Password_Strength_Requirements_Not_Enforced extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'password-strength-requirements-not-enforced';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Password Strength Requirements Not Enforced';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if password strength is enforced';

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
		// Check if password strength filter exists
		if ( ! has_filter( 'wp_check_password', 'wp_check_password_strength' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Password strength requirements are not enforced. Use a password strength plugin to require strong passwords for all users.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 40,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/password-strength-requirements-not-enforced',
			);
		}

		return null;
	}
}
