<?php
/**
 * Memory Limit Treatment
 *
 * Handles automatic fixing of low PHP memory limits
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\KPI_Tracker;

/**
 * Treatment for increasing PHP memory limit
 */
class Treatment_Memory_Limit extends Treatment_Base {
	/**
	 * Get the finding ID this treatment addresses
	 *
	 * @return string
	 */
	public static function get_finding_id() {
		return 'memory-limit-low';
	}
	
	/**
	 * Check if this treatment can be applied
	 *
	 * @return bool True if treatment can run.
	 */
	public static function can_apply() {
		$config_file = self::get_wp_config_path();
		return file_exists( $config_file ) && is_writable( $config_file );
	}
	
	/**
	 * Apply the treatment/fix
	 *
	 * @return array Result with 'success' bool and 'message' string.
	 */
	public static function apply() {
		$config_file = self::get_wp_config_path();
		
		if ( ! self::can_apply() ) {
			return array(
				'success' => false,
				'message' => 'wp-config.php is not writable. Contact your hosting provider.',
			);
		}
		
		$config_content = file_get_contents( $config_file );
		
		// Check if already defined
		if ( preg_match( '/define\(\s*[\'"]WP_MEMORY_LIMIT[\'"]/i', $config_content ) ) {
			return array(
				'success' => false,
				'message' => 'Memory limit is already defined in wp-config.php.',
			);
		}
		
		// Add memory limit definition before wp-settings inclusion
		$new_line = "define( 'WP_MEMORY_LIMIT', '256M' );\n";
		$config_content = preg_replace(
			'/require_once.*wp-settings\.php/i',
			$new_line . "require_once( ABSPATH . 'wp-settings.php' )",
			$config_content
		);
		
		// Save backup
		copy( $config_file, $config_file . '.bak' );
		
		// Write new config
		if ( ! file_put_contents( $config_file, $config_content ) ) {
			return array(
				'success' => false,
				'message' => 'Failed to write to wp-config.php.',
			);
		}
		
		// Track KPI
		KPI_Tracker::log_fix_applied( self::get_finding_id(), 'auto' );
		
		return array(
			'success' => true,
			'message' => 'PHP memory limit increased to 256MB. A backup of wp-config.php was created.',
		);
	}
	
	/**
	 * Undo the treatment (if possible)
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
				'message' => 'Failed to restore from backup.',
			);
		}
		
		return array(
			'success' => true,
			'message' => 'Memory limit configuration removed. Restored from backup.',
		);
	}
	
	/**
	 * Get wp-config.php path
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
