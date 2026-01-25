<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Diagnostic_Lean_Checks;

/**
 * Diagnostic: User Profile Completion
 *
 * Category: Users & Team
 * Priority: 2
 * Philosophy: 1
 *
 * Test Description:
 * What percentage of user profile fields are filled?
 *
 * @package WPShadow
 * @subpackage Diagnostics
 *
 * @verified 2026-01-24 - Batch 5 implementation
 * @guardian-integrated Pending
 */
class Diagnostic_Users_Profile_Completion_Overall extends Diagnostic_Base {
	protected static $slug         = 'users-profile-completion-overall';
	protected static $title        = 'User Profile Completion';
	protected static $description  = 'What percentage of user profile fields are filled?';
	protected static $category     = 'Users & Team';
	protected static $threat_level = 'low';
	protected static $family       = 'general';
	protected static $family_label = 'General';

	/**
	 * Run the diagnostic check
	 *
	 * @return ?array Null if pass, array of findings if fail
	 */
	public function check(): ?array {
		// Get all users
		$users = get_users( array( 'fields' => 'ID' ) );

		if ( empty( $users ) ) {
			return null;
		}

		$total_completion = 0;
		$profile_fields   = array( 'user_email', 'user_url', 'description', 'first_name', 'last_name' );

		foreach ( $users as $user_id ) {
			$user          = get_userdata( $user_id );
			$filled_fields = 0;

			// Check email
			if ( ! empty( $user->user_email ) && 'noreply@example.com' !== $user->user_email ) {
				++$filled_fields;
			}

			// Check URL
			if ( ! empty( $user->user_url ) ) {
				++$filled_fields;
			}

			// Check bio
			if ( ! empty( $user->description ) ) {
				++$filled_fields;
			}

			// Check first name
			if ( ! empty( $user->first_name ) ) {
				++$filled_fields;
			}

			// Check last name
			if ( ! empty( $user->last_name ) ) {
				++$filled_fields;
			}

			$user_completion   = ( $filled_fields / count( $profile_fields ) ) * 100;
			$total_completion += $user_completion;
		}

		$average_completion = $total_completion / count( $users );

		if ( $average_completion < 50 ) {
			return Diagnostic_Lean_Checks::build_finding(
				'users-profile-completion-overall',
				'User Profiles Incomplete',
				sprintf( 'Average user profile completion is %.0f%%. Encourage team members to complete their profiles for better collaboration.', $average_completion ),
				'Users & Team',
				'low',
				'low'
			);
		}

		return null;
	}
}
