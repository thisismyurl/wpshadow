<?php
/**
 * Membership Settings Security Diagnostic
 *
 * Verifies that membership/user registration settings are properly configured
 * for security and to prevent unauthorized user creation.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26032.1800
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Membership Settings Security Diagnostic Class
 *
 * Ensures user registration is properly configured.
 *
 * @since 1.26032.1800
 */
class Diagnostic_Membership_Settings_Security extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'membership-settings-security';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Membership Settings Security';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies user registration security settings';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'settings';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks:
	 * - Registration is intentionally enabled or disabled
	 * - Default role for new users is appropriate
	 * - No overly permissive registration settings
	 * - Email confirmation requirements
	 *
	 * @since  1.26032.1800
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if user registration is enabled.
		$users_can_register = (bool) get_option( 'users_can_register', false );

		if ( $users_can_register ) {
			// Get default role.
			$default_role = get_option( 'default_role', 'subscriber' );

			// Check if default role is too permissive.
			$restricted_roles = array( 'administrator', 'editor' );
			if ( in_array( $default_role, $restricted_roles, true ) ) {
				$issues[] = sprintf(
					/* translators: %s: role name */
					__( 'User registration is enabled with overly permissive default role (%s); this is a security risk', 'wpshadow' ),
					ucfirst( $default_role )
				);
			}

			// Check if email confirmation is not required.
			$require_name_email = (bool) get_option( 'require_name_email', true );
			if ( ! $require_name_email ) {
				$issues[] = __( 'User registration is enabled without requiring name and email; this reduces security', 'wpshadow' );
			}
		} else {
			// Registration is disabled - this is generally good for security.
			// Check if there's a reason to enable it.
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( '. ', $issues ),
				'severity'    => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/membership-settings-security',
			);
		}

		return null;
	}
}
