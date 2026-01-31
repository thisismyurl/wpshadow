<?php
/**
 * User Registration Verification Not Enhanced Diagnostic
 *
 * Checks if user registration verification is enhanced.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2351
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * User Registration Verification Not Enhanced Diagnostic Class
 *
 * Detects unenhanced user verification.
 *
 * @since 1.2601.2351
 */
class Diagnostic_User_Registration_Verification_Not_Enhanced extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'user-registration-verification-not-enhanced';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'User Registration Verification Not Enhanced';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if user registration verification is enhanced';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2351
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if registration is open
		$users_can_register = get_option( 'users_can_register' );

		if ( $users_can_register ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'User registration verification is not enhanced. Enable email verification and implement CAPTCHA for user registrations.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 40,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/user-registration-verification-not-enhanced',
			);
		}

		return null;
	}
}
