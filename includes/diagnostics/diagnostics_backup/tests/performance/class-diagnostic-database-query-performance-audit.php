<?php
/**
 * Database Query Performance Audit Diagnostic
 *
 * Checks if database queries are being monitored and optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2308
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database Query Performance Audit Diagnostic Class
 *
 * Detects database performance issues.
 *
 * @since 1.2601.2308
 */
class Diagnostic_Database_Query_Performance_Audit extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-query-performance-audit';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database Query Performance Audit';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Audits database query performance and identifies optimization opportunities';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2308
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues = array();

		// Check if query debugging is enabled
		$debug_queries = defined( 'SAVEQUERIES' ) && SAVEQUERIES;

		if ( ! $debug_queries ) {
			$issues[] = __( 'Query debugging is not enabled (SAVEQUERIES)', 'wpshadow' );
		}

		// Check for database optimization plugins
		$optimization_plugins = array(
			'wp-super-cache/wp-cache.php',
			'w3-total-cache/w3-total-cache.php',
			'litespeed-cache/litespeed-cache.php',
			'wp-rocket/wp-rocket.php',
			'autoptimize/autoptimize.php',
		);

		$has_optimization = false;
		foreach ( $optimization_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_optimization = true;
				break;
			}
		}

		if ( ! $has_optimization ) {
			$issues[] = __( 'No database optimization plugin detected', 'wpshadow' );
		}

		// Check if database prefix is customized (security best practice)
		if ( $wpdb->prefix === 'wp_' ) {
			$issues[] = __( 'Using default database prefix (wp_) - consider customizing for security', 'wpshadow' );
		}

		// Check database size
		$query = "SELECT SUM(data_length + index_length) FROM information_schema.tables WHERE table_schema = %s";
		$result = $wpdb->get_var( $wpdb->prepare( $query, DB_NAME ) );
		$db_size_mb = $result ? $result / 1024 / 1024 : 0;

		if ( $db_size_mb > 500 ) {
			$issues[] = sprintf(
				/* translators: %s: database size in MB */
				__( 'Database is %sMB - consider running optimization', 'wpshadow' ),
				round( $db_size_mb, 2 )
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					/* translators: %d: number of issues */
					__( 'Found %d database performance concerns', 'wpshadow' ),
					count( $issues )
				),
				'severity'      => 'medium',
				'threat_level'  => 40,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/database-query-performance-audit',
			);
		}

		return null;
	}
}
