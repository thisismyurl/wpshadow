<?php
/**
 * Debug Mode Treatment
 *
 * Handles disabling WP_DEBUG when enabled on production sites.
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\KPI_Tracker;

/**
 * Treatment for disabling WordPress debug mode.
 */
class Treatment_Debug_Mode extends Treatment_Base {
	/**
	 * Get the finding ID this treatment addresses.
	 *
	 * @return string
	 */
	public static function get_finding_id() {
		return 'debug-mode-enabled';
	}

	/**
	 * Check if this treatment can be applied.
	 *
	 * @return bool True if treatment can run.
	 */
	public static function can_apply() {
		$config_file = self::get_wp_config_path();
		return file_exists( $config_file ) && is_writable( $config_file );
	}

	/**
	 * Apply the treatment/fix.
	 *
	 * @return array Result with 'success' bool and 'message' string.
	 */
	public static function apply() {
		$config_file = self::get_wp_config_path();

		if ( ! self::can_apply() ) {
			return array(
				'success' => false,
				'message' => 'wp-config.php is not writable. Contact your hosting provider to disable debug mode.',
			);
		}

		$config_content = file_get_contents( $config_file );
		$patterns       = array(
			"/define\\(\s*'WP_DEBUG'\s*,\s*true\s*\);/i",
			'/define\\(\s*"WP_DEBUG"\s*,\s*true\s*\);/i',
		);

		$modified = false;
		foreach ( $patterns as $pattern ) {
			if ( preg_match( $pattern, $config_content ) ) {
				$config_content = preg_replace(
					$pattern,
					"define( 'WP_DEBUG', false );",
					$config_content
				);
				$modified       = true;
				break;
			}
		}

		// If no WP_DEBUG define found, add a safe default before the end marker.
		if ( ! $modified ) {
			if ( strpos( $config_content, 'WP_DEBUG' ) === false ) {
				$config_content = preg_replace(
					"/(\/\*\*?\s*That's all[^*]*\*+\/)/i",
					"define( 'WP_DEBUG', false );\n\n$1",
					$config_content
				);
				$modified       = true;
			}
		}

		if ( ! $modified ) {
			return array(
				'success' => false,
				'message' => 'Could not locate WP_DEBUG definition to update.',
			);
		}

		// Save backup
		copy( $config_file, $config_file . '.bak' );

		if ( false === file_put_contents( $config_file, $config_content ) ) {
			return array(
				'success' => false,
				'message' => 'Failed to write to wp-config.php. Please check file permissions.',
			);
		}

		KPI_Tracker::log_fix_applied( self::get_finding_id(), 'auto' );

		return array(
			'success' => true,
			'message' => 'Debug mode disabled successfully in wp-config.php.',
		);
	}

	/**
	 * Undo the treatment (best effort by restoring backup if present).
	 *
	 * @return array Result with 'success' bool and 'message' string.
	 */
	public static function undo() {
		$config_file = self::get_wp_config_path();
		$backup_file = $config_file . '.bak';

		if ( ! file_exists( $backup_file ) ) {
			return array(
				'success' => false,
				'message' => 'Backup file not found. Cannot undo.',
			);
		}

		if ( ! copy( $backup_file, $config_file ) ) {
			return array(
				'success' => false,
				'message' => 'Failed to restore wp-config.php from backup.',
			);
		}

		return array(
			'success' => true,
			'message' => 'wp-config.php restored from backup. WP_DEBUG setting reverted.',
		);
	}

	/**
	 * Get wp-config.php path.
	 *
	 * @return string Path to wp-config.php
	 */
	private static function get_wp_config_path() {
		$config_file = ABSPATH . 'wp-config.php';
		if ( ! file_exists( $config_file ) ) {
			$config_file = dirname( ABSPATH ) . '/wp-config.php';
		}
		return $config_file;
	}
}
