<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Diagnostic_Lean_Checks;

/**
 * Diagnostic: Debug Mode Enabled
 *
 * Category: Environment & Infrastructure
 * Priority: 2
 * Philosophy: 1
 *
 * Test Description:
 * Is WordPress debug mode enabled in production?
 *
 * @package WPShadow
 * @subpackage Diagnostics
 *
 * @verified 2026-01-24 - Batch 4 implementation
 * @guardian-integrated Pending
 */
class Diagnostic_Debug_Mode_Enabled extends Diagnostic_Base {

	protected static $slug         = 'debug-mode-enabled';
	protected static $title        = 'Debug Mode Enabled';
	protected static $description  = 'Is WordPress debug mode enabled?';
	protected static $category     = 'Environment & Infrastructure';
	protected static $threat_level = 'medium';
	protected static $family       = 'general';
	protected static $family_label = 'General';

	/**
	 * Run the diagnostic check
	 *
	 * @return ?array Null if pass, array of findings if fail
	 */
	public function check(): ?array {
		// Check if debug mode is enabled
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			// This is a security risk in production
			return Diagnostic_Lean_Checks::build_finding(
				'debug-mode-enabled',
				'Debug Mode Enabled',
				'WordPress debug mode is enabled. This can expose sensitive information. Disable it in production.',
				'Environment & Infrastructure',
				'medium',
				'high'
			);
		}

		// Also check debug log
		if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
			return Diagnostic_Lean_Checks::build_finding(
				'debug-mode-enabled',
				'Debug Log Enabled',
				'WordPress debug logging is enabled. Consider disabling in production.',
				'Environment & Infrastructure',
				'low',
				'low'
			);
		}

		return null;
	}
}
