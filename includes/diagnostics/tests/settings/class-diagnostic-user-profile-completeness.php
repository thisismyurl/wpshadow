<?php
/**
 * User Profile Completeness Diagnostic
 *
 * Validates that user profiles contain required fields and that key
 * profile data is complete for security and administrative purposes.
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
 * User Profile Completeness Diagnostic Class
 *
 * Checks for missing user profile data.
 *
 * @since 0.6093.1200
 */
class Diagnostic_User_Profile_Completeness extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'user-profile-completeness';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'User Profile Completeness';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates that user profile data is complete and usable';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		$users = get_users( array( 'fields' => array( 'ID', 'user_login', 'user_email', 'display_name' ) ) );

		if ( empty( $users ) ) {
			$issues[] = __( 'No users found (system error)', 'wpshadow' );
		}

		$missing_names  = array();
		$missing_emails = array();
		$missing_bio    = array();

		foreach ( $users as $user ) {
			if ( empty( $user->display_name ) ) {
				$missing_names[] = $user->user_login;
			}

			if ( empty( $user->user_email ) || ! is_email( $user->user_email ) ) {
				$missing_emails[] = $user->user_login;
			}

			$bio = get_user_meta( $user->ID, 'description', true );
			if ( empty( $bio ) ) {
				$missing_bio[] = $user->user_login;
			}
		}

		if ( ! empty( $missing_names ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of users */
				__( '%d user(s) missing display names', 'wpshadow' ),
				count( $missing_names )
			);
		}

		if ( ! empty( $missing_emails ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of users */
				__( '%d user(s) missing valid email addresses', 'wpshadow' ),
				count( $missing_emails )
			);
		}

		if ( ! empty( $missing_bio ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of users */
				__( '%d user(s) missing profile descriptions', 'wpshadow' ),
				count( $missing_bio )
			);
		}

		// Check for missing contact methods on admins.
		$admin_users = get_users( array( 'role' => 'administrator', 'fields' => array( 'ID', 'user_login' ) ) );
		$admin_missing_contact = array();

		foreach ( $admin_users as $admin ) {
			$phone = get_user_meta( $admin->ID, 'phone', true );
			if ( empty( $phone ) ) {
				$admin_missing_contact[] = $admin->user_login;
			}
		}

		if ( ! empty( $admin_missing_contact ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of admins */
				__( '%d administrator(s) missing phone/contact information', 'wpshadow' ),
				count( $admin_missing_contact )
			);
		}

		// Check for duplicate display names.
		$display_names = array();
		foreach ( $users as $user ) {
			if ( ! empty( $user->display_name ) ) {
				if ( isset( $display_names[ $user->display_name ] ) ) {
					$display_names[ $user->display_name ]++;
				} else {
					$display_names[ $user->display_name ] = 1;
				}
			}
		}

		$duplicates = array();
		foreach ( $display_names as $display_name => $count ) {
			if ( $count > 1 ) {
				$duplicates[] = $display_name;
			}
		}

		if ( ! empty( $duplicates ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of duplicates */
				__( '%d duplicate display names detected (can cause confusion)', 'wpshadow' ),
				count( $duplicates )
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of profile issues */
					__( 'Found %d user profile completeness issues.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'low',
				'threat_level' => 20,
				'auto_fixable' => false,
				'details'      => array(
					'issues'         => $issues,
					'user_count'     => count( $users ),
					'missing_names'  => count( $missing_names ),
					'missing_emails' => count( $missing_emails ),
					'missing_bio'    => count( $missing_bio ),
					'recommendation' => __( 'Ensure user profiles contain display names, valid emails, and essential contact details for admins.', 'wpshadow' ),
				),
			);
		}

		return null;
	}
}
