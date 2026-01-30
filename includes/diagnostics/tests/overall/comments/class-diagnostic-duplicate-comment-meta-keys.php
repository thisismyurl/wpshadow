<?php
/**
 * Duplicate Comment Meta Keys Diagnostic
 *
 * Identifies duplicate comment meta entries that waste database space
 * and can slow down comment queries.
 *
 * @package    WPShadow\Diagnostics
 * @subpackage Tests
 * @since      1.2601.2205
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Duplicate Comment Meta Keys Diagnostic Class
 *
 * Checks for:
 * - Identical meta_key and meta_value for same comment_id
 * - Multiple entries of same meta_key with identical values
 * - Orphaned comment meta (comment no longer exists)
 *
 * @since 1.2601.2205
 */
class Diagnostic_Duplicate_Comment_Meta_Keys extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'duplicate-comment-meta-keys';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Duplicate Comment Meta Keys';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Identifies duplicate comment meta entries';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'database';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2205
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Find duplicate comment meta.
		$duplicates = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->commentmeta} cm1
			INNER JOIN {$wpdb->commentmeta} cm2
			ON cm1.comment_id = cm2.comment_id
			AND cm1.meta_key = cm2.meta_key
			AND cm1.meta_value = cm2.meta_value
			AND cm1.meta_id < cm2.meta_id"
		);

		// Find orphaned comment meta.
		$orphaned = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->commentmeta} cm
			LEFT JOIN {$wpdb->comments} c ON cm.comment_id = c.comment_ID
			WHERE c.comment_ID IS NULL"
		);

		// Find meta with suspicious patterns.
		$large_meta = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT meta_key, COUNT(*) as count, SUM(LENGTH(meta_value)) as total_size
				FROM {$wpdb->commentmeta}
				GROUP BY meta_key
				HAVING COUNT(*) > %d
				ORDER BY total_size DESC
				LIMIT 10",
				100
			)
		);

		$issues = array();

		if ( $duplicates > 0 ) {
			$issues[] = sprintf(
				__( '%d duplicate comment meta entries found', 'wpshadow' ),
				$duplicates
			);
		}

		if ( $orphaned > 0 ) {
			$issues[] = sprintf(
				__( '%d orphaned comment meta entries (comment deleted but meta remains)', 'wpshadow' ),
				$orphaned
			);
		}

		if ( ! empty( $large_meta ) ) {
			foreach ( $large_meta as $meta ) {
				$size_mb = $meta->total_size / ( 1024 * 1024 );
				if ( $size_mb > 1 ) {
					$issues[] = sprintf(
						__( 'Meta key "%s" has %d entries totaling %.2f MB', 'wpshadow' ),
						$meta->meta_key,
						$meta->count,
						$size_mb
					);
				}
			}
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => implode( "\n", $issues ),
			'severity'     => 'low',
			'threat_level' => 35,
			'auto_fixable' => true,
			'kb_link'      => 'https://wpshadow.com/kb/duplicate-comment-meta',
		);
	}
}
