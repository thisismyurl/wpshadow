<?php
/**
 * Comment Meta Table Orphan Detection Diagnostic
 *
 * Finds orphaned comment meta rows after comment deletions.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26029.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Comment Meta Table Orphan Detection Class
 *
 * Tests commentmeta orphans.
 *
 * @since 1.26029.0000
 */
class Diagnostic_Comment_Meta_Table_Orphan_Detection extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-meta-table-orphan-detection';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Meta Table Orphan Detection';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Finds orphaned comment meta rows after comment deletions';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'compliance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26029.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$orphan_check = self::check_orphaned_commentmeta();
		
		if ( $orphan_check['orphaned_count'] > 100 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of orphaned rows */
					__( '%d orphaned commentmeta rows found (waste space after comment deletions)', 'wpshadow' ),
					$orphan_check['orphaned_count']
				),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/comment-meta-table-orphan-detection',
				'meta'         => array(
					'orphaned_count' => $orphan_check['orphaned_count'],
					'wasted_space'   => $orphan_check['wasted_space'],
				),
			);
		}

		return null;
	}

	/**
	 * Check for orphaned commentmeta.
	 *
	 * @since  1.26029.0000
	 * @return array Check results.
	 */
	private static function check_orphaned_commentmeta() {
		global $wpdb;

		$check = array(
			'orphaned_count' => 0,
			'wasted_space'   => 0,
		);

		// Count orphaned commentmeta.
		$check['orphaned_count'] = (int) $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->commentmeta} cm
			LEFT JOIN {$wpdb->comments} c ON cm.comment_id = c.comment_ID
			WHERE c.comment_ID IS NULL"
		);

		// Estimate wasted space.
		if ( $check['orphaned_count'] > 0 ) {
			$avg_meta_size = (int) $wpdb->get_var(
				"SELECT AVG(LENGTH(meta_value))
				FROM {$wpdb->commentmeta}"
			);

			$check['wasted_space'] = $check['orphaned_count'] * $avg_meta_size;
		}

		return $check;
	}
}
