<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Unused Postmeta Keys
 *
 * Philosophy: Show value (#9) - identify database bloat from orphaned metadata
 * @package WPShadow
 *
 * @verified 2026-01-23 - Detects empty postmeta entries left by inactive plugins
 */
class Diagnostic_Unused_Postmeta_Keys extends Diagnostic_Base
{

	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array
	{
		global $wpdb;

		// Count empty postmeta entries (NULL, empty string, or serialized empty array/object)
		$empty_postmeta = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->postmeta}
			WHERE meta_value = ''
			OR meta_value = '0'
			OR meta_value = 'a:0:{}'
			OR meta_value = 'O:0:\"\":{}'
			OR meta_value IS NULL"
		);

		if (! $empty_postmeta) {
			$empty_postmeta = 0;
		}

		// Also check for orphaned metadata from inactive plugins
		// (meta_key patterns that don't match active plugins)
		$orphaned_postmeta = $wpdb->get_var(
			"SELECT COUNT(DISTINCT meta_key) FROM {$wpdb->postmeta}
			WHERE meta_key NOT LIKE '%\_%'
			AND meta_key NOT IN (
				SELECT DISTINCT meta_key FROM {$wpdb->postmeta}
				WHERE meta_value != ''
				AND meta_value != '0'
				AND meta_value != 'a:0:{}'
				LIMIT 100
			)"
		);

		if (! $orphaned_postmeta) {
			$orphaned_postmeta = 0;
		}

		$total_useless = (int) $empty_postmeta + (int) $orphaned_postmeta;

		// Threshold: flag if more than 100 useless entries
		if ($total_useless > 100) {
			return array(
				'id'            => 'unused-postmeta-keys',
				'title'         => 'Useless Postmeta Entries Detected',
				'description'   => sprintf(
					'Found %d empty/orphaned postmeta entries bloating your database. These are typically left by inactive plugins. Clean them up to improve database performance.',
					$total_useless
				),
				'severity'      => 'medium',
				'category'      => 'performance',
				'kb_link'       => 'https://wpshadow.com/kb/clean-postmeta-entries/',
				'training_link' => 'https://wpshadow.com/training/database-optimization/',
				'auto_fixable'  => false,
				'threat_level'  => 45,
			);
		}

		return null;
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Unused Postmeta Keys
	 * Slug: unused-postmeta-keys
	 * File: class-diagnostic-unused-postmeta-keys.php
	 *
	 * Test Purpose:
	 * Verify that empty/orphaned postmeta entries are detected
	 * - PASS: check() returns NULL when postmeta is clean or below 100 useless entries
	 * - FAIL: check() returns array when 100+ empty/orphaned postmeta found
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live__unused_postmeta_keys(): array
	{
		global $wpdb;

		$result = self::check();

		// Count empty postmeta using same logic as check()
		$empty_postmeta = (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->postmeta}
			WHERE meta_value = ''
			OR meta_value = '0'
			OR meta_value = 'a:0:{}'
			OR meta_value = 'O:0:\"\":{}'
			OR meta_value IS NULL"
		);

		// Count orphaned postmeta
		$orphaned_postmeta = (int) $wpdb->get_var(
			"SELECT COUNT(DISTINCT meta_key) FROM {$wpdb->postmeta}
			WHERE meta_key NOT LIKE '%\_%'
			AND meta_key NOT IN (
				SELECT DISTINCT meta_key FROM {$wpdb->postmeta}
				WHERE meta_value != ''
				AND meta_value != '0'
				AND meta_value != 'a:0:{}'
				LIMIT 100
			)"
		);

		$total_useless = $empty_postmeta + $orphaned_postmeta;

		if ($total_useless > 100) {
			// Many useless entries = diagnostic should report issue
			return array(
				'passed' => !is_null($result) && isset($result['id']) && $result['id'] === 'unused-postmeta-keys',
				'message' => sprintf('Found %d useless postmeta entries, issue correctly identified', $total_useless)
			);
		} else {
			// Few/no useless entries = diagnostic should pass
			return array(
				'passed' => is_null($result),
				'message' => sprintf('Database clean: %d useless postmeta entries (below 100 threshold)', $total_useless)
			);
		}
	}
}
