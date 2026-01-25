<?php

/**
 * WPShadow System Diagnostic Test: PHP Configuration Settings
 *
 * Tests critical PHP ini settings: memory_limit, upload_max_filesize,
 * max_execution_time, post_max_size, session handlers, etc.
 *
 * Testable via: ini_get()
 * Can be requested by Guardian: "test-php-ini-memory", "test-php-ini-upload", etc.
 *
 * @package     WPShadow\Diagnostics\Tests
 * @since       1.2601.2312
 * @category    System
 * @philosophy  #7 Ridiculously Good - Optimal PHP configuration
 */

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Test: PHP Configuration Settings
 *
 * Main diagnostic that checks critical PHP ini settings.
 * Can request specific setting tests via Guardian.
 *
 * @verified Not yet tested
 */
class Test_PHP_INI_Settings extends Diagnostic_Base {


	protected static $slug        = 'php-ini-settings';
	protected static $title       = 'PHP Configuration Settings';
	protected static $description = 'Checks critical PHP ini settings for optimal WordPress performance.';

	/**
	 * Run the diagnostic test
	 *
	 * @return array|null Diagnostic result array, or null if no issue found
	 */
	public function check(): ?array {
		$issues = array();

		// Check memory limit
		$memory_limit = self::get_bytes_value( ini_get( 'memory_limit' ) );
		if ( $memory_limit < 67108864 ) { // 64MB
			$issues[] = array(
				'setting'     => 'memory_limit',
				'current'     => ini_get( 'memory_limit' ),
				'recommended' => '256M',
				'impact'      => 'Out-of-memory errors, plugin/theme failures',
			);
		}

		// Check upload_max_filesize
		$upload_max = self::get_bytes_value( ini_get( 'upload_max_filesize' ) );
		if ( $upload_max < 2097152 ) { // 2MB
			$issues[] = array(
				'setting'     => 'upload_max_filesize',
				'current'     => ini_get( 'upload_max_filesize' ),
				'recommended' => '64M',
				'impact'      => 'Cannot upload files larger than ' . ini_get( 'upload_max_filesize' ),
			);
		}

		// Check post_max_size
		$post_max = self::get_bytes_value( ini_get( 'post_max_size' ) );
		if ( $post_max < $upload_max ) {
			$issues[] = array(
				'setting'            => 'post_max_size',
				'current'            => ini_get( 'post_max_size' ),
				'should_be_at_least' => ini_get( 'upload_max_filesize' ),
				'impact'             => 'POST requests truncated, data loss possible',
			);
		}

		// Check max_execution_time
		$max_exec = (int) ini_get( 'max_execution_time' );
		if ( $max_exec > 0 && $max_exec < 30 ) {
			$issues[] = array(
				'setting'     => 'max_execution_time',
				'current'     => $max_exec . 's',
				'recommended' => '60s',
				'impact'      => 'Long operations timeout, backups fail',
			);
		}

		// Check session handler
		$session_handler = ini_get( 'session.save_handler' );
		$session_path    = ini_get( 'session.save_path' );
		if ( $session_handler === 'files' && ( empty( $session_path ) || ! is_writable( $session_path ) ) ) {
			$issues[] = array(
				'setting' => 'session.save_path',
				'current' => $session_path ?: 'default',
				'issue'   => 'Session directory not writable or not configured',
				'impact'  => 'Session data not saved, users logged out',
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'            => static::$slug . '-suboptimal',
				'title'         => 'PHP Configuration Not Optimal',
				'description'   => count( $issues ) . ' PHP setting(s) below recommended values. Contact hosting provider.'
				'kb_link'       => 'https://wpshadow.com/kb/php-ini-settings/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=php-ini',
				'training_link' => 'https://wpshadow.com/training/php-configuration/',
				'auto_fixable'  => false,
				'threat_level'  => 50,
				'module'        => 'System',
				'priority'      => 3,
				'meta'          => array(
					'issues_count' => count( $issues ),
					'issues'       => $issues,
				),
			);
		}

		return null;
	}

	/**
	 * Helper: Convert ini_get values (with K, M, G suffixes) to bytes
	 */
	private static function get_bytes_value( $value ): int {
		$value = trim( $value );
		if ( $value === '-1' || strtoupper( $value ) === 'UNLIMITED' ) {
			return PHP_INT_MAX;
		}

		$matches = array();
		if ( preg_match( '/^(\d+)\s*([KMG])B?$/i', $value, $matches ) ) {
			$size = (int) $matches[1];
			$unit = strtoupper( $matches[2] );

			return $size * array(
				'K' => 1024,
				'M' => 1024 ** 2,
				'G' => 1024 ** 3,
			)[ $unit ];
		}

		return (int) $value;
	}

	/**
	 * Guardian can request: "test-php-ini-memory"
	 * Checks: memory_limit >= 64MB
	 */
	public static function test_php_ini_memory(): array {
		$current = ini_get( 'memory_limit' );
		$bytes   = self::get_bytes_value( $current );
		$passed  = $bytes >= 67108864; // 64MB

		return array(
			'passed'  => $passed,
			'message' => $passed
				? "✓ Memory limit is adequate ({$current})"
				: "✗ Memory limit too low ({$current}, minimum 64M recommended)",
			'data'    => array(
				'setting'       => 'memory_limit',
				'current'       => $current,
				'current_bytes' => $bytes,
				'minimum'       => '64M',
				'recommended'   => '256M',
			),
		);
	}

	/**
	 * Guardian can request: "test-php-ini-upload"
	 * Checks: upload_max_filesize >= 2MB and post_max_size >= upload_max_filesize
	 */
	public static function test_php_ini_upload(): array {
		$upload_max = ini_get( 'upload_max_filesize' );
		$post_max   = ini_get( 'post_max_size' );

		$upload_bytes = self::get_bytes_value( $upload_max );
		$post_bytes   = self::get_bytes_value( $post_max );

		$passed = $upload_bytes >= 2097152 && $post_bytes >= $upload_bytes;

		return array(
			'passed'  => $passed,
			'message' => $passed
				? '✓ Upload settings are configured correctly'
				: "✗ Upload settings may cause issues (upload_max={$upload_max}, post_max={$post_max})",
			'data'    => array(
				'upload_max_filesize' => $upload_max,
				'post_max_size'       => $post_max,
				'upload_max_bytes'    => $upload_bytes,
				'post_max_bytes'      => $post_bytes,
				'mismatch'            => $post_bytes < $upload_bytes,
			),
		);
	}

	/**
	 * Guardian can request: "test-php-ini-execution-time"
	 * Checks: max_execution_time >= 30s
	 */
	public static function test_php_ini_execution_time(): array {
		$current = (int) ini_get( 'max_execution_time' );
		$passed  = $current === 0 || $current >= 30; // 0 = unlimited

		return array(
			'passed'  => $passed,
			'message' => $passed
				? "✓ Execution time limit is adequate ({$current}s)"
				: "✗ Execution time too low ({$current}s, minimum 30s recommended)",
			'data'    => array(
				'setting'     => 'max_execution_time',
				'current'     => $current . 's',
				'recommended' => '60s',
				'unlimited'   => $current === 0,
			),
		);
	}

	/**
	 * Guardian can request: "test-php-ini-session-handler"
	 * Checks: Session handler and path configuration
	 */
	public static function test_php_ini_session_handler(): array {
		$handler = ini_get( 'session.save_handler' );
		$path    = ini_get( 'session.save_path' );

		$handler_ok = $handler !== false;
		$path_ok    = ! ( empty( $path ) || ! is_writable( $path ) );
		$passed     = $handler_ok && ( $handler !== 'files' || $path_ok );

		return array(
			'passed'  => $passed,
			'message' => $passed
				? "✓ Session handler is properly configured ({$handler})"
				: "✗ Session handler issue: {$handler} save path may not be writable",
			'data'    => array(
				'session_handler'   => $handler,
				'session_save_path' => $path ?: 'default',
				'path_writable'     => $path_ok,
			),
		);
	}

	/**
	 * Guardian can request: "test-php-ini-all"
	 * Returns all critical PHP settings
	 */
	public static function test_php_ini_all(): array {
		return array(
			'passed'  => true,
			'message' => 'Complete PHP configuration snapshot',
			'data'    => array(
				'memory_limit'           => ini_get( 'memory_limit' ),
				'upload_max_filesize'    => ini_get( 'upload_max_filesize' ),
				'post_max_size'          => ini_get( 'post_max_size' ),
				'max_execution_time'     => ini_get( 'max_execution_time' ),
				'max_input_time'         => ini_get( 'max_input_time' ),
				'default_socket_timeout' => ini_get( 'default_socket_timeout' ),
				'session_handler'        => ini_get( 'session.save_handler' ),
				'session_save_path'      => ini_get( 'session.save_path' ),
				'display_errors'         => ini_get( 'display_errors' ),
				'error_reporting'        => ini_get( 'error_reporting' ),
			),
		);
	}
}
