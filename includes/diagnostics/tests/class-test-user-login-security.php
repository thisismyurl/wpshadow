<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

/**
 * Diagnostic: User Login Security
 *
 * Monitors login security and detects brute force attack risks.
 * Weak login protection enables account hijacking and unauthorized access.
 *
 * @since 1.2.0
 */
class Test_User_Login_Security extends Diagnostic_Base
{

	/**
	 * Check login security configuration
	 *
	 * @return array|null Diagnostic array if issues found, null if all good
	 */
	public static function check(): ?array
	{
		$login_security = self::analyze_login_security();

		if ($login_security['threat_level'] === 0) {
			return null;
		}

		return [
			'threat_level'    => $login_security['threat_level'],
			'threat_color'    => 'orange',
			'passed'          => false,
			'issue'           => $login_security['issue'],
			'metadata'        => $login_security,
			'kb_link'         => 'https://wpshadow.com/kb/wordpress-login-security/',
			'training_link'   => 'https://wpshadow.com/training/wordpress-authentication-security/',
		];
	}

	/**
	 * Guardian Sub-Test: Two-factor authentication
	 *
	 * @return array Test result
	 */
	public static function test_two_factor_authentication(): array
	{
		$active_plugins = get_plugins();

		$tfa_plugins = [
			'two-factor/two-factor.php' => 'Two Factor',
			'google-authenticator-per-user-prompt/google-auth.php' => 'Google Authenticator',
			'wordfence/wordfence.php' => 'Wordfence',
		];

		$has_tfa = false;
		foreach ($tfa_plugins as $plugin_file => $plugin_name) {
			if (isset($active_plugins[$plugin_file])) {
				$has_tfa = true;
				break;
			}
		}

		return [
			'test_name'   => 'Two-Factor Authentication',
			'enabled'     => $has_tfa,
			'passed'      => $has_tfa,
			'description' => $has_tfa ? 'Two-factor authentication plugin active' : 'Two-factor authentication not configured',
		];
	}

	/**
	 * Guardian Sub-Test: Brute force protection
	 *
	 * @return array Test result
	 */
	public static function test_brute_force_protection(): array
	{
		$active_plugins = get_plugins();

		$brute_force_plugins = [
			'wordfence/wordfence.php' => 'Wordfence',
			'sucuri-scanner/sucuri.php' => 'Sucuri Security',
			'iThemes-Security-Pro/iThemes-Security-Pro.php' => 'iThemes Security',
			'limit-login-attempts-reloaded/limit-login-attempts-reloaded.php' => 'Limit Login Attempts',
		];

		$has_protection = false;
		foreach ($brute_force_plugins as $plugin_file => $plugin_name) {
			if (isset($active_plugins[$plugin_file])) {
				$has_protection = true;
				break;
			}
		}

		return [
			'test_name'   => 'Brute Force Protection',
			'enabled'     => $has_protection,
			'passed'      => $has_protection,
			'description' => $has_protection ? 'Brute force protection plugin active' : 'Brute force protection not configured',
		];
	}

	/**
	 * Guardian Sub-Test: Login URL obscurity
	 *
	 * @return array Test result
	 */
	public static function test_login_url_obscurity(): array
	{
		// Check if login URL is at default /wp-login.php or customized
		$wp_login_accessible = true;

		$response = wp_remote_head(home_url('/wp-login.php'), [
			'timeout' => 5,
		]);

		if (is_wp_error($response) || wp_remote_retrieve_response_code($response) === 404) {
			$wp_login_accessible = false;
		}

		return [
			'test_name'     => 'Login URL Obscurity',
			'default_login' => $wp_login_accessible,
			'passed'        => ! $wp_login_accessible,
			'description'   => $wp_login_accessible ? 'Using default /wp-login.php URL' : 'Login URL is customized/hidden',
		];
	}

	/**
	 * Guardian Sub-Test: Login attempt logging
	 *
	 * @return array Test result
	 */
	public static function test_login_attempt_logging(): array
	{
		$active_plugins = get_plugins();

		// Check for plugins that log login attempts
		$logging_plugins = [
			'simple-login-log/simple-login-log.php' => 'Simple Login Log',
			'wordfence/wordfence.php' => 'Wordfence',
			'sucuri-scanner/sucuri.php' => 'Sucuri Security',
		];

		$has_logging = false;
		foreach ($logging_plugins as $plugin_file => $plugin_name) {
			if (isset($active_plugins[$plugin_file])) {
				$has_logging = true;
				break;
			}
		}

		return [
			'test_name'   => 'Login Attempt Logging',
			'enabled'     => $has_logging,
			'passed'      => $has_logging,
			'description' => $has_logging ? 'Login attempts are logged' : 'Login attempt logging not configured',
		];
	}

	/**
	 * Guardian Sub-Test: Admin username check
	 *
	 * @return array Test result
	 */
	public static function test_admin_username(): array
	{
		$admin = get_user_by('login', 'admin');

		$has_default_admin = $admin !== false;

		return [
			'test_name'         => 'Default Admin Username',
			'has_default_admin' => $has_default_admin,
			'passed'            => ! $has_default_admin,
			'description'       => $has_default_admin ? 'Found default "admin" username account' : 'No default admin account found',
		];
	}

	/**
	 * Analyze login security
	 *
	 * @return array Login security analysis
	 */
	private static function analyze_login_security(): array
	{
		$active_plugins = get_plugins();

		$threat_level = 0;
		$issues = [];

		// Check for two-factor authentication
		$tfa_plugins = [
			'two-factor/two-factor.php',
			'google-authenticator-per-user-prompt/google-auth.php',
			'wordfence/wordfence.php',
		];

		$has_tfa = false;
		foreach ($tfa_plugins as $plugin_file) {
			if (isset($active_plugins[$plugin_file])) {
				$has_tfa = true;
				break;
			}
		}

		if (! $has_tfa) {
			$issues[] = 'Two-factor authentication not enabled';
			$threat_level = 30;
		}

		// Check for brute force protection
		$brute_force_plugins = [
			'wordfence/wordfence.php',
			'sucuri-scanner/sucuri.php',
			'limit-login-attempts-reloaded/limit-login-attempts-reloaded.php',
		];

		$has_brute_force = false;
		foreach ($brute_force_plugins as $plugin_file) {
			if (isset($active_plugins[$plugin_file])) {
				$has_brute_force = true;
				break;
			}
		}

		if (! $has_brute_force) {
			$issues[] = 'Brute force protection not enabled';
			$threat_level = max($threat_level, 40);
		}

		// Check for default admin account
		$admin = get_user_by('login', 'admin');
		if ($admin) {
			$issues[] = 'Default admin account still exists';
			$threat_level = max($threat_level, 50);
		}

		$issue = ! empty($issues) ? implode('; ', $issues) : 'Login security is properly configured';

		return [
			'threat_level' => $threat_level,
			'issue'        => $issue,
			'has_tfa'      => $has_tfa,
			'has_brute_force' => $has_brute_force,
			'has_default_admin' => $admin !== false,
		];
	}

	/**
	 * Get diagnostic name
	 *
	 * @return string
	 */
	public static function get_name(): string
	{
		return 'User Login Security';
	}

	/**
	 * Get diagnostic description
	 *
	 * @return string
	 */
	public static function get_description(): string
	{
		return 'Monitors login security and detects brute force attack risks';
	}

	/**
	 * Get diagnostic category
	 *
	 * @return string
	 */
	public static function get_category(): string
	{
		return 'Security';
	}
}
