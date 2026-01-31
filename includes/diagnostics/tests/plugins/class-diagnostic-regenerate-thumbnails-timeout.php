<?php
/**
 * Regenerate Thumbnails Timeout Diagnostic
 *
 * Regenerate Thumbnails Timeout detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.770.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Regenerate Thumbnails Timeout Diagnostic Class
 *
 * @since 1.770.0000
 */
class Diagnostic_RegenerateThumbnailsTimeout extends Diagnostic_Base {

	protected static $slug = 'regenerate-thumbnails-timeout';
	protected static $title = 'Regenerate Thumbnails Timeout';
	protected static $description = 'Regenerate Thumbnails Timeout detected';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! function_exists( 'regenerate_thumbnails' ) && ! class_exists( 'RegenerateThumbnails' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Execution timeout.
		$max_execution = ini_get( 'max_execution_time' );
		if ( $max_execution > 0 && $max_execution < 300 ) {
			$issues[] = 'max execution time too low';
		}
		
		// Check 2: Memory limit.
		$memory_limit = ini_get( 'memory_limit' );
		$memory_bytes = wp_convert_hr_to_bytes( $memory_limit );
		if ( $memory_bytes < 268435456 ) {
			$issues[] = 'memory limit too low';
		}
		
		// Check 3: Batch size.
		$batch_size = get_option( 'regenerate_thumbs_batch_size', 50 );
		if ( $batch_size > 100 ) {
			$issues[] = 'batch size too large';
		}
		
		// Check 4: Background processing.
		$background = get_option( 'regenerate_thumbs_background', '1' );
		if ( '0' === $background ) {
			$issues[] = 'background processing disabled';
		}
		
		// Check 5: Error logging.
		$error_log = get_option( 'regenerate_thumbs_log_errors', '1' );
		if ( '0' === $error_log ) {
			$issues[] = 'error logging disabled';
		}
		
		// Check 6: Image library.
		$image_lib = get_option( 'regenerate_thumbs_library', 'gd' );
		if ( 'gd' === $image_lib && extension_loaded( 'imagick' ) ) {
			$issues[] = 'using GD instead of faster Imagick';
		}
		
		if ( ! empty( $issues ) ) {
			$threat_level = min( 65, 50 + ( count( $issues ) * 3 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Thumbnail regeneration issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/regenerate-thumbnails-timeout',
			);
		}
		
		return null;
	}
}
