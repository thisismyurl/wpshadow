<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Diagnostic: Autoloaded Options Size (DB-001)
 *
 * Detects if autoloaded options exceed the safe threshold (default 0.8MB).
 * Philosophy: Shows value (#9) by highlighting database bloat that slows every request.
 *
 * @verified 2026-01-23 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 *
 * @package WPShadow
 */
class Diagnostic_Autoloaded_Options_Size extends Diagnostic_Base
{
	protected static $slug         = 'autoloaded-options-size';
	protected static $title        = 'Autoloaded Options Size';
	protected static $description  = 'Checks if autoloaded options exceed the safe size threshold (0.8MB) which impacts every request.';
	protected static $family       = 'database';
	protected static $family_label = 'Database Health';

	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Array with finding details or null if no issue found.
	 */
	public static function check(): ?array
	{
		global $wpdb;

		if (! isset($wpdb) || ! is_object($wpdb) || ! isset($wpdb->options)) {
			return null;
		}

		$threshold_mb    = 0.8; // 800KB guideline
		$threshold_bytes = $threshold_mb * 1024 * 1024;

		$autoloaded_size = (int) $wpdb->get_var(
			"SELECT COALESCE(SUM(CHAR_LENGTH(option_value)), 0) FROM {$wpdb->options} WHERE autoload='yes'"
		);

		if ($autoloaded_size <= $threshold_bytes) {
			return null;
		}

		$size_mb = $autoloaded_size / (1024 * 1024);

		return array(
			'id'           => self::$slug,
			'finding_id'   => self::$slug,
			'title'        => sprintf(__('Large Autoloaded Options (%.2f MB)', 'wpshadow'), $size_mb),
			'description'  => __('Autoloaded options are loaded on every page. Reduce or disable autoload on large options to speed up every request.', 'wpshadow'),
			'category'     => 'performance',
			'family'       => self::$family,
			'family_label' => self::$family_label,
			'kb_link'      => 'https://wpshadow.com/kb/autoloaded-options-optimization/',
			'training_link' => 'https://wpshadow.com/training/options-autoload/',
			'auto_fixable' => false,
			'impact'       => 'database_bloat',
			'severity'     => 'medium',
			'threat_level' => 55,
			'timestamp'    => current_time('mysql'),
		);
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Autoloaded Options Size
	 * Slug: autoloaded-options-size
	 *
	 * Test Purpose:
	 * - Verify that check() method returns the correct result based on site state
	 * - PASS: check() returns NULL when diagnostic condition is NOT met (site is healthy)
	 * - FAIL: check() returns array when diagnostic condition IS met (issue found)
	 * - Description: Checks if autoloaded options exceed the safe size threshold (0.8MB) which impacts every request.
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live_autoloaded_options_size(): array
	{
		global $wpdb;

		// Recompute actual autoloaded size from database
		$threshold_mb    = 0.8; // Must match check() threshold
		$threshold_bytes = $threshold_mb * 1024 * 1024;

		$autoloaded_size = (int) $wpdb->get_var(
			"SELECT COALESCE(SUM(CHAR_LENGTH(option_value)), 0) FROM {$wpdb->options} WHERE autoload='yes'"
		);

		$size_mb = $autoloaded_size / (1024 * 1024);

		// Call diagnostic check
		$diagnostic_result = self::check();

		// Determine expected state
		$should_find_issue = ($autoloaded_size > $threshold_bytes);
		$diagnostic_found_issue = ($diagnostic_result !== null);

		// Compare expected vs actual diagnostic result
		$test_passes = ($should_find_issue === $diagnostic_found_issue);

		$message = sprintf(
			'Autoloaded size: %.2f MB (threshold: %.2f MB). Expected diagnostic to %s issue. Diagnostic %s issue. Test: %s',
			$size_mb,
			$threshold_mb,
			$should_find_issue ? 'FIND' : 'NOT find',
			$diagnostic_found_issue ? 'FOUND' : 'DID NOT find',
			$test_passes ? 'PASS' : 'FAIL'
		);

		return array(
			'passed'  => $test_passes,
			'message' => $message,
		);
	}
}
