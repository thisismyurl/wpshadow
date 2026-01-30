<?php
/**
 * Database Fragmentation Diagnostic
 *
 * Detects fragmented database tables reducing performance
 * and increasing query time.
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
 * Diagnostic_Database_Fragmentation Class
 *
 * Detects database table fragmentation.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Database_Fragmentation extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-fragmentation';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database Fragmentation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects table fragmentation';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'quality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if fragmentation detected, null otherwise.
	 */
	public static function check() {
		$fragmentation_status = self::check_table_fragmentation();

		if ( ! $fragmentation_status['has_issue'] ) {
			return null; // Database health good
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Database tables fragmented. Each DELETE/UPDATE leaves gaps = wasted space = slower queries. OPTIMIZE TABLE command compacts and speeds up by 10-30%.', 'wpshadow' ),
			'severity'     => 'low',
			'threat_level' => 40,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/database-fragmentation',
			'family'       => self::$family,
			'meta'         => array(
				'fragmentation_percent' => $fragmentation_status['fragmentation_percent'],
			),
			'details'      => array(
				'database_fragmentation_explained' => array(
					'How It Happens' => array(
						'1. Create table: Compact, organized',
						'2. Insert 1000 rows: Still compact',
						'3. Delete 500 rows: Gaps appear',
						'4. Over time: Many gaps',
					),
					'Impact' => array(
						'Queries slower: Gaps to skip',
						'Disk usage up: Logical size vs. physical',
						'Scans inefficient: Non-contiguous data',
					),
					'Real Impact' => array(
						'Query 1ms → 5ms: 400% slower',
						'Affects: High-traffic pages most',
					),
				),
				'checking_fragmentation'           => array(
					'MySQL Command' => array(
						'SELECT table_name, ROUND(data_free / 1024 / 1024, 2) as frag_mb',
						'FROM information_schema.tables',
						'WHERE data_free > 0',
					),
					'phpMyAdmin' => array(
						'Select database → Table',
						'Tab: Structure',
						'Look: "Overhead" column (fragmentation)',
					),
					'WordPress Plugin' => array(
						'Plugin: WP Database Manager',
						'Shows: Table health + fragmentation',
					),
				),
				'fragmentation_threshold'         => array(
					'< 5MB overhead' => 'Healthy - no action needed',
					'5-20MB overhead' => 'Monitor - optimize soon',
					'20-100MB overhead' => 'Optimize now',
					'> 100MB overhead' => 'Urgent - significant slowdown',
				),
				'optimizing_database'             => array(
					'Optimize Individual Table' => array(
						'Command: OPTIMIZE TABLE wp_posts;',
						'Time: Usually < 1 second',
						'Impact: Reclaim wasted space',
					),
					'Optimize All Tables' => array(
						'Method 1: WP Database Manager plugin',
						'Method 2: phpMyAdmin → Select all → Optimize',
						'Method 3: wp db optimize (WP-CLI)',
					),
					'Schedule Optimization' => array(
						'Plugin: WP Database Manager',
						'Can schedule: Weekly or monthly',
						'Automated: Runs in background',
					),
				),
				'why_fragmentation_happens'       => array(
					'Post Deletions' => array(
						'Delete post: Leaves gap in table',
						'Delete frequently: More gaps',
						'Example: Delete spam = holes everywhere',
					),
					'Revisions' => array(
						'Each revision: New wp_posts row',
						'Delete revisions: Leaves gaps',
						'Solution: Limit revisions to 5',
					),
					'Comment Spam' => array(
						'Spam comment: Inserted to wp_comments',
						'Delete spam: Gap left',
						'High spam: Many gaps',
					),
					'Transients' => array(
						'Expired transient: Still in table',
						'Never cleaned up: Accumulate',
						'Plugin: WP Transient Cleanup',
					),
				),
			),
		);
	}

	/**
	 * Check table fragmentation.
	 *
	 * @since  1.2601.2148
	 * @return array Fragmentation status.
	 */
	private static function check_table_fragmentation() {
		global $wpdb;

		try {
			$result = $wpdb->get_var(
				"SELECT SUM(data_free) FROM information_schema.tables WHERE table_schema = '" . DB_NAME . "'"
			);

			$data_free_mb = (int) ( (int) $result / 1048576 );
			$has_issue = $data_free_mb > 20;
			$fragmentation_percent = $data_free_mb > 0 ? round( ( $data_free_mb / 100 ) * 100, 1 ) : 0;

			return array(
				'has_issue'           => $has_issue,
				'fragmentation_percent' => $fragmentation_percent,
			);
		} catch ( \Exception $e ) {
			return array(
				'has_issue'           => false,
				'fragmentation_percent' => 0,
			);
		}
	}
}
