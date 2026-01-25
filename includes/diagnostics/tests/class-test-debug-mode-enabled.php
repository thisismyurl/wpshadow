<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

/**
 * Diagnostic: Debug Mode Enabled
 *
 * Detects when WordPress debug mode is enabled in production.
 * Debug mode exposes sensitive information and should only be used in development.
 *
 * @since 1.2.0
 */
class Test_Debug_Mode_Enabled extends Diagnostic_Base {


	/**
	 * Check for debug mode enabled
	 *
	 * @return array|null Diagnostic array if issues found, null if all good
	 */
	public static function check(): ?array {
		$debug_enabled = self::is_debug_enabled();

		if ( ! $debug_enabled ) {
			return null;
		}

		$threat = 75;

		return array(
			'threat_level'  => $threat,
			'threat_color'  => 'red',
			'passed'        => false,
			'issue'         => 'Debug mode enabled - exposes sensitive information',
			'metadata'      => array(
				'wp_debug'      => defined( 'WP_DEBUG' ) ? WP_DEBUG : false,
				'debug_log'     => defined( 'WP_DEBUG_LOG' ) ? WP_DEBUG_LOG : false,
				'debug_display' => defined( 'WP_DEBUG_DISPLAY' ) ? WP_DEBUG_DISPLAY : false,
				'script_debug'  => defined( 'SCRIPT_DEBUG' ) ? SCRIPT_DEBUG : false,
				'log_file'      => defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ? WP_CONTENT_DIR . '/debug.log' : 'Not set',
				'log_exists'    => file_exists( WP_CONTENT_DIR . '/debug.log' ),
			),
			'kb_link'       => 'https://wpshadow.com/kb/wordpress-debug-mode/',
			'training_link' => 'https://wpshadow.com/training/wordpress-security-best-practices/',
		);
	}

	/**
	 * Guardian Sub-Test: WP_DEBUG constant status
	 *
	 * @return array Test result
	 */
	public static function test_wp_debug(): array {
		$enabled = defined( 'WP_DEBUG' ) && WP_DEBUG;

		return array(
			'test_name'    => 'WP_DEBUG Status',
			'status'       => $enabled ? 'Enabled' : 'Disabled',
			'constant_set' => defined( 'WP_DEBUG' ),
			'passed'       => ! $enabled,
			'description'  => $enabled ? 'WP_DEBUG is ON (security risk in production)' : 'WP_DEBUG is OFF (correct)',
		);
	}

	/**
	 * Guardian Sub-Test: Debug logging
	 *
	 * @return array Test result
	 */
	public static function test_debug_logging(): array {
		$log_enabled = defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG;
		$log_file    = WP_CONTENT_DIR . '/debug.log';
		$log_exists  = file_exists( $log_file );
		$log_size    = $log_exists ? filesize( $log_file ) : 0;

		return array(
			'test_name'       => 'Debug Logging',
			'logging_enabled' => $log_enabled,
			'log_file_exists' => $log_exists,
			'log_file_path'   => $log_file,
			'log_file_size'   => self::format_bytes( $log_size ),
			'passed'          => ! $log_enabled,
			'description'     => $log_enabled ? 'Debug logging ENABLED (contains sensitive data)' : 'Debug logging disabled',
		);
	}

	/**
	 * Guardian Sub-Test: Debug display status
	 *
	 * @return array Test result
	 */
	public static function test_debug_display(): array {
		$display_enabled = defined( 'WP_DEBUG_DISPLAY' ) && WP_DEBUG_DISPLAY;

		$risk = $display_enabled ? 'Critical' : 'Low';

		return array(
			'test_name'       => 'Debug Display',
			'display_enabled' => $display_enabled,
			'risk_level'      => $risk,
			'passed'          => ! $display_enabled,
			'description'     => $display_enabled ? 'Errors displayed to frontend (critical risk)' : 'Errors not displayed',
		);
	}

	/**
	 * Guardian Sub-Test: SCRIPT_DEBUG status
	 *
	 * @return array Test result
	 */
	public static function test_script_debug(): array {
		$enabled = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG;

		return array(
			'test_name'   => 'SCRIPT_DEBUG Status',
			'status'      => $enabled ? 'Enabled' : 'Disabled',
			'passed'      => ! $enabled,
			'description' => $enabled ? 'SCRIPT_DEBUG ON - loads uncompressed assets' : 'Using production-optimized assets',
		);
	}

	/**
	 * Guardian Sub-Test: Debug log file analysis
	 *
	 * @return array Test result
	 */
	public static function test_debug_log_contents(): array {
		$log_file       = WP_CONTENT_DIR . '/debug.log';
		$log_exists     = file_exists( $log_file );
		$errors_count   = 0;
		$warnings_count = 0;

		if ( $log_exists && is_readable( $log_file ) ) {
			$log_content    = file_get_contents( $log_file );
			$errors_count   = substr_count( $log_content, 'Error' );
			$warnings_count = substr_count( $log_content, 'Warning' );
		}

		return array(
			'test_name'       => 'Debug Log Contents',
			'log_file_exists' => $log_exists,
			'errors_in_log'   => $errors_count,
			'warnings_in_log' => $warnings_count,
			'description'     => $log_exists ? sprintf( 'Log contains %d errors, %d warnings', $errors_count, $warnings_count ) : 'No debug log found',
		);
	}

	/**
	 * Check if debug mode is enabled
	 *
	 * @return bool
	 */
	private static function is_debug_enabled(): bool {
		return ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ||
			( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) ||
			( defined( 'WP_DEBUG_DISPLAY' ) && WP_DEBUG_DISPLAY ) ||
			( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG );
	}

	/**
	 * Format bytes as human-readable
	 *
	 * @param int $bytes Byte count
	 * @return string Formatted size
	 */
	private static function format_bytes( int $bytes ): string {
		$units  = array( 'B', 'KB', 'MB', 'GB' );
		$bytes  = max( $bytes, 0 );
		$pow    = floor( ( $bytes ? log( $bytes ) : 0 ) / log( 1024 ) );
		$pow    = min( $pow, count( $units ) - 1 );
		$bytes /= ( 1 << ( 10 * $pow ) );

		return round( $bytes, 2 ) . ' ' . $units[ $pow ];
	}

	/**
	 * Get diagnostic name
	 *
	 * @return string
	 */
	public static function get_name(): string {
		return 'Debug Mode Enabled';
	}

	/**
	 * Get diagnostic description
	 *
	 * @return string
	 */
	public static function get_description(): string {
		return 'Checks if WordPress debug mode is safely configured';
	}

	/**
	 * Get diagnostic category
	 *
	 * @return string
	 */
	public static function get_category(): string {
		return 'Security';
	}
}
