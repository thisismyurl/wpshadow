<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

/**
 * Diagnostic: WP-Content Writable
 * Checks if wp-content directory is writable (allows plugin installation)
 */
class Test_Filesystem_WP_Content_Permissions extends Diagnostic_Base {

	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Array with issue details or null if healthy
	 */
	public static function check(): ?array {
		if (!is_writable(WP_CONTENT_DIR)) {
			return array(
				'id'            => 'filesystem-wp-content-writable',
				'title'         => 'WP-Content Directory Not Writable',
				'threat_level'  => 55,
				'description'   => 'WP-content directory lacks write permissions. Plugin installation may fail.',
			);
		}

		return null;
	}

	/**
	 * Test the diagnostic check
	 *
	 * @return array Test result with passed status and message
	 */
	public static function test_live_wp_content_writable(): array {
		$result = self::check();
		return array(
			'passed'  => $result === null,
			'message' => $result === null ? 'WP-content is writable' : 'WP-content not writable',
		);
	}
}
