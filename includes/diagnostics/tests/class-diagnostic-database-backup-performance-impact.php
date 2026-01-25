<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Database Backup Performance Impact (DB-019)
 * Detects if backups run during peak traffic and hurt performance.
 * Philosophy: Helpful neighbor (#1), show value (#9) by timing guidance.
 * KB: https://wpshadow.com/kb/database-backup-performance
 * Training: https://wpshadow.com/training/backup-scheduling
 */
class Diagnostic_Database_Backup_Performance_Impact extends Diagnostic_Base {

	/**
	 * OPEN QUESTIONS (need guidance):
	 * - Signal source: Do we inspect recent backup job timestamps (from common plugins) vs. generic cron events containing "backup"?
	 * - Peak window: What hours define peak? Should this use traffic heuristics (visits log) or a fixed local-time window (e.g., 8a-8p)?
	 * - Impact metric: Should we check overlap between backup runs and high DB load (e.g., queries per second, slow query log hints) or keep it lightweight?
	 * - Plugin coverage: Which backup plugins should we detect (UpdraftPlus, Jetpack Backup, Duplicator, etc.) and where to read their schedules safely?
	 * - Thresholds: When do we warn vs. fail (e.g., >1 concurrent heavy backup in peak, or backup duration > X minutes during peak)?
	 */
	public static function check(): ?array {
		// Pending implementation until scope, signals, and thresholds are defined.
		return null;
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Database Backup Performance Impact
	 * Slug: -database-backup-performance-impact
	 * File: class-diagnostic-database-backup-performance-impact.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Database Backup Performance Impact
	 * Slug: -database-backup-performance-impact
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
	public static function test_live__database_backup_performance_impact(): array {
		/*
		 * IMPLEMENTATION NOTES:
		 * - This test validates the actual WordPress site state
		 * - Do not use mocks or stubs
		 * - Call self::check() to get the diagnostic result
		 * - Verify the result matches expected site state
		 * - Return [ 'passed' => bool, 'message' => string ]
		 */

		$result = self::check();

		return array(
			'passed'  => is_null( $result ),
			'message' => 'Diagnostic not yet implemented; awaiting scheduling/impact rules',
		);
	}
}
