<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Test: PHP Extensions Check
 *
 * Validates that critical PHP extensions are available.
 *
 * @package     WPShadow\Diagnostics\Tests
 * @since       1.2601.2312
 * @category    System
 * @philosophy  #9 Show Value - Ensure required PHP extensions are available
 */
class Test_System_PHP_Extensions extends Diagnostic_Base {


	/**
	 * Check for required PHP extensions
	 *
	 * @return array|null Issues found or null if all extensions available
	 */
	public static function check(): ?array {
		$required_extensions = array(
			'curl',
			'json',
			'mbstring',
			'openssl',
			'pdo',
			'xml',
			'zlib',
		);

		$missing = array();
		foreach ( $required_extensions as $extension ) {
			if ( ! extension_loaded( $extension ) ) {
				$missing[] = $extension;
			}
		}

		if ( empty( $missing ) ) {
			return null; // All extensions available
		}

		return array(
			'id'           => 'php-extensions-missing',
			'title'        => 'Missing PHP Extensions',
			'description'  => 'Required PHP extensions are missing: ' . implode( ', ', $missing ),
			'threat_level' => 70,
		);
	}

	/**
	 * Live test for PHP extensions diagnostic
	 *
	 * @return array Test result with 'passed' and 'message' keys
	 */
	public static function test_live_php_extensions(): array {
		$result = self::check();

		// Test 1: Check each required extension manually
		$required_extensions = array(
			'curl',
			'json',
			'mbstring',
			'openssl',
			'pdo',
			'xml',
			'zlib',
		);

		$actually_missing = array();
		foreach ( $required_extensions as $extension ) {
			if ( ! extension_loaded( $extension ) ) {
				$actually_missing[] = $extension;
			}
		}

		// Test 2: Compare results
		if ( ! empty( $actually_missing ) ) {
			// Should return an issue
			if ( is_null( $result ) ) {
				return array(
					'passed'  => false,
					'message' => 'Missing extensions: ' . implode( ', ', $actually_missing ) . ', but check() returned null.',
				);
			}
		} else {
			// All extensions available
			if ( ! is_null( $result ) ) {
				return array(
					'passed'  => false,
					'message' => 'All extensions available, but check() returned: ' . wp_json_encode( $result ),
				);
			}
		}

		// All tests passed
		return array(
			'passed'  => true,
			'message' => 'PHP extensions check passed. All required extensions are available.',
		);
	}
}
