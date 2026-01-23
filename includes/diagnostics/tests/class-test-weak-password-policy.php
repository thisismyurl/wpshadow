<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

/**
 * Diagnostic: Weak Password Policy
 *
 * Detects when WordPress doesn't enforce strong password policies.
 * Weak passwords are a primary attack vector for account compromises.
 *
 * @since 1.2.0
 */
class Test_Weak_Password_Policy extends Diagnostic_Base {

	/**
	 * Check for weak password policy
	 *
	 * @return array|null Diagnostic array if issues found, null if all good
	 */
	public static function check(): ?array {
		$policy_issues = self::detect_policy_issues();

		if ( empty( $policy_issues ) ) {
			return null;
		}

		$threat = count( $policy_issues ) * 15;
		$threat = min( 70, $threat );

		return [
			'threat_level'    => $threat,
			'threat_color'    => 'orange',
			'passed'          => false,
			'issue'           => sprintf(
				'Found %d password policy weaknesses',
				count( $policy_issues )
			),
			'metadata'        => [
				'issues_count' => count( $policy_issues ),
				'issues'       => $policy_issues,
			],
			'kb_link'         => 'https://wpshadow.com/kb/password-security-policy/',
			'training_link'   => 'https://wpshadow.com/training/wordpress-user-security/',
		];
	}

	/**
	 * Guardian Sub-Test: Password policy configuration
	 *
	 * @return array Test result
	 */
	public static function test_password_policy_config(): array {
		$policy = self::get_password_policy();

		return [
			'test_name'     => 'Password Policy Configuration',
			'minimum_length' => $policy['min_length'],
			'requires_uppercase' => $policy['requires_uppercase'],
			'requires_numbers' => $policy['requires_numbers'],
			'requires_special' => $policy['requires_special'],
			'passed'        => $policy['min_length'] >= 12,
			'description'   => sprintf( 'Minimum length: %d characters', $policy['min_length'] ),
		];
	}

	/**
	 * Guardian Sub-Test: Two-factor authentication
	 *
	 * @return array Test result
	 */
	public static function test_two_factor_authentication(): array {
		$has_2fa = self::has_2fa_plugin();
		$admin_count = count( get_users( [ 'role' => 'administrator' ] ) );
		$admins_with_2fa = self::count_users_with_2fa( 'administrator' );

		return [
			'test_name'       => 'Two-Factor Authentication',
			'plugin_available' => $has_2fa,
			'admin_count'     => $admin_count,
			'admins_with_2fa' => $admins_with_2fa,
			'adoption_rate'   => $admin_count > 0 ? round( ( $admins_with_2fa / $admin_count ) * 100 ) : 0,
			'passed'          => $has_2fa && $admins_with_2fa >= ( $admin_count / 2 ), // At least 50% adoption
			'description'     => $has_2fa ? sprintf( '2FA available: %d/%d admins enabled', $admins_with_2fa, $admin_count ) : 'No 2FA plugin found',
		];
	}

	/**
	 * Guardian Sub-Test: Weak admin accounts
	 *
	 * @return array Test result
	 */
	public static function test_weak_admin_accounts(): array {
		$weak_accounts = self::find_weak_usernames();

		return [
			'test_name'        => 'Weak Admin Usernames',
			'weak_accounts'    => $weak_accounts,
			'count'            => count( $weak_accounts ),
			'passed'           => empty( $weak_accounts ),
			'description'      => empty( $weak_accounts ) ? 'No weak admin usernames found' : sprintf( '%d weak admin usernames detected', count( $weak_accounts ) ),
		];
	}

	/**
	 * Guardian Sub-Test: Password expiration policy
	 *
	 * @return array Test result
	 */
	public static function test_password_expiration(): array {
		$expiration_days = get_option( 'wpshadow_password_expiration_days', 0 );
		$has_expiration = $expiration_days > 0;

		return [
			'test_name'       => 'Password Expiration Policy',
			'has_expiration'  => $has_expiration,
			'expiration_days' => $expiration_days,
			'passed'          => $has_expiration,
			'description'     => $has_expiration ? sprintf( 'Passwords expire every %d days', $expiration_days ) : 'No password expiration policy',
		];
	}

	/**
	 * Detect password policy issues
	 *
	 * @return array List of issues
	 */
	private static function detect_policy_issues(): array {
		$issues = [];

		$policy = self::get_password_policy();

		if ( $policy['min_length'] < 8 ) {
			$issues[] = sprintf( 'Minimum password length too short (%d characters)', $policy['min_length'] );
		}

		if ( ! $policy['requires_uppercase'] ) {
			$issues[] = 'Passwords don\'t require uppercase letters';
		}

		if ( ! $policy['requires_numbers'] ) {
			$issues[] = 'Passwords don\'t require numbers';
		}

		// Check for weak admin usernames
		$weak = self::find_weak_usernames();
		if ( ! empty( $weak ) ) {
			$issues[] = sprintf( '%d admin accounts use weak usernames', count( $weak ) );
		}

		return $issues;
	}

	/**
	 * Get password policy settings
	 *
	 * @return array Policy settings
	 */
	private static function get_password_policy(): array {
		return [
			'min_length'         => 8, // WordPress minimum
			'requires_uppercase' => false, // Not enforced by default
			'requires_numbers'   => false, // Not enforced by default
			'requires_special'   => false, // Not enforced by default
		];
	}

	/**
	 * Check if 2FA plugin is available
	 *
	 * @return bool
	 */
	private static function has_2fa_plugin(): bool {
		$twofa_plugins = [
			'two-factor-authentication/two-factor-authentication.php',
			'wordfence/wordfence.php', // Has 2FA
			'jetpack/jetpack.php', // Has 2FA
		];

		$active = get_option( 'active_plugins', [] );

		foreach ( $twofa_plugins as $plugin ) {
			if ( in_array( $plugin, $active, true ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Count users with 2FA enabled
	 *
	 * @param string $role User role
	 * @return int
	 */
	private static function count_users_with_2fa( string $role ): int {
		$users = get_users( [ 'role' => $role ] );
		$count = 0;

		foreach ( $users as $user ) {
			$has_2fa = get_user_meta( $user->ID, '_user_2fa_enabled', true );
			if ( $has_2fa ) {
				$count++;
			}
		}

		return $count;
	}

	/**
	 * Find weak admin usernames
	 *
	 * @return array Weak usernames
	 */
	private static function find_weak_usernames(): array {
		$weak = [];
		$weak_names = [ 'admin', 'administrator', 'root', 'test', 'demo', 'user' ];

		$admins = get_users( [ 'role' => 'administrator' ] );
		foreach ( $admins as $user ) {
			if ( in_array( strtolower( $user->user_login ), $weak_names, true ) ) {
				$weak[] = $user->user_login;
			}
		}

		return $weak;
	}

	/**
	 * Get diagnostic name
	 *
	 * @return string
	 */
	public static function get_name(): string {
		return 'Weak Password Policy';
	}

	/**
	 * Get diagnostic description
	 *
	 * @return string
	 */
	public static function get_description(): string {
		return 'Checks if WordPress enforces strong password policies';
	}

	/**
	 * Get diagnostic category
	 *
	 * @return string
	 */
	public static function get_category(): string {
		return 'Security';
	}
}
