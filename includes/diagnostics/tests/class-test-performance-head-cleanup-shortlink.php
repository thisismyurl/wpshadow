<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

/**
 * Diagnostic: Head Cleanup - WordPress Shortlink
 * Checks if WordPress shortlink functionality is enabled and can be removed
 */
class Test_Performance_Head_Cleanup_Shortlink extends Diagnostic_Base
{

	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Array with issue details or null if healthy
	 */
	public static function check(): ?array
	{
		if (!has_action('wp_head', 'wp_shortlink_wp_head')) {
			return null;
		}

		return array(
			'id'            => 'head-cleanup-shortlink',
			'title'         => 'WordPress Shortlink Enabled',
			'threat_level'  => 10,
			'description'   => 'The WordPress shortlink feature is rarely used in modern sites. Removing it reduces page headers and improves performance.',
		);
	}

	/**
	 * Test the diagnostic check
	 *
	 * @return array Test result with passed status and message
	 */
	public static function test_live_head_cleanup_shortlink(): array
	{
		$result = self::check();
		return array(
			'passed'  => $result === null,
			'message' => $result === null ? 'Shortlink is disabled' : 'Shortlink found',
		);
	}
}
