<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

/**
 * Diagnostic: Head Cleanup - RSD Link
 * Checks if RSD (Really Simple Discovery) link is enabled and can be removed
 */
class Test_Security_Head_Cleanup_RSD extends Diagnostic_Base {


	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Array with issue details or null if healthy
	 */
	public static function check(): ?array {
		if ( ! has_action( 'wp_head', 'rsd_link' ) ) {
			return null;
		}

		return array(
			'id'           => 'head-cleanup-rsd',
			'title'        => 'RSD (Really Simple Discovery) Link Enabled',
			'threat_level' => 18,
			'description'  => 'The RSD link is legacy from the XML-RPC era and is unnecessary for modern WordPress sites. Removing it improves security and reduces page noise.',
		);
	}

	/**
	 * Test the diagnostic check
	 *
	 * @return array Test result with passed status and message
	 */
	public static function test_live_head_cleanup_rsd(): array {
		$result = self::check();
		return array(
			'passed'  => $result === null,
			'message' => $result === null ? 'RSD link is disabled' : 'RSD link detected',
		);
	}
}
