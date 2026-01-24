<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Diagnostic_Lean_Checks;

/**
 * Diagnostic: Admin Last Login Tracking
 *
 * Category: Users & Team
 * Priority: 2
 * Philosophy: 1
 *
 * Test Description:
 * When did admin accounts last log in?
 *
 * @package WPShadow
 * @subpackage Diagnostics
 *
 * @verified 2026-01-24 - Batch 3 implementation
 * @guardian-integrated Pending
 */
class Diagnostic_Users_Admin_Last_Login extends Diagnostic_Base
{
	protected static $slug = 'users-admin-last-login';
	protected static $title = 'Admin Last Login Tracking';
	protected static $description = 'When did admin accounts last log in?';
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
		// Check if admin last login tracking is active
		$login_tracking_plugins = [
			'wps-limit-login',
			'loginizer',
			'wp-security-audit-log',
			'simple-login-log',
			'wordfence'
		];

		$has_tracking = false;
		foreach ($login_tracking_plugins as $plugin) {
			if (
				is_plugin_active($plugin . '/' . $plugin . '.php') ||
				is_plugin_active($plugin)
			) {
				$has_tracking = true;
				break;
			}
		}

		if (! $has_tracking) {
			return Diagnostic_Lean_Checks::build_finding(
				'users-admin-last-login',
				'No Login Tracking Active',
				'Consider enabling a login tracking plugin to monitor admin account activity.',
				'Users & Team',
				'low',
				'informational'
			);
		}

		return null;
	}
}
