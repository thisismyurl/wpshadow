<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Diagnostic_Lean_Checks;

/**
 * Diagnostic: Users with Profile Photo
 *
 * Category: Users & Team
 * Priority: 2
 * Philosophy: 1
 *
 * Test Description:
 * What percentage of users have uploaded a profile photo?
 *
 * @package WPShadow
 * @subpackage Diagnostics
 *
 * @verified 2026-01-24 - Batch 4 implementation
 * @guardian-integrated Pending
 */
class Diagnostic_Users_Profile_Photo_Adoption extends Diagnostic_Base
{
	protected static $slug = 'users-profile-photo-adoption';
	protected static $title = 'Users with Profile Photo';
	protected static $description = 'What percentage of users have uploaded a profile photo?';
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
		// Check if a profile photo plugin is active
		$profile_plugins = [
			'user-avatar',
			'wp-user-avatar',
			'members',
			'gravatar',
			'buddypress'
		];

		$has_profiles = false;
		foreach ($profile_plugins as $plugin) {
			if (
				is_plugin_active($plugin . '/' . $plugin . '.php') ||
				is_plugin_active($plugin)
			) {
				$has_profiles = true;
				break;
			}
		}

		// If no profile plugin, informational
		if (! $has_profiles) {
			$total_users = count_users();
			if ($total_users['total_users'] > 1) {
				return Diagnostic_Lean_Checks::build_finding(
					'users-profile-photo-adoption',
					'No User Avatar Plugin Found',
					'Consider enabling a user avatar plugin to improve team visibility.',
					'Users & Team',
					'low',
					'informational'
				);
			}
		}

		return null;
	}
}
