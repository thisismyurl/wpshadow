<?php
/**
 * File Editors Disable Treatment
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\KPI_Tracker;

/**
 * Treatment to disable theme/plugin file editors by setting DISALLOW_FILE_EDIT.
 */
class Treatment_File_Editors extends Treatment_Base {
	/**
	 * Finding ID this treatment addresses.
	 *
	 * @return string
	 */
	public static function get_finding_id() {
		return 'file-editors-enabled';
	}

	/**
	 * Check if treatment can be applied.
	 *
	 * @return bool
	 */
	public static function can_apply() {
		$config_file = self::get_wp_config_path();
		return file_exists( $config_file ) && is_writable( $config_file );
	}

	/**
	 * Apply the treatment.
	 *
	 * @return array
	 */
	public static function apply() {
		$config_file = self::get_wp_config_path();

		if ( ! self::can_apply() ) {
			return array(
				'success' => false,
				'message' => 'wp-config.php is not writable. Update permissions or disable editors manually.',
			);
		}

		$config_content = file_get_contents( $config_file );
		$modified = false;

		$patterns = array(
			"/define\\(\\s*'DISALLOW_FILE_EDIT'\\s*,\\s*false\\s*\\);/i",
			'/define\\(\\s*"DISALLOW_FILE_EDIT"\\s*,\\s*false\\s*\\);/i',
		);

		foreach ( $patterns as $pattern ) {
			if ( preg_match( $pattern, $config_content ) ) {
				$config_content = preg_replace(
					$pattern,
					"define( 'DISALLOW_FILE_EDIT', true );",
					$config_content
				);
				$modified = true;
				break;
			}
		}

		// If not defined, add before the end marker.
		if ( ! $modified ) {
			if ( strpos( $config_content, 'DISALLOW_FILE_EDIT' ) === false ) {
				$config_content = preg_replace(
					"/(\/\*\*?\\s*That's all[^*]*\*+\/)/i",
					"define( 'DISALLOW_FILE_EDIT', true );\n\n$1",
					$config_content
				);
				$modified = true;
			}
		}

		if ( ! $modified ) {
			return array(
				'success' => false,
				'message' => 'Could not update DISALLOW_FILE_EDIT in wp-config.php.',
			);
		}

		copy( $config_file, $config_file . '.bak' );

		if ( false === file_put_contents( $config_file, $config_content ) ) {
			return array(
				'success' => false,
				'message' => 'Failed to write wp-config.php. Please check file permissions.',
			);
		}

		KPI_Tracker::log_fix_applied( self::get_finding_id(), 'auto' );

		return array(
			'success' => true,
			'message' => 'Theme and plugin editors disabled in wp-config.php.',
		);
	}

	/**
	 * Undo treatment (best effort restore from backup).
	 *
	 * @return array
	 */
	public static function undo() {
		$config_file = self::get_wp_config_path();
		$backup_file = $config_file . '.bak';

		if ( ! file_exists( $backup_file ) ) {
			return array(
				'success' => false,
				'message' => 'No backup available to restore.',
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
			'message' => 'wp-config.php restored from backup.',
		);
	}

	/**
	 * Locate wp-config.php.
	 *
	 * @return string
	 */
	private static function get_wp_config_path() {
		$config_file = ABSPATH . 'wp-config.php';
		if ( ! file_exists( $config_file ) ) {
			$config_file = dirname( ABSPATH ) . '/wp-config.php';
		}
		return $config_file;
	}
}
