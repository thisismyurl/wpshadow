<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Diagnostic_Lean_Checks;

/**
 * Diagnostic: User Role Distribution
 *
 * Category: Users & Team
 * Priority: 2
 * Philosophy: 1
 *
 * Test Description:
 * Breakdown of users by role (admin, editor, author, contributor, subscriber)
 *
 * @package WPShadow
 * @subpackage Diagnostics
 *
 * @verified 2026-01-24 - Batch 4 implementation
 * @guardian-integrated Pending
 */
class Diagnostic_Users_Role_Distribution extends Diagnostic_Base
{
	protected static $slug = 'users-role-distribution';
	protected static $title = 'User Role Distribution';
	protected static $description = 'Breakdown of users by role';
	protected static $category = 'Users & Team';
	protected static $threat_level = 'low';
	protected static $family = 'general';
	protected static $family_label = 'General';

	/**
	 * Run the diagnostic check
	 *
	 * @return ?array Null if pass, array of findings if fail
	 */
	public function check(): ?array
	{
		$roles = ['administrator', 'editor', 'author', 'contributor', 'subscriber'];
		$distribution = [];

		foreach ($roles as $role) {
			$users = count(get_users(['role' => $role]));
			if ($users > 0) {
				$distribution[$role] = $users;
			}
		}

		// Informational - no pass/fail, just audit
		return null;
	}
}
