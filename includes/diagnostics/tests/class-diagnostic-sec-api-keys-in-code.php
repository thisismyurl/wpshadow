<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Diagnostic_Lean_Checks;

/**
 * Diagnostic: API Keys Exposed in Code
 *
 * Category: Security & Compliance
 * Priority: 1
 * Philosophy: 1
 *
 * Test Description:
 * Are API keys or secrets stored in code/config?
 *
 * @package WPShadow
 * @subpackage Diagnostics
 *
 * @verified 2026-01-24 - Continuous batch implementation
 * @guardian-integrated Pending
 */
class Diagnostic_Sec_Api_Keys_In_Code extends Diagnostic_Base {
	protected static $slug = 'sec-api-keys-in-code';
	protected static $title = 'API Keys Exposed in Code';
	protected static $description = 'Are API keys or secrets stored in code/config?';
	protected static $category = 'Security & Compliance';
	protected static $threat_level = 'high';
	protected static $family = 'general';
	protected static $family_label = 'General';

	/**
	 * Run the diagnostic check
	 *
	 * @return ?array Null if pass, array of findings if fail
	 */
	public function check(): ?array {
		// Check wp-config.php for common patterns
		$config_file = ABSPATH . 'wp-config.php';
		if ( ! file_exists( $config_file ) ) {
			return null;
		}

		$config_content = file_get_contents( $config_file );

		// Look for patterns that suggest exposed secrets
		$suspicious_patterns = [
			'/define\s*\(\s*[\'"].*API.*KEY.*[\'"]/i',
			'/define\s*\(\s*[\'"].*SECRET.*[\'"]/i',
			'/define\s*\(\s*[\'"].*TOKEN.*[\'"]/i',
		];

		$found_issues = false;
		foreach ( $suspicious_patterns as $pattern ) {
			if ( preg_match( $pattern, $config_content ) ) {
				// Check if value looks like actual credentials (long strings)
				if ( preg_match( $pattern . '.*[\'"]([\w\-]{20,})/', $config_content ) ) {
					$found_issues = true;
					break;
				}
			}
		}

		if ( $found_issues ) {
			return Diagnostic_Lean_Checks::build_finding(
				'sec-api-keys-in-code',
				'Potential Exposed Secrets',
				'Sensitive API keys or secrets appear to be stored in wp-config.php. Use environment variables or a secrets management system instead.',
				'Security & Compliance',
				'high',
				'critical'
			);
		}

		return null;
	}
}
