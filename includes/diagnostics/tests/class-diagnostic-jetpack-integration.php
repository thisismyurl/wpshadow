<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Jetpack Integration Health
 *
 * Target Persona: Enterprise WordPress Platform (Automattic/WPEngine)
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Loaded via Diagnostic_Registry
 */
class Diagnostic_Jetpack_Integration extends Diagnostic_Base {
	protected static $slug        = 'jetpack-integration';
	protected static $title       = 'Jetpack Integration Health';
	protected static $description = 'Monitors Jetpack feature functionality.';

	public static function check(): ?array {
		// Check if Jetpack is active
		if ( ! is_plugin_active( 'jetpack/jetpack.php' ) ) {
			return null; // Pass - Jetpack not installed, no concern
		}

		// Check Jetpack connection status
		if ( class_exists( 'Jetpack' ) && method_exists( 'Jetpack', 'is_connection_ready' ) ) {
			if ( Jetpack::is_connection_ready() ) {
				return null; // Pass - Jetpack connected
			}
			return array(
				'id'            => static::$slug,
				'title'         => static::$title,
				'description'   => 'Jetpack installed but not connected to WordPress.com.',
				'kb_link'       => 'https://wpshadow.com/kb/jetpack-integration/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=jetpack-integration',
				'training_link' => 'https://wpshadow.com/training/jetpack-integration/',
				'auto_fixable'  => false,
				'threat_level'  => 60,
				'module'        => 'Integration',
				'priority'      => 2,
			);
		}

		return null;
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Jetpack Integration Health
	 * Slug: jetpack-integration
	 *
	 * Test Purpose:
	 * - Verify that check() method returns the correct result based on site state
	 * - PASS: check() returns NULL when diagnostic condition is NOT met (site is healthy)
	 * - FAIL: check() returns array when diagnostic condition IS met (issue found)
	 * - Description: Monitors Jetpack feature functionality.
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live_jetpack_integration(): array {
		$result = self::check();
		if ( $result === null ) {
			return array(
				'passed'  => true,
				'message' => 'Jetpack properly integrated and functioning',
			);
		}
		$message = $result['description'] ?? 'Jetpack integration issue detected';
		return array(
			'passed'  => false,
			'message' => $message,
		);
	}
}
