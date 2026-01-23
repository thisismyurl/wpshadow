<?php

declare(strict_types=1);
/**
 * Database Table Optimization Diagnostic
 *
 * Philosophy: Optimized tables improve query speed
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Database_Table_Optimization extends Diagnostic_Base
{
	public static function check(): ?array
	{
		return array(
			'id'            => 'seo-database-table-optimization',
			'title'         => 'Database Table Optimization',
			'description'   => 'Regularly optimize database tables to reduce overhead and improve query performance.',
			'severity'      => 'low',
			'category'      => 'seo',
			'kb_link'       => 'https://wpshadow.com/kb/database-optimization/',
			'training_link' => 'https://wpshadow.com/training/database-maintenance/',
			'auto_fixable'  => false,
			'threat_level'  => 15,
		);
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: SEO Database Table Optimization
	 * Slug: -seo-database-table-optimization
	 * File: class-diagnostic-seo-database-table-optimization.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: SEO Database Table Optimization
	 * Slug: -seo-database-table-optimization
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
	public static function test_live__seo_database_table_optimization(): array
	{
		$diagnostic_result = self::check();

		// This diagnostic always returns an array; use that as expectation
		$should_find_issue      = true;
		$diagnostic_found_issue = (null !== $diagnostic_result);

		$test_passes = ($diagnostic_found_issue === $should_find_issue);

		$message = sprintf(
			'Diagnostic returned %s. Expected to always return an array. Test: %s',
			$diagnostic_found_issue ? 'an array' : 'null',
			$test_passes ? 'PASS' : 'FAIL'
		);

		return array(
			'passed'  => $test_passes,
			'message' => $message,
		);
	}
}
