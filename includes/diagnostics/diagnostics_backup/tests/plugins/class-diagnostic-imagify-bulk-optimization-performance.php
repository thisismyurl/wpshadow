<?php
/**
 * Imagify Bulk Optimization Performance Diagnostic
 *
 * Imagify Bulk Optimization Performance detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.739.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Imagify Bulk Optimization Performance Diagnostic Class
 *
 * @since 1.739.0000
 */
class Diagnostic_ImagifyBulkOptimizationPerformance extends Diagnostic_Base {

	protected static $slug = 'imagify-bulk-optimization-performance';
	protected static $title = 'Imagify Bulk Optimization Performance';
	protected static $description = 'Imagify Bulk Optimization Performance detected';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'IMAGIFY_VERSION' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Bulk optimization enabled
		$bulk_enabled = get_option( 'imagify_bulk_optimization_enabled', 0 );
		if ( ! $bulk_enabled ) {
			$issues[] = 'Bulk optimization not enabled';
		}

		// Check 2: Batch size configured
		$batch_size = absint( get_option( 'imagify_bulk_batch_size', 0 ) );
		if ( $batch_size <= 0 ) {
			$issues[] = 'Bulk optimization batch size not configured';
		}

		// Check 3: Memory limit set
		$memory_limit = absint( get_option( 'imagify_bulk_memory_limit_mb', 0 ) );
		if ( $memory_limit < 256 ) {
			$issues[] = 'Memory limit too low for bulk operations';
		}

		// Check 4: Timeout configured
		$timeout = absint( get_option( 'imagify_bulk_timeout_seconds', 0 ) );
		if ( $timeout <= 0 ) {
			$issues[] = 'Bulk optimization timeout not configured';
		}

		// Check 5: Progress tracking
		$progress_tracking = get_option( 'imagify_bulk_progress_tracking', 0 );
		if ( ! $progress_tracking ) {
			$issues[] = 'Progress tracking not enabled';
		}

		// Check 6: Error handling
		$error_handling = get_option( 'imagify_bulk_error_handling', '' );
		if ( empty( $error_handling ) ) {
			$issues[] = 'Error handling not configured';
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 45;
			$threat_multiplier = 6;
			$max_threat = 75;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d bulk optimization issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/imagify-bulk-optimization-performance',
			);
		}

		return null;
	}
}
