<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Diagnostic_Lean_Checks;

/**
 * Diagnostic: User Bio/Description Completion
 *
 * Category: Users & Team
 * Priority: 2
 * Philosophy: 1
 *
 * Test Description:
 * What percentage of users have filled in their bio?
 *
 * @package WPShadow
 * @subpackage Diagnostics
 *
 * @verified 2026-01-24 - Batch 5 implementation
 * @guardian-integrated Pending
 */
class Diagnostic_Users_Bio_Completion extends Diagnostic_Base {
	protected static $slug = 'users-bio-completion';
	protected static $title = 'User Bio Completion';
	protected static $description = 'What percentage of users have filled in their bio?';
	protected static $category = 'Users & Team';
	protected static $threat_level = 'low';
	protected static $family = 'general';
	protected static $family_label = 'General';

	/**
	 * Run the diagnostic check
	 *
	 * @return ?array Null if pass, array of findings if fail
	 */
	public function check(): ?array {
		// Get all users
		$users = get_users( [ 'fields' => 'ID' ] );

		if ( empty( $users ) ) {
			return null;
		}

		$users_with_bio = 0;

		foreach ( $users as $user_id ) {
			$user = get_userdata( $user_id );
			if ( ! empty( $user->description ) && strlen( trim( $user->description ) ) > 0 ) {
				$users_with_bio++;
			}
		}

		$percentage = ( $users_with_bio / count( $users ) ) * 100;

		if ( $percentage < 50 ) {
			return Diagnostic_Lean_Checks::build_finding(
				'users-bio-completion',
				'User Bios Not Filled',
				sprintf( 'Only %.0f%% of users have filled in their bio. Help readers know your team better by encouraging complete profiles.', $percentage ),
				'Users & Team',
				'low',
				'informational'
			);
		}

		return null;
	}
}
