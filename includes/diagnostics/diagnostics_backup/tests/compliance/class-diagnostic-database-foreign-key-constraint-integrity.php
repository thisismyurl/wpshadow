<?php
/**
 * Database Foreign Key Constraint Integrity Diagnostic
 *
 * Checks for orphaned records caused by missing foreign key constraints.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26028.1905
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database Foreign Key Constraint Integrity Class
 *
 * Tests data integrity.
 *
 * @since 1.26028.1905
 */
class Diagnostic_Database_Foreign_Key_Constraint_Integrity extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-foreign-key-constraint-integrity';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database Foreign Key Constraint Integrity';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for orphaned records caused by missing foreign key constraints';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'compliance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26028.1905
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$orphaned_check = self::check_orphaned_records();
		
		if ( $orphaned_check['total_orphaned'] > 0 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of orphaned records */
					__( 'Found %d orphaned records (postmeta, comments, term relationships with deleted parents)', 'wpshadow' ),
					$orphaned_check['total_orphaned']
				),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/database-foreign-key-constraint-integrity',
				'meta'         => array(
					'orphaned_postmeta'          => $orphaned_check['orphaned_postmeta'],
					'orphaned_comments'          => $orphaned_check['orphaned_comments'],
					'orphaned_term_relationships' => $orphaned_check['orphaned_term_relationships'],
					'total_orphaned'             => $orphaned_check['total_orphaned'],
				),
			);
		}

		return null;
	}

	/**
	 * Check for orphaned records.
	 *
	 * @since  1.26028.1905
	 * @return array Check results.
	 */
	private static function check_orphaned_records() {
		global $wpdb;

		$check = array(
			'orphaned_postmeta'          => 0,
			'orphaned_comments'          => 0,
			'orphaned_term_relationships' => 0,
			'total_orphaned'             => 0,
		);

		// Check orphaned postmeta.
		$check['orphaned_postmeta'] = (int) $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->postmeta} pm
			LEFT JOIN {$wpdb->posts} p ON pm.post_id = p.ID
			WHERE p.ID IS NULL
			LIMIT 1000"
		);

		// Check orphaned comments.
		$check['orphaned_comments'] = (int) $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->comments} c
			LEFT JOIN {$wpdb->posts} p ON c.comment_post_ID = p.ID
			WHERE p.ID IS NULL
			LIMIT 1000"
		);

		// Check orphaned term relationships.
		$check['orphaned_term_relationships'] = (int) $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->term_relationships} tr
			LEFT JOIN {$wpdb->posts} p ON tr.object_id = p.ID
			WHERE p.ID IS NULL AND tr.object_id != 0
			LIMIT 1000"
		);

		$check['total_orphaned'] = $check['orphaned_postmeta'] + $check['orphaned_comments'] + $check['orphaned_term_relationships'];

		return $check;
	}
}
