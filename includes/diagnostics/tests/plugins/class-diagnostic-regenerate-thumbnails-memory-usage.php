<?php
/**
 * Regenerate Thumbnails Memory Usage Diagnostic
 *
 * Regenerate Thumbnails Memory Usage detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.769.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Regenerate Thumbnails Memory Usage Diagnostic Class
 *
 * @since 1.769.0000
 */
class Diagnostic_RegenerateThumbnailsMemoryUsage extends Diagnostic_Base {

	protected static $slug = 'regenerate-thumbnails-memory-usage';
	protected static $title = 'Regenerate Thumbnails Memory Usage';
	protected static $description = 'Regenerate Thumbnails Memory Usage detected';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! function_exists( 'regenerate_thumbnails' ) && ! class_exists( 'RegenerateThumbnails' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Verify memory limit is adequate
		$memory_limit = ini_get( 'memory_limit' );
		$memory_bytes = wp_convert_hr_to_bytes( $memory_limit );
		if ( $memory_bytes < ( 256 * 1024 * 1024 ) ) {
			$issues[] = __( 'Memory limit too low for thumbnail regeneration', 'wpshadow' );
		}

		// Check 2: Check batch processing configuration
		$batch_size = get_option( 'regenerate_thumbnails_batch_size', 10 );
		if ( $batch_size > 20 ) {
			$issues[] = __( 'Batch size too large may cause memory issues', 'wpshadow' );
		}

		// Check 3: Verify timeout settings for large images
		$max_execution = ini_get( 'max_execution_time' );
		if ( $max_execution > 0 && $max_execution < 120 ) {
			$issues[] = __( 'Execution timeout too low for large images', 'wpshadow' );
		}

		// Check 4: Check image library availability
		if ( ! extension_loaded( 'gd' ) && ! extension_loaded( 'imagick' ) ) {
			$issues[] = __( 'No image processing library available', 'wpshadow' );
		}

		// Check 5: Verify concurrent operation limits
		$concurrent_limit = get_option( 'regenerate_thumbnails_concurrent_limit', 0 );
		if ( $concurrent_limit === 0 || $concurrent_limit > 3 ) {
			$issues[] = __( 'Concurrent operation limit not configured or too high', 'wpshadow' );
		}

		// Check 6: Check error recovery mechanism
		$error_recovery = get_option( 'regenerate_thumbnails_error_recovery', false );
		if ( ! $error_recovery ) {
			$issues[] = __( 'Error recovery mechanism not enabled', 'wpshadow' );
		}
		return null;
	}
}
