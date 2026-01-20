<?php
/**
 * Error Log Rotation Treatment
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\KPI_Tracker;

/**
 * Treatment to rotate/truncate debug.log.
 */
class Treatment_Error_Log implements Treatment_Interface {
	public static function get_finding_id() {
		return 'error-log-large';
	}
	
	public static function can_apply() {
		$path = self::get_log_path();
		return $path && file_exists( $path ) && is_writable( $path );
	}
	
	public static function apply() {
		$path = self::get_log_path();
		if ( ! $path || ! file_exists( $path ) ) {
			return array(
				'success' => false,
				'message' => 'debug.log not found.',
			);
		}
		
		$backup = $path . '.' . time() . '.bak';
		if ( ! @copy( $path, $backup ) ) {
			return array(
				'success' => false,
				'message' => 'Could not back up debug.log.',
			);
		}
		
		if ( false === file_put_contents( $path, '' ) ) {
			return array(
				'success' => false,
				'message' => 'Failed to truncate debug.log.',
			);
		}
		
		KPI_Tracker::log_fix_applied( self::get_finding_id(), 'auto' );
		
		return array(
			'success' => true,
			'message' => 'debug.log truncated and backed up.',
		);
	}
	
	public static function undo() {
		return array(
			'success' => false,
			'message' => 'Cannot automatically restore previous log (backup retained).',
		);
	}
	
	private static function get_log_path() {
		$path = ini_get( 'error_log' );
		if ( $path && is_string( $path ) && file_exists( $path ) ) {
			return $path;
		}
		
		$default = WP_CONTENT_DIR . '/debug.log';
		return file_exists( $default ) ? $default : null;
	}
}
