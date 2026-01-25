<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Diagnostic_Lean_Checks;

/**
 * Diagnostic: Orphaned User Accounts
 *
 * Category: Users & Team
 * Priority: 2
 * Philosophy: 1
 *
 * Test Description:
 * Are there user accounts that have no posts or activity?
 *
 * @package WPShadow
 * @subpackage Diagnostics
 *
 * @verified 2026-01-24 - Continuous batch implementation
 * @guardian-integrated Pending
 */
class Diagnostic_Users_Orphaned_Accounts extends Diagnostic_Base {
	protected static $slug         = 'users-orphaned-accounts';
	protected static $title        = 'Orphaned User Accounts';
	protected static $description  = 'Are there user accounts with no activity?';
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
		$users = get_users( array( 'fields' => 'ID' ) );

		if ( empty( $users ) || count( $users ) < 2 ) {
			return null; // Only admin or very few users
		}

		$inactive_users = 0;
		foreach ( $users as $user_id ) {
			// Check if user has any posts
			$user_posts = count_user_posts( $user_id );
			if ( 0 === $user_posts ) {
				// Check if never logged in or very old
				$last_login = get_user_meta( $user_id, 'last_login', true );
				if ( empty( $last_login ) ) {
					++$inactive_users;
				}
			}
		}

		if ( $inactive_users > 0 ) {
			return Diagnostic_Lean_Checks::build_finding(
				'users-orphaned-accounts',
				'Orphaned User Accounts Found',
				sprintf( '%d user account(s) have no posts and no recorded login. Consider removing unused accounts for security.', $inactive_users ),
				'Users & Team',
				'low',
				'informational'
			);
		}

		return null;
	}
}
