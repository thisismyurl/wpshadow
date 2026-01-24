<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Diagnostic_Lean_Checks;

/**
 * Diagnostic: Two-Factor Authentication Adoption
 *
 * Category: Users & Team
 * Priority: 2
 * Philosophy: 1
 *
 * Test Description:
 * What percentage of users have two-factor auth enabled?
 *
 * @package WPShadow
 * @subpackage Diagnostics
 *
 * @verified 2026-01-24 - Batch 5 implementation
 * @guardian-integrated Pending
 */
class Diagnostic_Users_Two_Factor_Adoption extends Diagnostic_Base {
	protected static $slug = 'users-two-factor-adoption';
	protected static $title = 'Two-Factor Authentication Adoption';
	protected static $description = 'What percentage of users have two-factor auth enabled?';
	protected static $category = 'Users & Team';
	protected static $threat_level = 'medium';
	protected static $family = 'general';
	protected static $family_label = 'General';

	/**
	 * Run the diagnostic check
	 *
	 * @return ?array Null if pass, array of findings if fail
	 */
	public function check(): ?array {
		// First check if 2FA plugin is active
		$two_fa_plugins = [
			'two-factor',
			'wordfence',
			'jetpack',
			'shield-security',
			'google-authenticator',
			'authy-two-factor'
		];

		$has_2fa = false;
		foreach ( $two_fa_plugins as $plugin ) {
			if ( is_plugin_active( $plugin . '/' . $plugin . '.php' ) ||
				 is_plugin_active( $plugin ) ) {
				$has_2fa = true;
				break;
			}
		}

		if ( ! $has_2fa ) {
			return Diagnostic_Lean_Checks::build_finding(
				'users-two-factor-adoption',
				'2FA Not Available',
				'Two-factor authentication plugin is not active. Consider enabling it for improved security.',
				'Users & Team',
				'medium',
				'medium'
			);
		}

		// If plugin is active, try to check adoption
		// Most 2FA plugins store enabled status in user meta
		$users = get_users( [ 'fields' => 'ID' ] );
		if ( ! empty( $users ) ) {
			$users_with_2fa = 0;
			foreach ( $users as $user_id ) {
				// Check common 2FA meta keys
				$has_2fa_enabled = get_user_meta( $user_id, 'two_factor_enabled' ) ||
								   get_user_meta( $user_id, '_two_factor_active' ) ||
								   get_user_meta( $user_id, 'wordfence_2fa' );
				if ( $has_2fa_enabled ) {
					$users_with_2fa++;
				}
			}

			$percentage = ( $users_with_2fa / count( $users ) ) * 100;
			if ( $percentage < 50 ) {
				return Diagnostic_Lean_Checks::build_finding(
					'users-two-factor-adoption',
					'Low 2FA Adoption',
					sprintf( 'Only %.0f%% of users have 2FA enabled. Encourage all users to enable it for security.', $percentage ),
					'Users & Team',
					'low',
					'low'
				);
			}
		}

		return null;
	}
}
