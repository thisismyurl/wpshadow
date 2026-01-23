<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

/**
 * Diagnostic: Head Cleanup - oEmbed Discovery Links
 * Checks if WordPress oEmbed discovery links are enabled and can be removed
 */
class Test_Performance_Head_Cleanup_OEmbed extends Diagnostic_Base {

	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Array with issue details or null if healthy
	 */
	public static function check(): ?array {
		if (!has_action('wp_head', 'wp_oembed_add_discovery_links')) {
			return null;
		}

		return array(
			'id'            => 'head-cleanup-oembed',
			'title'         => 'oEmbed Discovery Links Enabled',
			'threat_level'  => 12,
			'description'   => 'oEmbed discovery links are rarely used by modern sites. Removing them reduces page bloat and HTTP headers.',
		);
	}

	/**
	 * Test the diagnostic check
	 *
	 * @return array Test result with passed status and message
	 */
	public static function test_live_head_cleanup_oembed(): array {
		$result = self::check();
		return array(
			'passed'  => $result === null,
			'message' => $result === null ? 'oEmbed discovery links not enabled' : 'oEmbed discovery links detected',
		);
	}
}
