<?php
/**
 * User Registration Moderation Not Configured Diagnostic
 *
 * Checks if user registration moderation is configured.
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
 * User Registration Moderation Not Configured Diagnostic Class
 *
 * Detects unmoderated user registration.
 *
 * @since 1.2601.2352
 */
class Diagnostic_User_Registration_Moderation_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'user-registration-moderation-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'User Registration Moderation Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if user registration moderation is configured';

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
		// Check if user registration requires approval
		$moderation_enabled = get_option( 'users_can_register' );

		if ( $moderation_enabled && ! get_option( 'require_admin_approval_for_registration' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'User registration moderation is not configured. Enable admin approval for new user registrations to control access.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 40,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/user-registration-moderation-not-configured',
			);
		}

		return null;
	}
}
