<?php

declare(strict_types=1);
/**
 * OAuth Token Storage Diagnostic
 *
 * Philosophy: Token security - secure OAuth token storage
 * @package WPShadow
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check OAuth token storage security.
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_OAuth_Token_Storage extends Diagnostic_Base
{
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array
	{
		// Check for OAuth plugins
		$oauth_plugins = array(
			'oauth2-provider/oauth2-provider.php',
			'wp-oauth-server/wp-oauth-server.php',
			'miniorange-oauth-20-server/miniorange_oauth_server.php',
		);

		$active = get_option('active_plugins', array());
		$has_oauth = false;

		foreach ($oauth_plugins as $plugin) {
			if (in_array($plugin, $active, true)) {
				$has_oauth = true;
				break;
			}
		}

		if (! $has_oauth) {
			return null; // No OAuth
		}

		// Check if tokens are stored in database (common pattern)
		global $wpdb;
		$token_tables = $wpdb->get_results(
			"SHOW TABLES LIKE '{$wpdb->prefix}%oauth%token%'"
		);

		if (! empty($token_tables)) {
			return array(
				'id'          => 'oauth-token-storage',
				'title'       => 'OAuth Tokens in Database',
				'description' => 'OAuth tokens are stored in database tables. Tokens should be stored in httpOnly, Secure cookies or encrypted at rest. Database storage exposes tokens via SQL injection or backups.',
				'severity'    => 'high',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/secure-oauth-tokens/',
				'training_link' => 'https://wpshadow.com/training/oauth-security/',
				'auto_fixable' => false,
				'threat_level' => 85,
			);
		}

		return null;
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: OAuth Token Storage
	 * Slug: -oauth-token-storage
	 * File: class-diagnostic-oauth-token-storage.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: OAuth Token Storage
	 * Slug: -oauth-token-storage
	 *
	 * TODO: Review the check() method to understand what constitutes a passing test.
	 * The test should verify that:
	 * - check() returns NULL when the diagnostic condition is NOT met (site is healthy)
	 * - check() returns an array when the diagnostic condition IS met (issue found)
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live__oauth_token_storage(): array
	{
		$oauth_plugins = array(
			'oauth2-provider/oauth2-provider.php',
			'wp-oauth-server/wp-oauth-server.php',
			'miniorange-oauth-20-server/miniorange_oauth_server.php',
		);

		$active   = get_option('active_plugins', array());
		$has_oauth = false;

		foreach ($oauth_plugins as $plugin) {
			if (in_array($plugin, $active, true)) {
				$has_oauth = true;
				break;
			}
		}

		$tokens_in_db = false;
		if ($has_oauth) {
			global $wpdb;
			$token_tables = $wpdb->get_results("SHOW TABLES LIKE '{$wpdb->prefix}%oauth%token%'");
			$tokens_in_db = ! empty($token_tables);
		}

		$expected_issue = $has_oauth && $tokens_in_db;

		$result = self::check();
		$has_finding = is_array($result);

		if ($expected_issue === $has_finding) {
			$message = $expected_issue ? 'Finding returned when OAuth tokens stored in database.' : 'No finding returned when no database token storage detected.';
			return array(
				'passed'  => true,
				'message' => $message,
			);
		}

		$message = $expected_issue
			? 'Expected a finding for OAuth tokens stored in database, but got none.'
			: 'Expected no finding when token storage in database is absent, but got a finding.';

		return array(
			'passed'  => false,
			'message' => $message,
		);
	}
}
