<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

/**
 * Diagnostic: Database Scheduled Tasks
 * Checks for pending scheduled cron tasks
 */
class Test_Database_Scheduled_Tasks extends Diagnostic_Base {


	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Array with issue details or null if healthy
	 */
	public static function check(): ?array {
		global $wpdb;

		$scheduled_tasks = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'scheduled_task' AND post_status = 'pending'"
		);

		if ( $scheduled_tasks > 1000 ) {
			return array(
				'id'           => 'database-scheduled-tasks',
				'title'        => 'Large Queue of Scheduled Tasks',
				'threat_level' => 35,
				'description'  => sprintf(
					'Database has %d pending scheduled tasks. Check cron execution.',
					$scheduled_tasks
				),
			);
		}

		return null;
	}

	/**
	 * Test the diagnostic check
	 *
	 * @return array Test result with passed status and message
	 */
	public static function test_live_scheduled_tasks(): array {
		$result = self::check();
		return array(
			'passed'  => $result === null,
			'message' => $result === null ? 'Scheduled task queue is normal' : 'Large task queue detected',
		);
	}
}
