<?php
/**
 * Database Table Optimization and Overhead
 *
 * Validates database table optimization status and overhead accumulation.
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
 * Diagnostic_Database_Table_Optimization Class
 *
 * Checks database table optimization and overhead issues.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Database_Table_Optimization extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-table-optimization';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database Table Optimization';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates database table optimization status and overhead';

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

		// Get database name
		$database = DB_NAME;

		// Query table status
		$tables = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT TABLE_NAME, ENGINE, DATA_LENGTH, INDEX_LENGTH, DATA_FREE 
				FROM information_schema.TABLES 
				WHERE TABLE_SCHEMA = %s 
				AND TABLE_NAME LIKE %s",
				$database,
				$wpdb->esc_like( $wpdb->prefix ) . '%'
			),
			ARRAY_A
		);

		if ( empty( $tables ) ) {
			return null;
		}

		$total_overhead = 0;
		$total_size = 0;
		$fragmented_tables = array();

		foreach ( $tables as $table ) {
			$overhead = isset( $table['DATA_FREE'] ) ? intval( $table['DATA_FREE'] ) : 0;
			$size = intval( $table['DATA_LENGTH'] ) + intval( $table['INDEX_LENGTH'] );

			$total_overhead += $overhead;
			$total_size += $size;

			// Table with >5% overhead is fragmented
			if ( $size > 0 && ( $overhead / $size ) > 0.05 ) {
				$fragmented_tables[] = array(
					'name' => $table['TABLE_NAME'],
					'overhead' => $overhead,
					'percentage' => round( ( $overhead / $size ) * 100, 2 ),
				);
			}
		}

		// Pattern 1: Significant database overhead (>10MB)
		if ( $total_overhead > 10485760 ) { // 10MB in bytes
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Database has significant overhead requiring optimization', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/database-table-optimization',
				'details'      => array(
					'issue' => 'high_database_overhead',
					'total_overhead_bytes' => $total_overhead,
					'total_overhead_mb' => round( $total_overhead / 1048576, 2 ),
					'message' => sprintf(
						/* translators: %s: overhead size in MB */
						__( 'Database has %sMB of wasted overhead space', 'wpshadow' ),
						round( $total_overhead / 1048576, 2 )
					),
					'what_is_overhead' => __( 'Overhead = deleted/updated rows not yet reclaimed by database engine', 'wpshadow' ),
					'performance_impact' => array(
						'Queries scan deleted rows (slower performance)',
						'Database takes more disk space than needed',
						'Backups are larger and slower',
						'Indexes become inefficient (fragmented)',
					),
					'causes_of_overhead' => array(
						'DELETE operations (rows marked deleted, not removed)',
						'UPDATE operations (old row versions kept)',
						'Transients expiring (not cleaned up)',
						'Plugin deactivation (leftover data)',
					),
					'benchmarks' => array(
						'< 5MB' => 'Normal, no action needed',
						'5-10MB' => 'Consider optimization',
						'10-50MB' => 'Optimization recommended',
						'> 50MB' => 'Urgent optimization required',
					),
					'how_to_optimize' => array(
						'Run OPTIMIZE TABLE command on each table',
						'Use plugin: WP-Optimize, Advanced Database Cleaner',
						'Schedule monthly optimization',
						'Backup before optimizing',
					),
					'performance_gain' => __( 'Optimization can improve query speed by 10-30%', 'wpshadow' ),
					'recommendation' => __( 'Run database optimization to reclaim overhead space', 'wpshadow' ),
				),
			);
		}

		// Pattern 2: Multiple fragmented tables (>5 tables with >5% overhead)
		if ( count( $fragmented_tables ) > 5 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Multiple database tables are fragmented', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/database-table-optimization',
				'details'      => array(
					'issue' => 'multiple_fragmented_tables',
					'fragmented_count' => count( $fragmented_tables ),
					'fragmented_tables' => array_slice( $fragmented_tables, 0, 10 ),
					'message' => sprintf(
						/* translators: %d: number of fragmented tables */
						__( '%d tables have significant fragmentation (>5%% overhead)', 'wpshadow' ),
						count( $fragmented_tables )
					),
					'fragmentation_impact' => array(
						'Slower SELECT queries (scans wasted space)',
						'Inefficient index lookups',
						'Wasted disk space',
						'Longer backup times',
					),
					'high_activity_tables' => array(
						'wp_options' => 'Frequently updated (auto_loaded options)',
						'wp_postmeta' => 'Post metadata changes constantly',
						'wp_comments' => 'Comment spam deletions',
						'wp_usermeta' => 'User metadata updates',
					),
					'optimization_strategy' => array(
						'Identify high-overhead tables',
						'Run OPTIMIZE TABLE on each',
						'Schedule regular optimization (monthly)',
						'Monitor overhead growth',
					),
					'automation' => __( 'Use cron job or plugin to auto-optimize weekly', 'wpshadow' ),
					'recommendation' => __( 'Optimize fragmented tables to improve performance', 'wpshadow' ),
				),
			);
		}

		// Pattern 3: MyISAM tables detected (should be InnoDB)
		$myisam_tables = array();
		foreach ( $tables as $table ) {
			if ( 'MyISAM' === $table['ENGINE'] ) {
				$myisam_tables[] = $table['TABLE_NAME'];
			}
		}

		if ( ! empty( $myisam_tables ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Database tables using outdated MyISAM engine', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/database-table-optimization',
				'details'      => array(
					'issue' => 'myisam_tables_detected',
					'myisam_count' => count( $myisam_tables ),
					'myisam_tables' => array_slice( $myisam_tables, 0, 10 ),
					'message' => sprintf(
						/* translators: %d: number of MyISAM tables */
						__( '%d tables using MyISAM instead of InnoDB', 'wpshadow' ),
						count( $myisam_tables )
					),
					'why_myisam_is_outdated' => array(
						'No transaction support (data corruption risk)',
						'No foreign key constraints',
						'Table-level locking only (concurrency issues)',
						'No crash recovery',
						'Not ACID compliant',
					),
					'innodb_advantages' => array(
						'Row-level locking (better concurrency)',
						'Transaction support (data integrity)',
						'Crash recovery (auto-repair)',
						'Foreign key constraints',
						'ACID compliant (reliable)',
					),
					'wordpress_recommendation' => __( 'WordPress officially recommends InnoDB for all tables', 'wpshadow' ),
					'conversion_risks' => array(
						'Backup database before converting',
						'Test on staging environment first',
						'Expect brief downtime during conversion',
						'Verify data integrity after conversion',
					),
					'conversion_command' => 'ALTER TABLE table_name ENGINE=InnoDB;',
					'automated_tools' => 'Use plugin: WP-Optimize, Database Converter',
					'recommendation' => __( 'Convert MyISAM tables to InnoDB for better reliability', 'wpshadow' ),
				),
			);
		}

		// Pattern 4: Tables never optimized (overhead ratio >10%)
		$never_optimized = array();
		foreach ( $fragmented_tables as $table ) {
			if ( $table['percentage'] > 10 ) {
				$never_optimized[] = $table;
			}
		}

		if ( ! empty( $never_optimized ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Database tables have never been optimized', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/database-table-optimization',
				'details'      => array(
					'issue' => 'tables_never_optimized',
					'severely_fragmented_count' => count( $never_optimized ),
					'severely_fragmented_tables' => $never_optimized,
					'message' => sprintf(
						/* translators: %d: number of tables */
						__( '%d tables have >10%% overhead (likely never optimized)', 'wpshadow' ),
						count( $never_optimized )
					),
					'performance_degradation' => __( 'Tables with >10% overhead run 20-40% slower', 'wpshadow' ),
					'why_optimization_matters' => array(
						'Reclaims wasted disk space',
						'Rebuilds indexes for faster lookups',
						'Removes fragmentation',
						'Improves query performance',
						'Reduces backup size and time',
					),
					'signs_of_unoptimized_database' => array(
						'Slow admin dashboard loading',
						'Timeout errors on large queries',
						'Large database size vs. content volume',
						'Slow plugin activation/deactivation',
					),
					'optimization_urgency' => 'URGENT - Performance significantly impacted',
					'optimization_process' => array(
						'1. Create full database backup',
						'2. Put site in maintenance mode (optional)',
						'3. Run OPTIMIZE TABLE on each table',
						'4. Test site functionality',
						'5. Schedule future optimizations',
					),
					'expected_results' => __( 'Typical optimization reclaims 5-20% disk space and improves speed', 'wpshadow' ),
					'recommendation' => __( 'Optimize severely fragmented tables immediately', 'wpshadow' ),
				),
			);
		}

		// Pattern 5: Large database with no optimization schedule
		$total_size_mb = round( $total_size / 1048576, 2 );

		if ( $total_size_mb > 500 ) {
			$last_optimize = get_option( 'wpshadow_last_database_optimize', 0 );
			$days_since_optimize = $last_optimize > 0 ? intval( ( time() - $last_optimize ) / 86400 ) : 999;

			if ( $days_since_optimize > 90 ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => __( 'Large database with no regular optimization schedule', 'wpshadow' ),
					'severity'     => 'low',
					'threat_level' => 35,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/database-table-optimization',
					'details'      => array(
						'issue' => 'large_db_no_optimization_schedule',
						'database_size_mb' => $total_size_mb,
						'days_since_optimize' => $days_since_optimize,
						'message' => sprintf(
							/* translators: 1: database size in MB, 2: days since optimization */
							__( '%1$sMB database, not optimized in %2$d+ days', 'wpshadow' ),
							$total_size_mb,
							$days_since_optimize
						),
						'large_database_challenges' => array(
							'Overhead accumulates faster',
							'More tables = more fragmentation',
							'Optimization takes longer',
							'Performance degradation more noticeable',
						),
						'recommended_schedule' => array(
							'< 100MB' => 'Quarterly (every 3 months)',
							'100-500MB' => 'Monthly',
							'500MB - 1GB' => 'Bi-weekly (every 2 weeks)',
							'> 1GB' => 'Weekly',
						),
						'automation_tools' => array(
							'WP-Optimize' => 'Schedule automatic weekly optimization',
							'WP-Cron' => 'Create custom cron job',
							'Advanced Database Cleaner' => 'Schedule optimization + cleanup',
						),
						'optimization_timing' => __( 'Schedule during low-traffic hours (e.g., 3am server time)', 'wpshadow' ),
						'monitoring' => __( 'Track overhead growth weekly to adjust schedule', 'wpshadow' ),
						'recommendation' => __( 'Implement regular database optimization schedule', 'wpshadow' ),
					),
				);
			}
		}

		// Pattern 6: Database charset mismatch
		$charset_issues = array();
		foreach ( $tables as $table ) {
			$charset_info = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT TABLE_COLLATION FROM information_schema.TABLES 
					WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s",
					$database,
					$table['TABLE_NAME']
				)
			);

			if ( $charset_info && false === strpos( $charset_info->TABLE_COLLATION, 'utf8mb4' ) ) {
				$charset_issues[] = array(
					'table' => $table['TABLE_NAME'],
					'collation' => $charset_info->TABLE_COLLATION,
				);
			}
		}

		if ( ! empty( $charset_issues ) && count( $charset_issues ) > 3 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Database tables not using utf8mb4 charset', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/database-table-optimization',
				'details'      => array(
					'issue' => 'charset_not_utf8mb4',
					'affected_tables_count' => count( $charset_issues ),
					'affected_tables' => array_slice( $charset_issues, 0, 10 ),
					'message' => sprintf(
						/* translators: %d: number of tables */
						__( '%d tables not using utf8mb4 charset (emoji/multilingual issues)', 'wpshadow' ),
						count( $charset_issues )
					),
					'why_utf8mb4_matters' => array(
						'Supports emoji (😊) in content',
						'Supports full Unicode (all languages)',
						'Supports rare characters and symbols',
						'WordPress 4.2+ default standard',
					),
					'problems_without_utf8mb4' => array(
						'Emoji display as ??? or boxes',
						'Some non-English characters broken',
						'Content corruption on save',
						'Data loss when copying from Word',
					),
					'conversion_requirement' => __( 'Convert tables to utf8mb4_unicode_ci for full compatibility', 'wpshadow' ),
					'conversion_command' => 'ALTER TABLE table_name CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;',
					'wordpress_conversion_tool' => __( 'WP-CLI: wp db convert-to-utf8mb4', 'wpshadow' ),
					'backup_critical' => __( 'CRITICAL: Backup database before charset conversion', 'wpshadow' ),
					'recommendation' => __( 'Convert tables to utf8mb4 for emoji and international character support', 'wpshadow' ),
				),
			);
		}

		return null; // No issues found
	}
}
