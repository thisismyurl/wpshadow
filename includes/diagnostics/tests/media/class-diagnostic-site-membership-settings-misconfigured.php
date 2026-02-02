<?php
/**
 * Site Membership Settings Misconfigured Diagnostic
 *
 * Tests for membership and user registration settings.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26033.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Site Membership Settings Misconfigured Diagnostic Class
 *
 * Tests for membership and user registration settings.
 *
 * @since 1.26033.0000
 */
class Diagnostic_Site_Membership_Settings_Misconfigured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'site-membership-settings-misconfigured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Site Membership Settings Misconfigured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests for membership and user registration settings';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26033.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if user registration is enabled (may be intentional).
		$users_can_register = get_option( 'users_can_register' );

		if ( empty( $users_can_register ) ) {
			$issues[] = __( 'User registration is disabled', 'wpshadow' );
		}

		// Check default role for new users.
		$default_role = get_option( 'default_role' );

		if ( empty( $default_role ) ) {
			$issues[] = __( 'No default role set for new users', 'wpshadow' );
		} elseif ( $default_role === 'administrator' ) {
			$issues[] = __( 'New users are automatically assigned Administrator role - security risk', 'wpshadow' );
		}

		// Check for contributor role (may be safer for public registration).
		$all_roles = wp_roles()->get_names();

		if ( empty( $all_roles ) ) {
			$issues[] = __( 'No user roles configured', 'wpshadow' );
		}

		// Check if user registration form is exposed (optional).
		if ( $users_can_register ) {
			$registration_url = wp_registration_url();
			if ( ! empty( $registration_url ) ) {
				$issues[] = __( 'Registration form is publicly accessible', 'wpshadow' );
			}
		}

		// Check for email confirmation on registration.
		$require_email_confirmation = get_option( '_wpshadow_require_email_confirmation' );

		if ( empty( $require_email_confirmation ) && $users_can_register ) {
			$issues[] = __( 'Email confirmation not required for new registrations', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'low',
				'threat_level' => 35,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/site-membership-settings-misconfigured',
			);
		}

		return null;
	}
}
