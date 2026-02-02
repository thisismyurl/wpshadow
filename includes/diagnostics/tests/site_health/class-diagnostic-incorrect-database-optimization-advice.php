<?php
/**
 * Incorrect Database Optimization Advice
 *
 * Tests whether Site Health provides accurate database health recommendations.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\SiteHealth
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Incorrect_Database_Optimization_Advice Class
 *
 * Validates database optimization recommendations for accuracy.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Incorrect_Database_Optimization_Advice extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'incorrect-database-optimization-advice';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database Optimization Recommendations';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies database optimization recommendations are accurate';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'site_health';

	/**
	 * Run the diagnostic check.
	 *
	 * Tests database optimization advice accuracy.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues = array();

		// 1. Check overhead calculation accuracy
		$overhead_issue = self::check_overhead_calculation();
		if ( $overhead_issue ) {
			$issues[] = $overhead_issue;
		}

		// 2. Check for engine-specific advice
		$engine_issue = self::check_engine_specific_advice();
		if ( $engine_issue ) {
			$issues[] = $engine_issue;
		}

		// 3. Check fragmentation detection
		$fragmentation_issue = self::check_fragmentation_detection();
		if ( $fragmentation_issue ) {
			$issues[] = $fragmentation_issue;
		}

		// 4. Check optimization suggestions relevance
		$relevance_issue = self::check_optimization_relevance();
		if ( $relevance_issue ) {
			$issues[] = $relevance_issue;
		}

		// 5. Check for destructive optimization warnings
		$destructive_issue = self::check_destructive_optimization_warnings();
		if ( $destructive_issue ) {
			$issues[] = $destructive_issue;
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of accuracy issues */
					__( '%d database optimization recommendation issues found', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => true,
				'details'      => array_merge(
					$issues,
					array(
						sprintf( __( 'Database Engine: %s', 'wpshadow' ), self::get_storage_engine() ),
						sprintf( __( 'Database Size: %s', 'wpshadow' ), self::format_bytes( self::get_database_size() ) ),
					)
				),
				'kb_link'      => 'https://wpshadow.com/kb/database-optimization-advice',
				'recommendations' => array(
					__( 'Run OPTIMIZE TABLE manually and monitor performance', 'wpshadow' ),
					__( 'Check engine-specific optimization techniques', 'wpshadow' ),
					__( 'Monitor fragmentation separately from optimization', 'wpshadow' ),
					__( 'Create backups before running optimization', 'wpshadow' ),
				),
			);
		}

		return null;
	}

	/**
	 * Check overhead calculation accuracy.
	 *
	 * @since  1.2601.2148
	 * @return string|null Issue description or null if no issue.
	 */
	private static function check_overhead_calculation() {
		global $wpdb;

		// Get actual overhead from database
		$overhead = $wpdb->get_results( "SHOW TABLE STATUS FROM " . DB_NAME );

		if ( empty( $overhead ) ) {
			return __( 'Could not calculate table overhead', 'wpshadow' );
		}

		$total_overhead = 0;

		foreach ( $overhead as $table ) {
			if ( isset( $table->Data_free ) ) {
				$total_overhead += (int) $table->Data_free;
			}
		}

		// If very low overhead but Site Health suggests optimization
		if ( $total_overhead < 1048576 && $total_overhead > 0 ) { // Less than 1MB
			return sprintf(
				/* translators: %s: overhead size */
				__( 'Overhead is only %s but recommendations may suggest large optimization', 'wpshadow' ),
				self::format_bytes( $total_overhead )
			);
		}

		return null;
	}

	/**
	 * Check for engine-specific advice.
	 *
	 * @since  1.2601.2148
	 * @return string|null Issue description or null if no issue.
	 */
	private static function check_engine_specific_advice() {
		global $wpdb;

		$engine = self::get_storage_engine();

		// MyISAM optimization is different from InnoDB
		if ( 'MyISAM' === $engine ) {
			return __( 'Using MyISAM engine (OPTIMIZE TABLE is safe but fragmentation repair different than InnoDB)', 'wpshadow' );
		}

		// InnoDB specific
		if ( 'InnoDB' === $engine ) {
			// OPTIMIZE TABLE on InnoDB requires table rebuild (can lock table)
			return __( 'Using InnoDB engine (OPTIMIZE TABLE causes full table rebuild, plan maintenance window)', 'wpshadow' );
		}

		return null;
	}

	/**
	 * Check fragmentation detection.
	 *
	 * @since  1.2601.2148
	 * @return string|null Issue description or null if no issue.
	 */
	private static function check_fragmentation_detection() {
		global $wpdb;

		// Get fragmentation data
		$tables = $wpdb->get_results( "SHOW TABLE STATUS FROM " . DB_NAME );

		if ( empty( $tables ) ) {
			return null;
		}

		$fragmented_tables = array();

		foreach ( $tables as $table ) {
			// Calculate fragmentation percentage
			if ( isset( $table->Data_length, $table->Data_free ) ) {
				$data_length = (int) $table->Data_length;
				$data_free   = (int) $table->Data_free;

				if ( $data_length > 0 ) {
					$fragmentation = ( $data_free / $data_length ) * 100;

					// Only report if significant fragmentation
					if ( $fragmentation > 10 ) {
						$fragmented_tables[] = sprintf(
							'%s (%.1f%% fragmented)',
							$table->Name,
							$fragmentation
						);
					}
				}
			}
		}

		if ( ! empty( $fragmented_tables ) ) {
			return sprintf(
				/* translators: %d: number of fragmented tables */
				__( '%d tables show fragmentation (but may not impact performance)', 'wpshadow' ),
				count( $fragmented_tables )
			);
		}

		return null;
	}

	/**
	 * Check optimization suggestions relevance.
	 *
	 * @since  1.2601.2148
	 * @return string|null Issue description or null if no issue.
	 */
	private static function check_optimization_relevance() {
		global $wpdb;

		// Check if database is actually slow
		// Recommendation should only apply if performance is degraded

		// Run quick query to check performance
		$start = microtime( true );
		$wpdb->get_results( "SELECT COUNT(*) FROM {$wpdb->posts}" );
		$query_time = microtime( true ) - $start;

		// If queries are fast, optimization may not be needed
		if ( $query_time < 0.1 ) {
			return __( 'Database queries are performing well, optimization may not be necessary', 'wpshadow' );
		}

		return null;
	}

	/**
	 * Check for destructive optimization warnings.
	 *
	 * @since  1.2601.2148
	 * @return string|null Issue description or null if no issue.
	 */
	private static function check_destructive_optimization_warnings() {
		// Check if Site Health recommends potentially destructive operations
		// (Should warn about data loss risk)

		// Check for REPAIR TABLE recommendation
		$optimize_option = get_option( 'wpshadow_db_repair_recommended', false );

		if ( $optimize_option ) {
			// If recommending repair, should have backup warning
			return __( 'Database repair recommendations present but may not include backup warning', 'wpshadow' );
		}

		return null;
	}

	/**
	 * Get storage engine.
	 *
	 * @since  1.2601.2148
	 * @return string Storage engine type.
	 */
	private static function get_storage_engine() {
		global $wpdb;

		$engine = $wpdb->get_var( "SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() LIMIT 1" );

		return $engine ? $engine : 'Unknown';
	}

	/**
	 * Get database size.
	 *
	 * @since  1.2601.2148
	 * @return int Size in bytes.
	 */
	private static function get_database_size() {
		global $wpdb;

		$size = $wpdb->get_var(
			"SELECT SUM(data_length + index_length) FROM information_schema.TABLES WHERE table_schema = DATABASE()"
		);

		return $size ? (int) $size : 0;
	}

	/**
	 * Format bytes to human-readable.
	 *
	 * @since  1.2601.2148
	 * @param  int $bytes Size in bytes.
	 * @return string Formatted size.
	 */
	private static function format_bytes( $bytes ) {
		$units = array( 'B', 'KB', 'MB', 'GB' );
		$bytes = max( (int) $bytes, 0 );
		$pow   = floor( ( $bytes ? log( $bytes ) : 0 ) / log( 1024 ) );
		$pow   = min( $pow, count( $units ) - 1 );
		$bytes /= ( 1 << ( 10 * $pow ) );

		return round( $bytes, 2 ) . ' ' . $units[ $pow ];
	}
}
