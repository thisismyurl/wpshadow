<?php
/**
 * Relevanssi Indexing Performance Diagnostic
 *
 * Relevanssi indexing slowing database.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.399.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Relevanssi Indexing Performance Diagnostic Class
 *
 * @since 1.399.0000
 */
class Diagnostic_RelevanssiIndexingPerformance extends Diagnostic_Base {

	protected static $slug = 'relevanssi-indexing-performance';
	protected static $title = 'Relevanssi Indexing Performance';
	protected static $description = 'Relevanssi indexing slowing database';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'RELEVANSSI_PREMIUM_VERSION' ) && ! function_exists( 'relevanssi_search' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Verify indexing throttle is enabled
		$throttle = get_option( 'relevanssi_throttle', 0 );
		if ( ! $throttle ) {
			$issues[] = 'Indexing throttle not enabled';
		}

		// Check 2: Check for aggressive indexing batches
		$batch_size = get_option( 'relevanssi_indexing_batch_size', 0 );
		if ( $batch_size > 500 ) {
			$issues[] = 'Indexing batch size too large (over 500)';
		}

		// Check 3: Verify PDF indexing configuration
		$pdf_indexing = get_option( 'relevanssi_index_pdf', 0 );
		$pdf_throttle = get_option( 'relevanssi_pdf_throttle', 0 );
		if ( $pdf_indexing && ! $pdf_throttle ) {
			$issues[] = 'PDF indexing enabled without throttle';
		}

		// Check 4: Check scheduled indexing
		$cron_enabled = get_option( 'relevanssi_cron_indexing', 0 );
		if ( ! $cron_enabled ) {
			$issues[] = 'Scheduled indexing not enabled';
		}

		// Check 5: Verify indexer log size
		$log_enabled = get_option( 'relevanssi_log_queries', 0 );
		if ( $log_enabled ) {
			$issues[] = 'Query logging enabled (can slow indexing)';
		}

		// Check 6: Check for large post type indexing scope
		$post_types = get_option( 'relevanssi_index_post_types', array() );
		if ( is_array( $post_types ) && count( $post_types ) > 6 ) {
			$issues[] = 'Too many post types indexed (performance impact)';
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
					'Found %d Relevanssi indexing performance issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/relevanssi-indexing-performance',
			);
		}

		return null;
	}
}
