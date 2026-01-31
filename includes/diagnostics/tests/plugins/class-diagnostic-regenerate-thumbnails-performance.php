<?php
/**
 * Regenerate Thumbnails Performance Diagnostic
 *
 * Regenerate Thumbnails Performance detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.768.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Regenerate Thumbnails Performance Diagnostic Class
 *
 * @since 1.768.0000
 */
class Diagnostic_RegenerateThumbnailsPerformance extends Diagnostic_Base {

	protected static $slug = 'regenerate-thumbnails-performance';
	protected static $title = 'Regenerate Thumbnails Performance';
	protected static $description = 'Regenerate Thumbnails Performance detected';
	protected static $family = 'performance';

	public static function check() {
		$issues = array();
		
		// Check 1: Execution timeout sufficient
		$max_execution = ini_get( 'max_execution_time' );
		if ( $max_execution && $max_execution < 300 && 0 !== (int) $max_execution ) {
			$issues[] = 'Execution timeout too low for regeneration';
		}
		
		// Check 2: Memory limit sufficient
		$memory_limit = ini_get( 'memory_limit' );
		if ( $memory_limit ) {
			$memory_bytes = wp_convert_hr_to_bytes( $memory_limit );
			if ( $memory_bytes < 268435456 ) { // 256MB
				$issues[] = 'Memory limit too low for regeneration';
			}
		}
		
		// Check 3: Batch size configured
		$batch_size = get_option( 'regenerate_thumbnails_batch_size', 0 );
		if ( $batch_size <= 0 || $batch_size > 50 ) {
			$issues[] = 'Batch size not optimally configured';
		}
		
		// Check 4: Concurrent regeneration disabled
		$concurrent = get_option( 'regenerate_thumbnails_concurrent', false );
		if ( $concurrent ) {
			$issues[] = 'Concurrent regeneration enabled (risky)';
		}
		
		// Check 5: Image library available (GD or Imagick)
		if ( ! extension_loaded( 'gd' ) && ! extension_loaded( 'imagick' ) ) {
			$issues[] = 'No image processing library available';
		}
		
		// Check 6: Error logging enabled
		$error_logging = get_option( 'regenerate_thumbnails_log_errors', false );
		if ( ! $error_logging ) {
			$issues[] = 'Error logging disabled';
		}
		
		if ( ! empty( $issues ) ) {
			$threat_level = min( 70, 40 + ( count( $issues ) * 5 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Regenerate Thumbnails performance issues: ' . implode( ', ', $issues ),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/regenerate-thumbnails-performance',
			);
		}
		
		return null;
	}
}
