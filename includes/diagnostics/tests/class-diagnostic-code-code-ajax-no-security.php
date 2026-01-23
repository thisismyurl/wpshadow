<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: AJAX No Security Check
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-ajax-no-security
 * Training: https://wpshadow.com/training/code-ajax-no-security
 */
class Diagnostic_Code_CODE_AJAX_NO_SECURITY extends Diagnostic_Base
{
	public static function check(): ?array
	{
		$analysis = self::analyze_public_ajax_callbacks();

		if (0 === $analysis['unsecured']) {
			return null;
		}

		return [
			'id' => 'code-ajax-no-security',
			'title' => __('AJAX No Security Check', 'wpshadow'),
			'description' => __('Flags AJAX handlers lacking nonce/capability verification.', 'wpshadow'),
			'severity' => 'medium',
			'category' => 'code-quality',
			'kb_link' => 'https://wpshadow.com/kb/code-ajax-no-security',
			'training_link' => 'https://wpshadow.com/training/code-ajax-no-security',
			'auto_fixable' => false,
			'threat_level' => 6,
			'data' => $analysis,
		];
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Code CODE AJAX NO SECURITY
	 * Slug: -code-code-ajax-no-security
	 * File: class-diagnostic-code-code-ajax-no-security.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Code CODE AJAX NO SECURITY
	 * Slug: -code-code-ajax-no-security
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
	public static function test_live__code_code_ajax_no_security(): array
	{
		$analysis = self::analyze_public_ajax_callbacks();
		$diagnostic_result = self::check();
		$should_find_issue = ($analysis['unsecured'] > 0);
		$diagnostic_has_issue = (null !== $diagnostic_result);
		$test_passes = ($should_find_issue === $diagnostic_has_issue);

		$message = sprintf(
			'Public AJAX callbacks: %d, unsecured (non-core/non-WPShadow) callbacks: %d. Expected diagnostic to %s issue. Diagnostic %s issue. Test: %s',
			$analysis['total'],
			$analysis['unsecured'],
			$should_find_issue ? 'FIND' : 'NOT find',
			$diagnostic_has_issue ? 'FOUND' : 'DID NOT find',
			$test_passes ? 'PASS' : 'FAIL'
		);

		return array(
			'passed'  => $test_passes,
			'message' => $message,
		);
	}

	/**
	 * Inspect public AJAX callbacks and approximate security coverage.
	 *
	 * Heuristics:
	 * - Focus on unauthenticated AJAX hooks (wp_ajax_nopriv_*).
	 * - Treat WordPress core callbacks as secured.
	 * - Treat WPShadow AJAX handlers (extending AJAX_Handler_Base) as secured.
	 * - Everything else is counted as unsecured for this diagnostic.
	 *
	 * @return array{total:int,unsecured:int}
	 */
	private static function analyze_public_ajax_callbacks(): array
	{
		global $wp_filter;

		$total = 0;
		$unsecured = 0;

		if (! isset($wp_filter) || ! is_array($wp_filter)) {
			return array(
				'total' => 0,
				'unsecured' => 0,
			);
		}

		foreach ($wp_filter as $hook_name => $hook) {
			if (0 !== strpos($hook_name, 'wp_ajax_nopriv_')) {
				continue;
			}

			if (! $hook instanceof \WP_Hook || empty($hook->callbacks)) {
				continue;
			}

			foreach ($hook->callbacks as $callbacks_at_priority) {
				if (empty($callbacks_at_priority) || ! is_array($callbacks_at_priority)) {
					continue;
				}

				foreach ($callbacks_at_priority as $callback_data) {
					if (empty($callback_data['function'])) {
						continue;
					}

					$total++;

					$evaluation = self::evaluate_ajax_callback_security($callback_data['function']);

					if (! $evaluation['is_core'] && ! $evaluation['is_wpshadow_handler']) {
						$unsecured++;
					}
				}
			}
		}

		return array(
			'total' => $total,
			'unsecured' => $unsecured,
		);
	}

	/**
	 * Evaluate a single AJAX callback for known security patterns.
	 *
	 * @param callable $callback AJAX callback.
	 * @return array{is_core:bool,is_wpshadow_handler:bool}
	 */
	private static function evaluate_ajax_callback_security($callback): array
	{
		$is_core = false;
		$is_wpshadow_handler = false;
		$class_name = null;
		$file_path = null;

		try {
			if (is_string($callback)) {
				$reflection = new \ReflectionFunction($callback);
				$file_path = $reflection->getFileName();
			} elseif (is_array($callback) && isset($callback[0], $callback[1])) {
				$class_name = is_object($callback[0]) ? get_class($callback[0]) : (string) $callback[0];
				$reflection = new \ReflectionMethod($class_name, (string) $callback[1]);
				$file_path = $reflection->getFileName();
			} elseif ($callback instanceof \Closure) {
				$reflection = new \ReflectionFunction($callback);
				$file_path = $reflection->getFileName();
			}
		} catch (\ReflectionException $e) {
			// Reflection failed; treat as potentially unsecured.
		}

		if (null !== $class_name && is_subclass_of($class_name, '\\WPShadow\\Admin\\Ajax\\AJAX_Handler_Base')) {
			$is_wpshadow_handler = true;
		}

		if (null !== $file_path && defined('ABSPATH') && function_exists('wp_normalize_path')) {
			$normalized = wp_normalize_path($file_path);
			$core_paths = array(
				wp_normalize_path(ABSPATH . 'wp-admin'),
				wp_normalize_path(ABSPATH . 'wp-includes'),
			);

			foreach ($core_paths as $core_path) {
				if (0 === strpos($normalized, $core_path)) {
					$is_core = true;
					break;
				}
			}
		}

		return array(
			'is_core' => $is_core,
			'is_wpshadow_handler' => $is_wpshadow_handler,
		);
	}
}
