<?php
/**
 * Database Size Analysis Diagnostic
 *
 * Monitors database size growth and identifies bloat from
 * revisions, transients, and unused data to maintain performance.
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
 * Diagnostic_Database_Size_Analysis Class
 *
 * Analyzes database size and growth patterns.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Database_Size_Analysis extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-size-analysis';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database Size Analysis';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Monitors database size growth and identifies bloat';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'infrastructure';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if database bloat issues found, null otherwise.
	 */
	public static function check() {
		$db_analysis = self::analyze_database();

		if ( $db_analysis['size_mb'] < 500 ) {
			return null; // Small database, no concerns
		}

		$severity = $db_analysis['size_mb'] > 5000 ? 'high' : 'medium';
		$threat   = $db_analysis['size_mb'] > 5000 ? 70 : 50;

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: database size in MB */
				__( 'Database is large (%dMB) and may contain bloat. Backups take longer, restoration slower, queries slower.', 'wpshadow' ),
				$db_analysis['size_mb']
			),
			'severity'     => $severity,
			'threat_level' => $threat,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/database-optimization',
			'family'       => self::$family,
			'meta'         => array(
				'current_size_mb'  => $db_analysis['size_mb'],
				'typical_for_posts' => $db_analysis['post_count'] . ' posts',
				'backup_time'      => $db_analysis['estimated_backup_time'],
				'optimization_potential' => '20-40% size reduction typical',
			),
			'details'      => array(
				'common_bloat_sources'      => array(
					'Post Revisions' => array(
						'By default: WordPress saves every edit as revision',
						'Impact: 100 edits = 100 revisions',
						'Size: Can be 50-60% of total DB',
						'Fix: Delete old revisions, limit to 5 per post',
					),
					'Trashed Posts/Comments' => array(
						'By default: Trashed items stay in database',
						'Impact: Can accumulate to thousands',
						'Size: 10-20% of total DB',
						'Fix: Permanently delete from trash',
					),
					'Transients' => array(
						'Expired: Plugins leave transients in database',
						'Impact: Accumulate over time',
						'Size: 5-10% of total DB',
						'Fix: Clean up expired transients',
					),
					'Post Meta / Option Data' => array(
						'Accumulation: Plugins add options without cleanup',
						'Impact: Unused plugin data accumulates',
						'Size: 5-15% of total DB',
						'Fix: Deactivate unused plugins, clean metadata',
					),
					'WooCommerce Logs' => array(
						'By default: Logs every transaction',
						'Impact: Grows daily',
						'Size: Can be 30% on e-commerce',
						'Fix: Archive old logs, set retention',
					),
				),
				'optimization_strategies'   => array(
					'Quick Wins (1-2 hours)' => array(
						'Delete posts from trash (Ctrl+Delete)',
						'Clean transients: WP Sweep plugin (free)',
						'Remove unused plugin options',
						'Archive WooCommerce logs >90 days',
					),
					'Medium Effort (2-4 hours)' => array(
						'Limit post revisions: define("WP_POST_REVISIONS", 5);',
						'Optimize database: wp-cli db optimize',
						'Delete orphaned metadata',
						'Review plugin data: MyPHPAdmin',
					),
					'Advanced (4+ hours)' => array(
						'Manual revision deletion: DELETE FROM wp_posts WHERE post_type="revision"',
						'Archive old posts to separate table',
						'Implement transient purging cron job',
						'Separate WooCommerce logs table',
					),
				),
				'optimization_plugins'      => array(
					'WP-Sweep (Free)' => array(
						'One-click cleanup',
						'Removes expired transients',
						'Removes unused options',
						'Recommended for beginners',
					),
					'Advanced Database Cleaner' => array(
						'Detailed cleanup options',
						'Orphaned metadata removal',
						'Custom SQL queries',
						'More control, steeper learning curve',
					),
					'WP Optimize (Premium)' => array(
						'Automated cleanup',
						'Database compression',
						'Scheduled optimization',
						'Cost: $50-150/year',
					),
				),
				'manual_cleanup_sql'        => array(
					'WARNING: Backup before running SQL!' => array(
						'Delete all post revisions:',
						'DELETE FROM wp_posts WHERE post_type = "revision"',
					),
					'Delete posts from trash' => array(
						'DELETE FROM wp_posts WHERE post_status = "trash"',
					),
					'Delete expired transients' => array(
						'DELETE FROM wp_options WHERE option_name LIKE "%_transient_%"',
					),
					'Optimize all tables' => array(
						'OPTIMIZE TABLE wp_posts, wp_postmeta, wp_users',
					),
				),
				'prevention'                => array(
					__( 'Limit post revisions: wp-config.php setting' ),
					__( 'Set old post archive threshold: 1 year' ),
					__( 'Schedule weekly WP-Sweep cleanup' ),
					__( 'Deactivate unused plugins immediately' ),
					__( 'Set WooCommerce log retention: 90 days' ),
					__( 'Monitor database size monthly' ),
				),
			),
		);
	}

	/**
	 * Analyze database.
	 *
	 * @since  1.2601.2148
	 * @return array Database analysis.
	 */
	private static function analyze_database() {
		global $wpdb;

		// Get database name
		$db_name = defined( 'DB_NAME' ) ? DB_NAME : '';

		// Get database size
		$db_size = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) 
				FROM information_schema.TABLES 
				WHERE table_schema = %s",
				$db_name
			)
		);

		// Get post count
		$post_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type IN ('post', 'page', 'product')" );

		// Estimate backup time (roughly 1MB per second on average)
		$backup_seconds  = (int) $db_size;
		$backup_minutes  = $backup_seconds / 60;
		$backup_estimate = $backup_minutes > 60 ? round( $backup_minutes / 60, 1 ) . ' hours' : round( $backup_minutes, 0 ) . ' minutes';

		return array(
			'size_mb'                 => (float) $db_size,
			'post_count'              => $post_count,
			'estimated_backup_time'   => $backup_estimate,
		);
	}
}
