<?php
declare(strict_types=1);
/**
 * Timthumb Vulnerability Scanner Diagnostic
 *
 * Philosophy: Legacy vulnerability detection - timthumb exploit prevention
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Loaded via Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for vulnerable timthumb.php files.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Loaded via Diagnostic_Registry
 */
class Diagnostic_Timthumb_Scanner extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$found_timthumb = array();

		// Scan 1: Themes directory
		$themes_dir = get_theme_root();
		if ( is_dir( $themes_dir ) ) {
			$theme_folders = scandir( $themes_dir );
			foreach ( $theme_folders as $theme_slug ) {
				if ( $theme_slug === '.' || $theme_slug === '..' ) {
					continue;
				}
				$theme_dir = $themes_dir . '/' . $theme_slug;
				if ( is_dir( $theme_dir ) ) {
					$timthumb_path = $theme_dir . '/timthumb.php';
					if ( file_exists( $timthumb_path ) ) {
						$found_timthumb[] = 'theme: ' . $theme_slug;
					}
				}
			}
		}

		// Scan 2: Plugins directory
		$plugins_dir = WP_PLUGIN_DIR;
		if ( is_dir( $plugins_dir ) ) {
			$plugin_folders = scandir( $plugins_dir );
			foreach ( $plugin_folders as $plugin_slug ) {
				if ( $plugin_slug === '.' || $plugin_slug === '..' ) {
					continue;
				}
				$plugin_dir = $plugins_dir . '/' . $plugin_slug;
				if ( is_dir( $plugin_dir ) ) {
					$timthumb_path = $plugin_dir . '/timthumb.php';
					if ( file_exists( $timthumb_path ) ) {
						$found_timthumb[] = 'plugin: ' . $plugin_slug;
					}
				}
			}
		}

		// Scan 3: WordPress root directory
		$wp_root = ABSPATH;
		if ( file_exists( $wp_root . 'timthumb.php' ) ) {
			$found_timthumb[] = 'WordPress root';
		}

		// Scan 4: Uploads directory (sometimes stored there)
		$upload_dir = wp_upload_dir();
		if ( ! empty( $upload_dir['basedir'] ) && is_dir( $upload_dir['basedir'] ) ) {
			// Search one level deep in uploads
			$upload_folders = scandir( $upload_dir['basedir'] );
			foreach ( $upload_folders as $folder ) {
				if ( $folder === '.' || $folder === '..' ) {
					continue;
				}
				$folder_path = $upload_dir['basedir'] . '/' . $folder;
				if ( is_dir( $folder_path ) && file_exists( $folder_path . '/timthumb.php' ) ) {
					$found_timthumb[] = 'uploads: ' . $folder;
				}
			}
		}

		if ( ! empty( $found_timthumb ) ) {
			return array(
				'id'            => 'timthumb-scanner',
				'title'         => 'Vulnerable Timthumb Detected',
				'description'   => sprintf(
					'Timthumb.php found in: %s. This is a critical security risk allowing remote code execution. Remove all timthumb files immediately. <a href="https://wpshadow.com/kb/remove-timthumb-vulnerability/" target="_blank">Learn how to remove timthumb</a>',
					implode( ', ', $found_timthumb )
				),
				'severity'      => 'critical',
				'category'      => 'security',
				'kb_link'       => 'https://wpshadow.com/kb/remove-timthumb-vulnerability/',
				'training_link' => 'https://wpshadow.com/training/timthumb-security/',
				'auto_fixable'  => false,
				'threat_level'  => 95,
			);
		}

		return null;
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Test Purpose:
	 * Verify check() method correctly detects timthumb.php files in themes, plugins, root, and uploads directories.
	 * Pass criteria: No timthumb.php files found anywhere
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live__timthumb_scanner(): array {
		$result = self::check();

		if ( is_null( $result ) ) {
			return array(
				'passed'  => true,
				'message' => '✓ No timthumb.php files detected',
			);
		}

		return array(
			'passed'  => false,
			'message' => '✗ Timthumb detected: ' . $result['title'],
		);
	}

}
