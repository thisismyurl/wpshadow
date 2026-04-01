<?php
/**
 * Database Connection Pooling Diagnostic
 *
 * Analyzes database connection management and pooling opportunities.
 *
 * @since 0.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database Connection Pooling Diagnostic
 *
 * Evaluates database connection handling and persistent connections.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Database_Connection_Pooling extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-connection-pooling';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database Connection Pooling';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Analyzes database connection management and pooling opportunities';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Check if using persistent connections
		$db_user = defined( 'DB_USER' ) ? DB_USER : '';
		$db_host = defined( 'DB_HOST' ) ? DB_HOST : '';

		// Check for persistent connection (p: prefix in DB_HOST)
		$using_persistent = strpos( $db_host, 'p:' ) === 0;

		// Check database server version
		$db_version = $wpdb->get_var( 'SELECT VERSION()' );

		// Estimate site traffic level (rough heuristic)
		$post_count = wp_count_posts()->publish ?? 0;
		$user_count = count_users();
		$total_users = $user_count['total_users'] ?? 0;

		// High traffic indicators
		$is_high_traffic = $post_count > 1000 || $total_users > 100;

		// Get max_connections setting
		$max_connections = $wpdb->get_var( "SHOW VARIABLES LIKE 'max_connections'" );
		$max_conn_value  = $wpdb->get_var( "SHOW VARIABLES WHERE Variable_name = 'max_connections'" );

		// Generate findings if not using persistent connections on high-traffic sites
		if ( ! $using_persistent && $is_high_traffic ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Not using persistent database connections on high-traffic site. Persistent connections reduce overhead.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/database-connection-pooling?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'meta'         => array(
					'using_persistent'  => $using_persistent,
					'is_high_traffic'   => $is_high_traffic,
					'post_count'        => $post_count,
					'user_count'        => $total_users,
					'db_version'        => $db_version,
					'max_connections'   => $max_conn_value,
					'recommendation'    => 'Add "p:" prefix to DB_HOST in wp-config.php',
					'impact_estimate'   => '10-30ms faster database connection per request',
					'example'           => "define('DB_HOST', 'p:localhost');",
					'caveat'            => 'Ensure MySQL server supports persistent connections and has adequate max_connections',
				),
			);
		}

		// Check if max_connections is too low for high traffic
		if ( $is_high_traffic && $max_conn_value && absint( $max_conn_value ) < 100 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: current max connections */
					__( 'Database max_connections set to %d. High-traffic sites should have 150-300 connections available.', 'wpshadow' ),
					absint( $max_conn_value )
				),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/database-connection-pooling?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'meta'         => array(
					'current_max_connections' => absint( $max_conn_value ),
					'recommended'             => 150,
					'is_high_traffic'         => $is_high_traffic,
					'recommendation'          => 'Increase max_connections in MySQL configuration',
					'impact'                  => 'Prevents "too many connections" errors under load',
					'mysql_config'            => 'Add max_connections=150 to my.cnf',
				),
			);
		}

		return null;
	}
}
