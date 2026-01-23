<?php

/**
 * Diagnostic: Autoloaded Options Bloat
 *
 * Detects excessive autoloaded options that slow down every page load.
 *
 * Philosophy: Show Value (#9) - Prove performance impact with numbers
 * KB Link: https://wpshadow.com/kb/autoloaded-options-bloat
 * Training: https://wpshadow.com/training/autoloaded-options-bloat
 *
 * @package WPShadow
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Autoloaded Options Bloat diagnostic
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Autoloaded_Options_Bloat extends Diagnostic_Base
{

	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Diagnostic result or null if no issue
	 */
	public static function check(): ?array
	{
		global $wpdb;

		// Get total size of autoloaded options
		$autoloaded_size = $wpdb->get_var(
			"SELECT SUM(LENGTH(option_value)) FROM {$wpdb->options} WHERE autoload = 'yes'"
		);

		$autoloaded_size_mb = round($autoloaded_size / 1024 / 1024, 2);

		// Thresholds: < 1MB good, 1-3MB warning, > 3MB critical
		if ($autoloaded_size_mb < 1) {
			return null; // All good
		}

		// Get count of autoloaded options
		$autoloaded_count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->options} WHERE autoload = 'yes'"
		);

		// Get largest autoloaded options
		$large_options = $wpdb->get_results(
			"SELECT option_name, LENGTH(option_value) as size
			FROM {$wpdb->options}
			WHERE autoload = 'yes'
			ORDER BY size DESC
			LIMIT 10",
			ARRAY_A
		);

		$severity = $autoloaded_size_mb > 3 ? 'high' : 'medium';

		$description = sprintf(
			__('Your site has %s MB of autoloaded options across %d entries. Autoloaded options are loaded on every page request, slowing down your entire site. Recommended maximum is 1 MB.', 'wpshadow'),
			$autoloaded_size_mb,
			$autoloaded_count
		);

		// Build culprit list
		$culprits = [];
		foreach ($large_options as $option) {
			$size_kb = round($option['size'] / 1024, 2);
			if ($size_kb > 50) { // Only show options > 50KB
				$culprits[] = sprintf(
					'%s (%s KB)',
					$option['option_name'],
					$size_kb
				);
			}
		}

		if (! empty($culprits)) {
			$description .= ' ' . __('Largest autoloaded options: ', 'wpshadow') . implode(', ', $culprits);
		}

		return [
			'id'                => 'autoloaded-options-bloat',
			'title'             => __('Excessive Autoloaded Options', 'wpshadow'),
			'description'       => $description,
			'severity'          => $severity,
			'category'          => 'performance',
			'impact'            => 'high',
			'effort'            => 'medium',
			'kb_link'           => 'https://wpshadow.com/kb/autoloaded-options-bloat',
			'training_link'     => 'https://wpshadow.com/training/autoloaded-options-bloat',
			'affected_resource' => sprintf('%d options, %s MB', $autoloaded_count, $autoloaded_size_mb),
			'metadata'          => [
				'size_mb'          => $autoloaded_size_mb,
				'count'            => $autoloaded_count,
				'large_options'    => $large_options,
				'performance_cost' => sprintf('%d ms per page load', (int) ($autoloaded_size_mb * 10)),
			],
		];
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Autoloaded Options Bloat
	 * Slug: -autoloaded-options-bloat
	 * File: class-diagnostic-autoloaded-options-bloat.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Autoloaded Options Bloat
	 * Slug: -autoloaded-options-bloat
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
	public static function test_live__autoloaded_options_bloat(): array
	{
		global $wpdb;

		$result = self::check();

		$size_bytes = (float) $wpdb->get_var(
			"SELECT SUM(LENGTH(option_value)) FROM {$wpdb->options} WHERE autoload = 'yes'"
		);
		$size_mb    = round($size_bytes / 1024 / 1024, 2);
		$has_issue  = $size_mb >= 1.0;

		$diagnostic_found_issue = is_array($result);
		$test_passes            = ($has_issue === $diagnostic_found_issue);

		if ($test_passes && $has_issue) {
			$expected_severity = ($size_mb > 3) ? 'high' : 'medium';
			$actual_severity   = is_array($result) && isset($result['severity']) ? $result['severity'] : null;

			if ($actual_severity !== $expected_severity) {
				$test_passes = false;
			}
		}

		$message = $test_passes
			? 'Autoloaded options check matches site state'
			: sprintf(
				'Mismatch: expected %s but diagnostic returned %s (autoload size %.2f MB)',
				$has_issue ? 'issue' : 'no issue',
				$diagnostic_found_issue ? 'issue' : 'no issue',
				$size_mb
			);

		return array(
			'passed'  => $test_passes,
			'message' => $message,
		);
	}
}
