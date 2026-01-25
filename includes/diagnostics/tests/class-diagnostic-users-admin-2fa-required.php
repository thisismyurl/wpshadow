<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Diagnostic_Lean_Checks;

/**
 * Diagnostic: 2FA Required for Admins
 *
 * Category: Users & Team
 * Priority: 2
 * Philosophy: 1
 *
 * Test Description:
 * Is 2FA enforced for admin accounts?
 *
 * @package WPShadow
 * @subpackage Diagnostics
 *
 * @verified 2026-01-24 - Batch 3 implementation
 * @guardian-integrated Pending
 */
class Diagnostic_Users_Admin_2fa_Required extends Diagnostic_Base {

	protected static $slug         = 'users-admin-2fa-required';
	protected static $title        = '2FA Required for Admins';
	protected static $description  = 'Is 2FA enforced for admin accounts?';
	protected static $category     = 'Users & Team';
	protected static $threat_level = 'medium';
	protected static $family       = 'general';
	protected static $family_label = 'General';

	/**
	 * Run the diagnostic check
	 *
	 * @return ?array Null if pass, array of findings if fail
	 */
	public function check(): ?array {
		// Check if 2FA plugin is active
		$two_fa_plugins = array(
			'two-factor',
			'wordfence',
			'jetpack',
			'shield-security',
			'google-authenticator',
			'authy-two-factor',
		);

		$has_2fa = false;
		foreach ( $two_fa_plugins as $plugin ) {
			if (
				is_plugin_active( $plugin . '/' . $plugin . '.php' ) ||
				is_plugin_active( $plugin )
			) {
				$has_2fa = true;
				break;
			}
		}

		if ( ! $has_2fa ) {
			return Diagnostic_Lean_Checks::build_finding(
				'users-admin-2fa-required',
				'2FA Not Enforced',
				'Two-factor authentication is not enforced for admin accounts. Consider enabling it for improved security.',
				'Users & Team',
				'medium',
				'low'
			);
		}

		return null;
	}
}
