<?php
/**
 * Database Connection Pool Diagnostic
 *
 * Verifies database can handle concurrent connections,
 * preventing connection limit errors under load.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Database_Connection_Pool Class
 *
 * Verifies database connection capacity.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Database_Connection_Pool extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-connection-pool';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database Connection Pool';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies database connection capacity';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'health';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if connection issues, null otherwise.
	 */
	public static function check() {
		$connection_status = self::check_database_connections();

		if ( ! $connection_status['has_issue'] ) {
			return null; // Connections healthy
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: max connections, %d: current connections */
				__( 'Database has %d max connections, but approaching capacity. When limit hit = "too many connections" error = site crash. Scale before it happens.', 'wpshadow' ),
				$connection_status['max_connections'],
				$connection_status['current_connections']
			),
			'severity'     => 'high',
			'threat_level' => 70,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/database-connections',
			'family'       => self::$family,
			'meta'         => array(
				'max_connections'     => $connection_status['max_connections'],
				'current_connections' => $connection_status['current_connections'],
				'usage_percent'       => $connection_status['usage_percent'],
			),
			'details'      => array(
				'what_are_db_connections'     => array(
					__( 'Connection = link from PHP to MySQL database' ),
					__( 'Each request = 1-5 connections' ),
					__( 'Limit = max simultaneous connections' ),
					__( 'Full = "Error: Too many connections"' ),
				),
				'connection_limits'           => array(
					'Shared Hosting' => array(
						'Typical: 20-50 max connections',
						'Risk: Low traffic can exceed',
						'Solution: Upgrade or optimize',
					),
					'VPS' => array(
						'Typical: 100-200 max connections',
						'Adequate for: 1000-5000 monthly visitors',
					),
					'Dedicated/Cloud' => array(
						'Typical: 500-1000 max connections',
						'Adequate for: 100,000+ monthly visitors',
					),
				),
				'when_connections_spike'      => array(
					'Traffic Surge' => array(
						'HackerNews, Reddit link drop',
						'Viral social media post',
						'Sudden traffic spike',
					),
					'Slow Queries' => array(
						'Query takes 10 seconds = connection held 10s',
						'Multiple slow queries = all connections consumed',
						'Optimization: Add indexes, cache',
					),
					'Plugin Issues' => array(
						'Plugin holds connections unnecessarily',
						'Example: Plugin left loop running',
					),
				),
				'optimizing_connection_usage' => array(
					'Enable Persistent Connections' => array(
						'MySQL: mysqli with persistent=on',
						'wp-config.php: define( \'DB_PERSISTENT\', true );',
						'Note: Many hosts disable this',
					),
					'Optimize Queries' => array(
						'Reduce: Slow queries < 0.1s each',
						'Add: Indexes on frequently queried columns',
						'Cache: Store results in object cache',
					),
					'Connection Pooling (Advanced)' => array(
						'ProxySQL: Reuses connections',
						'PgBouncer: PostgreSQL pooler',
						'Requires: Custom setup, not typical',
					),
				),
				'monitoring_connections'      => array(
					'MySQL Command' => array(
						'SHOW STATUS LIKE "Threads_connected";',
						'Shows: Current active connections',
						'Run regularly to see trends',
					),
					'WordPress' => array(
						'No built-in: Cannot see from WP',
						'Ask host: Check via cPanel/control panel',
					),
					'cPanel' => array(
						'Home → MySQL databases → Connections',
						'Shows: Current vs max',
					),
				),
				'when_to_upgrade'             => array(
					__( 'Current connections > 60% max = upgrade soon' ),
					__( 'Connection errors starting = upgrade now' ),
					__( 'Plan ahead: Growth trajectory' ),
					__( 'Proactive > reactive' ),
				),
			),
		);
	}

	/**
	 * Check database connections.
	 *
	 * @since  1.2601.2148
	 * @return array Connection status.
	 */
	private static function check_database_connections() {
		global $wpdb;

		$max_connections = 150; // Default MySQL
		$current_connections = 5; // Estimate

		try {
			// Try to get actual values from MySQL
			$result = $wpdb->get_results( "SHOW STATUS LIKE 'max_connections'" );
			if ( ! empty( $result ) ) {
				$max_connections = (int) $result[0]->Value;
			}

			$result = $wpdb->get_results( "SHOW STATUS LIKE 'Threads_connected'" );
			if ( ! empty( $result ) ) {
				$current_connections = (int) $result[0]->Value;
			}
		} catch ( \Exception $e ) {
			// Silently fail, use estimates
		}

		$usage_percent = round( ( $current_connections / $max_connections ) * 100 );
		$has_issue = $usage_percent > 60;

		return array(
			'max_connections'     => $max_connections,
			'current_connections' => $current_connections,
			'usage_percent'       => $usage_percent,
			'has_issue'           => $has_issue,
		);
	}
}
