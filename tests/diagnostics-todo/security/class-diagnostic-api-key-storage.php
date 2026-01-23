<?php

declare(strict_types=1);
/**
 * API Key Storage Diagnostic
 *
 * Philosophy: Secret management - use constants not database
 * @package WPShadow
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if API keys are stored in database.
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_API_Key_Storage extends Diagnostic_Base
{
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array
	{
		global $wpdb;

		// Search wp_options for common API key patterns
		$api_patterns = array('%api_key%', '%api_secret%', '%access_token%', '%secret_key%');
		$found_keys = array();

		foreach ($api_patterns as $pattern) {
			$results = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s AND option_name NOT LIKE '%transient%' LIMIT 5",
					$pattern
				)
			);

			if (! empty($results)) {
				foreach ($results as $result) {
					// Exclude WordPress core keys
					if (! in_array($result->option_name, array('auth_key', 'secure_auth_key', 'logged_in_key', 'nonce_key'), true)) {
						$found_keys[] = $result->option_name;
					}
				}
			}
		}

		if (! empty($found_keys)) {
			return array(
				'id'          => 'api-key-storage',
				'title'       => 'API Keys Stored in Database',
				'description' => sprintf(
					'API keys found in wp_options: %s. Database-stored secrets are exposed via SQL injection or database dumps. Move to constants in wp-config.php outside version control.',
					implode(', ', array_slice($found_keys, 0, 3))
				),
				'severity'    => 'high',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/secure-api-key-storage/',
				'training_link' => 'https://wpshadow.com/training/secret-management/',
				'auto_fixable' => false,
				'threat_level' => 80,
			);
		}

		return null;
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: API Key Storage
	 * Slug: -api-key-storage
	 * File: class-diagnostic-api-key-storage.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: API Key Storage
	 * Slug: -api-key-storage
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
	public static function test_live__api_key_storage(): array
	{
		global $wpdb;

		$api_patterns = array('%api_key%', '%api_secret%', '%access_token%', '%secret_key%');
		$found_keys = array();

		foreach ($api_patterns as $pattern) {
			$results = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s AND option_name NOT LIKE '%transient%' LIMIT 5",
					$pattern
				)
			);

			if (! empty($results)) {
				foreach ($results as $result) {
					if (! in_array($result->option_name, array('auth_key', 'secure_auth_key', 'logged_in_key', 'nonce_key'), true)) {
						$found_keys[] = $result->option_name;
					}
				}
			}
		}

		$expected_issue = ! empty($found_keys);

		$result = self::check();
		$has_finding = is_array($result);

		if ($expected_issue === $has_finding) {
			$message = $expected_issue ? 'Finding returned when API keys found in database.' : 'No finding when database is clean.';
			return array(
				'passed'  => true,
				'message' => $message,
			);
		}

		$message = $expected_issue
			? 'Expected a finding for API keys in database but got none.'
			: 'Expected no finding but API keys were detected.';

		return array(
			'passed'  => false,
			'message' => $message,
		);
	}
}
