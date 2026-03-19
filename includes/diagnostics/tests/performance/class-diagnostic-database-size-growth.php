<?php
/**
 * Database Size and Growth Monitoring
 *
 * Validates database size trends and growth rate monitoring.
 *
 * @since 1.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Database_Size_Growth Class
 *
 * Checks database size and growth rate patterns.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Database_Size_Growth extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-size-growth';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database Size and Growth Monitoring';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Monitors database size trends and unusual growth patterns';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'database';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$database = DB_NAME;

		// Get total database size
		$db_size_query = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT 
					SUM(DATA_LENGTH + INDEX_LENGTH) as total_size,
					SUM(DATA_LENGTH) as data_size,
					SUM(INDEX_LENGTH) as index_size
				FROM information_schema.TABLES 
				WHERE TABLE_SCHEMA = %s",
				$database
			),
			ARRAY_A
		);

		if ( ! $db_size_query ) {
			return null;
		}

		$total_size = intval( $db_size_query['total_size'] );
		$data_size = intval( $db_size_query['data_size'] );
		$index_size = intval( $db_size_query['index_size'] );

		$total_size_mb = round( $total_size / 1048576, 2 );

		// Get largest tables
		$largest_tables = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT 
					TABLE_NAME, 
					(DATA_LENGTH + INDEX_LENGTH) as size_bytes,
					TABLE_ROWS
				FROM information_schema.TABLES 
				WHERE TABLE_SCHEMA = %s 
				AND TABLE_NAME LIKE %s
				ORDER BY size_bytes DESC 
				LIMIT 5",
				$database,
				$wpdb->esc_like( $wpdb->prefix ) . '%'
			),
			ARRAY_A
		);

		// Pattern 1: Database size exceeds reasonable limit for content volume
		$post_count = wp_count_posts()->publish;
		$expected_size_mb = ( $post_count * 0.5 ) + 50; // Rough estimate: 500KB per post + 50MB base

		if ( $total_size_mb > $expected_size_mb * 3 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Database size disproportionately large for content volume', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/database-size-growth',
				'details'      => array(
					'issue' => 'oversized_database',
					'database_size_mb' => $total_size_mb,
					'expected_size_mb' => round( $expected_size_mb, 2 ),
					'size_ratio' => round( $total_size_mb / $expected_size_mb, 2 ),
					'post_count' => intval( $post_count ),
					'message' => sprintf(
						/* translators: 1: actual size, 2: expected size */
						__( 'Database is %1$sMB (expected ~%2$sMB for content volume)', 'wpshadow' ),
						$total_size_mb,
						round( $expected_size_mb, 2 )
					),
					'common_causes' => array(
						'Post revisions accumulating (no limit set)',
						'Transients not expiring (plugin residue)',
						'Options table bloat (autoloaded data)',
						'Log files stored in database',
						'Spam comments not cleaned',
						'Plugin data not cleaned on deactivation',
					),
					'hosting_impact' => array(
						'Slower database queries',
						'Longer backup times',
						'Higher hosting costs (database storage)',
						'Potential disk quota issues',
					),
					'investigation_steps' => array(
						'Check largest tables (identify bloat sources)',
						'Review post revisions count',
						'Audit transients (expired but not deleted)',
						'Check options table size (autoload data)',
						'Look for plugin log tables',
					),
					'cleanup_strategies' => array(
						'Limit post revisions (WP_POST_REVISIONS constant)',
						'Delete old transients (expired caches)',
						'Remove spam comments permanently',
						'Clean post meta and usermeta orphans',
						'Remove deactivated plugin data',
					),
					'size_benchmarks' => array(
						'< 100MB' => 'Small site (typical blog)',
						'100-500MB' => 'Medium site (active blog/business)',
						'500MB-1GB' => 'Large site (e-commerce/membership)',
						'> 1GB' => 'Very large (requires active management)',
					),
					'recommendation' => __( 'Investigate and clean up database bloat sources', 'wpshadow' ),
				),
			);
		}

		// Pattern 2: One table dominates database size (>50% of total)
		if ( ! empty( $largest_tables ) ) {
			$largest_table_size = intval( $largest_tables[0]['size_bytes'] );
			$largest_percentage = ( $largest_table_size / $total_size ) * 100;

			if ( $largest_percentage > 50 ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => __( 'Single table dominates database size', 'wpshadow' ),
					'severity'     => 'medium',
					'threat_level' => 55,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/database-size-growth',
					'details'      => array(
						'issue' => 'single_table_dominance',
						'table_name' => $largest_tables[0]['TABLE_NAME'],
						'table_size_mb' => round( $largest_table_size / 1048576, 2 ),
						'percentage_of_total' => round( $largest_percentage, 2 ),
						'table_rows' => intval( $largest_tables[0]['TABLE_ROWS'] ),
						'message' => sprintf(
							/* translators: 1: table name, 2: percentage */
							__( '%1$s represents %2$s%% of total database size', 'wpshadow' ),
							$largest_tables[0]['TABLE_NAME'],
							round( $largest_percentage, 2 )
						),
						'common_oversized_tables' => array(
							'wp_options' => 'Autoloaded options bloat',
							'wp_postmeta' => 'Excessive post metadata',
							'wp_usermeta' => 'User metadata accumulation',
							'wp_comments' => 'Spam comments or old comments',
							'custom_logs' => 'Plugin logging data',
						),
						'investigation_per_table' => array(
							'wp_options' => 'Check autoloaded data (should be <1MB)',
							'wp_postmeta' => 'Look for serialized arrays or large values',
							'wp_usermeta' => 'Check for session data or large meta',
							'wp_comments' => 'Delete spam and old comments',
							'custom' => 'Identify plugin creating table, review need',
						),
						'performance_impact' => __( 'Large tables slow down all queries against that table', 'wpshadow' ),
						'indexing_concern' => __( 'Large tables without proper indexes become very slow', 'wpshadow' ),
						'cleanup_priority' => 'Focus cleanup efforts on the largest table first',
						'recommendation' => __( 'Investigate and optimize the dominant table', 'wpshadow' ),
					),
				);
			}
		}

		// Pattern 3: Index size larger than data size (over-indexing)
		if ( $index_size > $data_size ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Database indexes larger than actual data', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/database-size-growth',
				'details'      => array(
					'issue' => 'over_indexing',
					'data_size_mb' => round( $data_size / 1048576, 2 ),
					'index_size_mb' => round( $index_size / 1048576, 2 ),
					'index_to_data_ratio' => round( $index_size / $data_size, 2 ),
					'message' => sprintf(
						/* translators: 1: index size, 2: data size */
						__( 'Indexes (%1$sMB) larger than data (%2$sMB)', 'wpshadow' ),
						round( $index_size / 1048576, 2 ),
						round( $data_size / 1048576, 2 )
					),
					'what_is_over_indexing' => __( 'Too many indexes or poorly designed indexes waste space', 'wpshadow' ),
					'effects_of_over_indexing' => array(
						'Wasted disk space (indexes duplicate data)',
						'Slower INSERT/UPDATE/DELETE (must update all indexes)',
						'Larger backups (indexes included)',
						'Longer optimization times',
					),
					'healthy_ratio' => __( 'Index size should be 20-50% of data size', 'wpshadow' ),
					'investigation_needed' => array(
						'Identify tables with excessive indexes',
						'Review custom indexes created by plugins',
						'Check for duplicate or redundant indexes',
						'Analyze query patterns (are all indexes used?)',
					),
					'optimization_approach' => array(
						'Remove unused indexes',
						'Consolidate redundant indexes',
						'Review plugin-created indexes',
						'Use EXPLAIN to verify index usage',
					),
					'caution' => 'Only remove indexes after confirming they are not used by queries',
					'recommendation' => __( 'Audit database indexes and remove unnecessary ones', 'wpshadow' ),
				),
			);
		}

		// Pattern 4: Rapid database growth detected
		$previous_size = get_option( 'wpshadow_previous_db_size', 0 );
		$previous_check = get_option( 'wpshadow_previous_db_check', 0 );

		if ( $previous_size > 0 && $previous_check > 0 ) {
			$days_elapsed = intval( ( time() - $previous_check ) / 86400 );
			$growth_bytes = $total_size - $previous_size;
			$growth_mb = round( $growth_bytes / 1048576, 2 );

			if ( $days_elapsed > 0 ) {
				$growth_per_day = $growth_bytes / $days_elapsed;
				$growth_per_month = $growth_per_day * 30;

				// Alert if growing >100MB per month
				if ( $growth_per_month > 104857600 ) {
					return array(
						'id'           => self::$slug,
						'title'        => self::$title,
						'description'  => __( 'Database growing abnormally fast', 'wpshadow' ),
						'severity'     => 'high',
						'threat_level' => 70,
						'auto_fixable' => false,
						'kb_link'      => 'https://wpshadow.com/kb/database-size-growth',
						'details'      => array(
							'issue' => 'rapid_database_growth',
							'growth_mb' => $growth_mb,
							'days_elapsed' => $days_elapsed,
							'growth_per_day_mb' => round( $growth_per_day / 1048576, 2 ),
							'projected_growth_per_month_mb' => round( $growth_per_month / 1048576, 2 ),
							'message' => sprintf(
								/* translators: %s: growth per month in MB */
								__( 'Database growing at %sMB per month (abnormally high)', 'wpshadow' ),
								round( $growth_per_month / 1048576, 2 )
							),
							'growth_benchmarks' => array(
								'< 10MB/month' => 'Normal growth',
								'10-50MB/month' => 'Active site, acceptable',
								'50-100MB/month' => 'High activity, monitor',
								'> 100MB/month' => 'Abnormal, investigate immediately',
							),
							'common_causes_rapid_growth' => array(
								'Logging enabled (error logs, access logs)',
								'Session data accumulating',
								'Transients not expiring',
								'Analytics data stored in database',
								'Form submissions accumulating',
								'E-commerce order data (abandoned carts)',
							),
							'hosting_risks' => array(
								'Hitting disk quota limits',
								'Performance degradation',
								'Backup failures (timeout)',
								'Higher hosting costs',
							),
							'immediate_actions' => array(
								'Identify which table(s) growing fastest',
								'Check for logging plugins (disable if excessive)',
								'Review transients and sessions',
								'Clean up old data (orders, logs, analytics)',
								'Consider archiving old data',
							),
							'projection' => sprintf(
								/* translators: 1: months, 2: projected size */
								__( 'At current rate: %1$d months until %2$sGB', 'wpshadow' ),
								intval( 12 ),
								round( ( $total_size + ( $growth_per_month * 12 ) ) / 1073741824, 2 )
							),
							'recommendation' => __( 'Investigate rapid growth causes immediately', 'wpshadow' ),
						),
					);
				}
			}
		}

		// Store current size for next check
		update_option( 'wpshadow_previous_db_size', $total_size, false );
		update_option( 'wpshadow_previous_db_check', time(), false );

		// Pattern 5: Large number of rows in single table (performance risk)
		if ( ! empty( $largest_tables ) ) {
			$max_rows = intval( $largest_tables[0]['TABLE_ROWS'] );

			if ( $max_rows > 1000000 ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => __( 'Table with excessive row count (million+ rows)', 'wpshadow' ),
					'severity'     => 'medium',
					'threat_level' => 50,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/database-size-growth',
					'details'      => array(
						'issue' => 'excessive_table_rows',
						'table_name' => $largest_tables[0]['TABLE_NAME'],
						'row_count' => $max_rows,
						'message' => sprintf(
							/* translators: 1: table name, 2: row count */
							__( '%1$s has %2$s rows (performance concern)', 'wpshadow' ),
							$largest_tables[0]['TABLE_NAME'],
							number_format( $max_rows )
						),
						'performance_degradation' => __( 'Tables with 1M+ rows have significantly slower queries', 'wpshadow' ),
						'row_count_benchmarks' => array(
							'< 100K rows' => 'Normal, fast queries',
							'100K-500K rows' => 'Medium, requires good indexes',
							'500K-1M rows' => 'Large, needs optimization',
							'> 1M rows' => 'Very large, performance risk',
						),
						'optimization_requirements' => array(
							'Proper indexing critical',
							'Query optimization essential',
							'Consider partitioning table',
							'Archive old data periodically',
						),
						'investigation_steps' => array(
							'Review if all rows are necessary',
							'Check for old/obsolete data',
							'Verify indexes cover common queries',
							'Test query performance',
						),
						'archiving_strategy' => array(
							'Move old data to archive table',
							'Delete obsolete records',
							'Implement data retention policy',
							'Use external storage for logs',
						),
						'recommendation' => __( 'Review table row count and implement data retention policy', 'wpshadow' ),
					),
				);
			}
		}

		// Pattern 6: Database larger than available disk space allows for growth
		$disk_total = disk_total_space( ABSPATH );
		$disk_free = disk_free_space( ABSPATH );

		if ( $disk_free && $disk_total ) {
			$disk_free_mb = round( $disk_free / 1048576, 2 );
			$disk_used_percent = ( ( $disk_total - $disk_free ) / $disk_total ) * 100;

			// Alert if database is >30% of total disk and disk >80% full
			if ( $disk_used_percent > 80 && ( $total_size / $disk_total ) > 0.3 ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => __( 'Database size risky given available disk space', 'wpshadow' ),
					'severity'     => 'high',
					'threat_level' => 75,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/database-size-growth',
					'details'      => array(
						'issue' => 'disk_space_risk',
						'database_size_mb' => $total_size_mb,
						'disk_free_mb' => $disk_free_mb,
						'disk_used_percent' => round( $disk_used_percent, 2 ),
						'message' => sprintf(
							/* translators: 1: disk used percent, 2: database size */
							__( 'Disk %1$s%% full with %2$sMB database (growth risk)', 'wpshadow' ),
							round( $disk_used_percent, 2 ),
							$total_size_mb
						),
						'immediate_risks' => array(
							'Database cannot grow (out of disk space)',
							'Backups may fail (no space to write)',
							'Site may crash (disk full)',
							'Cannot save new content',
						),
						'disk_space_crisis' => __( 'When disk reaches 100%, site will crash entirely', 'wpshadow' ),
						'urgent_actions' => array(
							'Clean up database immediately',
							'Delete old backups',
							'Remove unused media files',
							'Upgrade hosting disk space',
							'Archive old data offsite',
						),
						'growth_projection' => __( 'Calculate how long until disk is full at current growth rate', 'wpshadow' ),
						'hosting_upgrade' => __( 'Consider upgrading hosting plan for more disk space', 'wpshadow' ),
						'recommendation' => __( 'Free up disk space immediately or upgrade hosting', 'wpshadow' ),
					),
				);
			}
		}

		return null; // No issues found
	}
}
