<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Test: Directory Permissions
 *
 * Validates that critical WordPress directories have proper permissions.
 *
 * @package     WPShadow\Diagnostics\Tests
 * @since       1.2601.2312
 * @category    System
 * @philosophy  #9 Show Value - Ensure file operations work without errors
 */
class Test_System_Directory_Permissions extends Diagnostic_Base {


	/**
	 * Check directory permissions
	 *
	 * @return array|null Issues found or null if all permissions OK
	 */
	public static function check(): ?array {
		$directories = array(
			'wp-content'         => WP_CONTENT_DIR,
			'uploads'            => wp_upload_dir()['basedir'] ?? null,
			'wp-content/plugins' => WP_PLUGIN_DIR,
		);

		$permission_issues = array();
		foreach ( $directories as $name => $path ) {
			if ( ! $path || ! is_dir( $path ) ) {
				continue;
			}

			if ( ! is_writable( $path ) ) {
				$permission_issues[] = $name;
			}
		}

		if ( empty( $permission_issues ) ) {
			return null; // All permissions OK
		}

		return array(
			'id'           => 'directory-permissions',
			'title'        => 'Directory Permission Issues',
			'description'  => 'Some WordPress directories are not writable: ' . implode( ', ', $permission_issues ),
			'threat_level' => 70,
		);
	}

	/**
	 * Live test for directory permissions diagnostic
	 *
	 * @return array Test result with 'passed' and 'message' keys
	 */
	public static function test_live_directory_permissions(): array {
		$result = self::check();

		// Test 1: Check each directory manually
		$directories = array(
			'wp-content'         => WP_CONTENT_DIR,
			'uploads'            => wp_upload_dir()['basedir'] ?? null,
			'wp-content/plugins' => WP_PLUGIN_DIR,
		);

		$actually_not_writable = array();
		foreach ( $directories as $name => $path ) {
			if ( ! $path || ! is_dir( $path ) ) {
				continue;
			}

			if ( ! is_writable( $path ) ) {
				$actually_not_writable[] = $name;
			}
		}

		// Test 2: Compare results
		if ( ! empty( $actually_not_writable ) ) {
			// Should return an issue
			if ( is_null( $result ) ) {
				return array(
					'passed'  => false,
					'message' => 'Not writable: ' . implode( ', ', $actually_not_writable ) . ', but check() returned null.',
				);
			}
		} else {
			// All directories writable
			if ( ! is_null( $result ) ) {
				return array(
					'passed'  => false,
					'message' => 'All directories writable, but check() returned: ' . wp_json_encode( $result ),
				);
			}
		}

		// All tests passed
		return array(
			'passed'  => true,
			'message' => 'Directory permissions check passed. All critical directories are writable.',
		);
	}
}
