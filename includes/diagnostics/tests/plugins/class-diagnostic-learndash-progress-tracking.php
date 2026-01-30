<?php
/**
 * LearnDash Progress Tracking Diagnostic
 *
 * LearnDash progress queries slow.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.362.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * LearnDash Progress Tracking Diagnostic Class
 *
 * @since 1.362.0000
 */
class Diagnostic_LearndashProgressTracking extends Diagnostic_Base {

	protected static $slug = 'learndash-progress-tracking';
	protected static $title = 'LearnDash Progress Tracking';
	protected static $description = 'LearnDash progress queries slow';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'LEARNDASH_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Progress tracking enabled
		$tracking = get_option( 'learndash_progress_tracking_enabled', 0 );
		if ( ! $tracking ) {
			$issues[] = 'Progress tracking not enabled';
		}
		
		// Check 2: Query optimization
		$query = get_option( 'learndash_progress_query_optimization_enabled', 0 );
		if ( ! $query ) {
			$issues[] = 'Progress query optimization not enabled';
		}
		
		// Check 3: Caching
		$cache = get_option( 'learndash_progress_caching_enabled', 0 );
		if ( ! $cache ) {
			$issues[] = 'Progress caching not enabled';
		}
		
		// Check 4: Indexing
		$indexing = get_option( 'learndash_progress_table_indexing_enabled', 0 );
		if ( ! $indexing ) {
			$issues[] = 'Progress table indexing not enabled';
		}
		
		// Check 5: Activity logging
		$logging = get_option( 'learndash_progress_logging_optimized', 0 );
		if ( ! $logging ) {
			$issues[] = 'Activity logging not optimized';
		}
		
		// Check 6: Batch processing
		$batch = get_option( 'learndash_progress_batch_processing_enabled', 0 );
		if ( ! $batch ) {
			$issues[] = 'Batch processing not enabled';
		}
		
		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 35;
			$threat_multiplier = 6;
			$max_threat = 65;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );
			
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d progress tracking issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/learndash-progress-tracking',
			);
		}
		
		return null;
	}
}
