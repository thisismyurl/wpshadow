<?php
/**
 * Database Query Performance and Indexing
 *
 * Validates database query performance and index optimization.
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
 * Diagnostic_Database_Query_Performance Class
 *
 * Checks database query performance and indexing issues.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Database_Query_Performance extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-query-performance';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database Query Performance';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates database query performance and index usage';

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

		// Check if slow query log is enabled
		$slow_query_log = $wpdb->get_var( "SHOW VARIABLES LIKE 'slow_query_log'" );
		$slow_query_enabled = false;

		if ( $slow_query_log ) {
			$slow_query_status = $wpdb->get_row( "SHOW VARIABLES LIKE 'slow_query_log'", ARRAY_A );
			$slow_query_enabled = isset( $slow_query_status['Value'] ) && 'ON' === $slow_query_status['Value'];
		}

		// Get database version
		$db_version = $wpdb->db_version();

		// Check for missing indexes on common columns
		$missing_indexes = self::check_missing_indexes();

		// Pattern 1: Slow query log not enabled
		if ( ! $slow_query_enabled ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Slow query logging not enabled', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/database-query-performance',
				'details'      => array(
					'issue' => 'slow_query_log_disabled',
					'message' => __( 'Database slow query logging is disabled', 'wpshadow' ),
					'what_is_slow_query_log' => __( 'MySQL feature that logs queries taking longer than threshold (default: 10s)', 'wpshadow' ),
					'why_enable_it' => array(
						'Identifies performance bottlenecks',
						'Reveals unoptimized queries',
						'Helps track down slow pages',
						'Essential for troubleshooting',
					),
					'what_it_captures' => array(
						'Query execution time',
						'Query statement (SQL)',
						'Timestamp',
						'Number of rows examined',
					),
					'performance_impact' => __( 'Minimal performance overhead (< 1%) when enabled', 'wpshadow' ),
					'how_to_enable' => array(
						'Add to my.cnf: slow_query_log = 1',
						'Set threshold: long_query_time = 2 (2 seconds)',
						'Restart MySQL service',
						'Or use phpMyAdmin/cPanel MySQL settings',
					),
					'log_location' => __( 'Typically: /var/log/mysql/slow-query.log', 'wpshadow' ),
					'analyzing_logs' => 'Use mysqldumpslow tool to analyze slow query patterns',
					'optimization_workflow' => array(
						'1. Enable slow query log',
						'2. Monitor for 1-7 days',
						'3. Analyze most frequent slow queries',
						'4. Add indexes or optimize queries',
						'5. Re-test after optimization',
					),
					'recommendation' => __( 'Enable slow query logging for performance monitoring', 'wpshadow' ),
				),
			);
		}

		// Pattern 2: Missing indexes on frequently queried columns
		if ( ! empty( $missing_indexes ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Database tables missing important indexes', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/database-query-performance',
				'details'      => array(
					'issue' => 'missing_indexes',
					'missing_indexes' => $missing_indexes,
					'message' => sprintf(
						/* translators: %d: number of missing indexes */
						__( '%d important indexes missing from database', 'wpshadow' ),
						count( $missing_indexes )
					),
					'what_are_indexes' => __( 'Database shortcuts that speed up lookups (like book index)', 'wpshadow' ),
					'performance_without_indexes' => array(
						'Full table scans (reads every row)',
						'Query times 10-1000x slower',
						'High CPU usage',
						'Slow page loads',
					),
					'performance_with_indexes' => array(
						'Direct row lookups (no scanning)',
						'Query times 10-1000x faster',
						'Low CPU usage',
						'Fast page loads',
					),
					'common_missing_indexes' => array(
						'wp_posts.post_author' => 'Author archive pages slow',
						'wp_posts.post_date' => 'Archive pages slow',
						'wp_postmeta.meta_key' => 'Custom field queries slow',
						'wp_comments.comment_post_ID' => 'Comment queries slow',
					),
					'how_to_add_indexes' => array(
						'Identify slow queries first',
						'Add index on WHERE/JOIN columns',
						'Test query speed after adding',
						'Monitor for improvement',
					),
					'index_creation_example' => 'CREATE INDEX idx_post_author ON wp_posts(post_author);',
					'caution' => array(
						'Too many indexes slow INSERT/UPDATE',
						'Only add indexes for frequently used queries',
						'Test on staging first',
					),
					'recommendation' => __( 'Add missing indexes to improve query performance', 'wpshadow' ),
				),
			);
		}

		// Pattern 3: Old MySQL version (performance and security)
		if ( version_compare( $db_version, '5.7', '<' ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Outdated MySQL/MariaDB version', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/database-query-performance',
				'details'      => array(
					'issue' => 'outdated_mysql_version',
					'current_version' => $db_version,
					'message' => sprintf(
						/* translators: %s: MySQL version */
						__( 'MySQL/MariaDB version %s is outdated', 'wpshadow' ),
						$db_version
					),
					'version_recommendations' => array(
						'MySQL 5.7+' => 'Good performance, security updates',
						'MySQL 8.0+' => 'Best performance, modern features',
						'MariaDB 10.3+' => 'Excellent alternative to MySQL',
					),
					'performance_improvements_newer' => array(
						'Better query optimizer',
						'JSON support (native)',
						'Improved indexing algorithms',
						'Better memory management',
						'Faster full-text search',
					),
					'security_concerns' => array(
						'Old versions lack security patches',
						'Known vulnerabilities',
						'No support for modern encryption',
					),
					'compatibility_notes' => __( 'WordPress requires MySQL 5.7+ or MariaDB 10.3+ for optimal performance', 'wpshadow' ),
					'upgrade_process' => array(
						'1. Backup entire database',
						'2. Test compatibility on staging',
						'3. Schedule maintenance window',
						'4. Upgrade database server',
						'5. Run mysql_upgrade script',
						'6. Test site functionality',
					),
					'hosting_provider' => __( 'Contact hosting provider to request MySQL/MariaDB upgrade', 'wpshadow' ),
					'recommendation' => __( 'Upgrade to MySQL 8.0 or MariaDB 10.5+ for better performance', 'wpshadow' ),
				),
			);
		}

		// Pattern 4: Query cache disabled (MySQL 5.7 and earlier)
		if ( version_compare( $db_version, '8.0', '<' ) ) {
			$query_cache = $wpdb->get_row( "SHOW VARIABLES LIKE 'query_cache_size'", ARRAY_A );

			if ( $query_cache && isset( $query_cache['Value'] ) && intval( $query_cache['Value'] ) === 0 ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => __( 'MySQL query cache disabled (MySQL 5.7 and earlier)', 'wpshadow' ),
					'severity'     => 'low',
					'threat_level' => 30,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/database-query-performance',
					'details'      => array(
						'issue' => 'query_cache_disabled',
						'message' => __( 'MySQL query cache is disabled or not configured', 'wpshadow' ),
						'what_is_query_cache' => __( 'MySQL feature that caches SELECT query results', 'wpshadow' ),
						'benefits_when_enabled' => array(
							'Identical queries return cached results',
							'Reduces database load',
							'Faster page loads for repeated queries',
							'Especially helpful for read-heavy sites',
						),
						'mysql_8_note' => __( 'Query cache removed in MySQL 8.0 (use application-level cache instead)', 'wpshadow' ),
						'when_query_cache_helps' => array(
							'High-traffic sites',
							'Read-heavy workloads',
							'Repeated queries (archives, listings)',
							'Sites without object cache',
						),
						'recommended_settings' => array(
							'query_cache_type = 1 (enabled)',
							'query_cache_size = 64M (moderate cache)',
							'query_cache_limit = 2M (max result size)',
						),
						'how_to_enable' => array(
							'Add to my.cnf: query_cache_type = 1',
							'Add to my.cnf: query_cache_size = 67108864',
							'Restart MySQL',
						),
						'better_alternative' => 'Use object cache (Redis, Memcached) instead of query cache',
						'recommendation' => __( 'Enable query cache or implement object caching', 'wpshadow' ),
					),
				);
			}
		}

		// Pattern 5: No object cache in use
		$object_cache_enabled = wp_using_ext_object_cache();

		if ( ! $object_cache_enabled ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'No external object cache configured', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/database-query-performance',
				'details'      => array(
					'issue' => 'no_object_cache',
					'message' => __( 'WordPress using default transient cache (database-based)', 'wpshadow' ),
					'what_is_object_cache' => __( 'In-memory cache that stores frequently accessed data', 'wpshadow' ),
					'benefits_of_object_cache' => array(
						'Stores data in RAM (not database)',
						'10-100x faster than database lookups',
						'Reduces database load significantly',
						'Scales to high traffic easily',
					),
					'object_cache_options' => array(
						'Redis' => 'Most popular, easy to set up, excellent performance',
						'Memcached' => 'Lightweight, very fast, widely supported',
						'APCu' => 'Built into PHP, simple but limited',
					),
					'when_object_cache_essential' => array(
						'High-traffic sites (>10K visits/day)',
						'E-commerce sites',
						'Membership sites',
						'Sites with complex queries',
					),
					'performance_gain' => __( 'Object cache typically reduces database queries by 50-90%', 'wpshadow' ),
					'implementation_steps' => array(
						'1. Install Redis or Memcached on server',
						'2. Install WordPress plugin (Redis Object Cache, Memcached)',
						'3. Configure connection in wp-config.php',
						'4. Enable object cache',
						'5. Monitor database load reduction',
					),
					'hosting_support' => __( 'Many managed WordPress hosts include Redis/Memcached', 'wpshadow' ),
					'recommendation' => __( 'Implement Redis or Memcached object cache for better performance', 'wpshadow' ),
				),
			);
		}

		// Pattern 6: No database connection pooling
		$persistent_connections = defined( 'DB_PERSISTENT_CONNECTIONS' ) && DB_PERSISTENT_CONNECTIONS;

		if ( ! $persistent_connections ) {
			$db_server_type = $wpdb->get_var( 'SELECT @@hostname' );

			// Only recommend if not on shared hosting
			if ( false !== strpos( $db_server_type, 'localhost' ) || false !== strpos( $db_server_type, '127.0.0.1' ) ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => __( 'Database persistent connections not enabled', 'wpshadow' ),
					'severity'     => 'low',
					'threat_level' => 20,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/database-query-performance',
					'details'      => array(
						'issue' => 'no_persistent_connections',
						'message' => __( 'Database connections are not persistent (recreated each request)', 'wpshadow' ),
						'what_are_persistent_connections' => __( 'Database connections reused across requests instead of reconnecting', 'wpshadow' ),
						'connection_overhead' => array(
							'Each page load: connect, authenticate, execute, disconnect',
							'Connection overhead: 5-50ms per request',
							'Persistent: connect once, reuse forever',
						),
						'benefits' => array(
							'Reduces connection overhead',
							'Faster page loads (5-50ms saved)',
							'Lower database server load',
							'Better scalability',
						),
						'when_to_use' => array(
							'High-traffic sites',
							'Sites with frequent database queries',
							'Dedicated servers (not shared hosting)',
						),
						'when_not_to_use' => array(
							'Shared hosting (limited connections)',
							'Low-traffic sites',
							'Sites with connection pool limits',
						),
						'how_to_enable' => 'Add to wp-config.php: define(\'DB_PERSISTENT_CONNECTIONS\', true);',
						'monitoring_needed' => __( 'Monitor database connection count after enabling', 'wpshadow' ),
						'caution' => __( 'On shared hosting, persistent connections may hit connection limits', 'wpshadow' ),
						'recommendation' => __( 'Consider enabling persistent connections on dedicated servers', 'wpshadow' ),
					),
				);
			}
		}

		return null; // No issues found
	}

	/**
	 * Check for missing indexes.
	 *
	 * @since 1.6093.1200
	 * @return array Missing indexes.
	 */
	private static function check_missing_indexes() {
		global $wpdb;

		$missing = array();

		// Check for common missing indexes
		$indexes_to_check = array(
			array(
				'table'  => $wpdb->posts,
				'column' => 'post_author',
				'name'   => 'idx_post_author',
			),
			array(
				'table'  => $wpdb->posts,
				'column' => 'post_date',
				'name'   => 'idx_post_date',
			),
			array(
				'table'  => $wpdb->postmeta,
				'column' => 'meta_key',
				'name'   => 'idx_meta_key',
			),
			array(
				'table'  => $wpdb->comments,
				'column' => 'comment_post_ID',
				'name'   => 'idx_comment_post_id',
			),
		);

		foreach ( $indexes_to_check as $index ) {
			$exists = $wpdb->get_row(
				$wpdb->prepare(
					"SHOW INDEX FROM %i WHERE Column_name = %s",
					$index['table'],
					$index['column']
				)
			);

			if ( ! $exists ) {
				$missing[] = array(
					'table'  => $index['table'],
					'column' => $index['column'],
					'suggested_name' => $index['name'],
				);
			}
		}

		return $missing;
	}
}
