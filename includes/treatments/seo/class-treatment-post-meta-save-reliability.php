<?php
/**
 * Post Meta Save Reliability Treatment
 *
 * Verifies post meta data saves correctly by testing update_post_meta
 * and checking for data persistence issues.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6030.2148
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post Meta Save Reliability Class
 *
 * Tests post meta save operations and detects issues that may cause
 * meta data to fail saving or be lost during updates.
 *
 * @since 1.6030.2148
 */
class Treatment_Post_Meta_Save_Reliability extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'post-meta-save-reliability';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Post Meta Save Reliability';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies post meta data saves correctly';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content';

	/**
	 * Run the treatment check.
	 *
	 * Tests post meta save reliability and checks for common issues
	 * that prevent meta data from persisting correctly.
	 *
	 * @since  1.6030.2148
	 * @return array|null Finding array if save issues found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues = array();
		$details = array();

		// Check postmeta table integrity.
		$table_check = $wpdb->get_var(
			$wpdb->prepare(
				'SHOW TABLES LIKE %s',
				$wpdb->postmeta
			)
		);

		if ( ! $table_check ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Critical: postmeta table is missing! Post meta cannot be saved.', 'wpshadow' ),
				'severity'    => 'critical',
				'threat_level' => 90,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/post-meta-save-reliability',
				'details'     => array(),
			);
		}

		// Check for duplicate meta entries (indicates save issues).
		$duplicates = $wpdb->get_results(
			"SELECT post_id, meta_key, COUNT(*) as count
			FROM {$wpdb->postmeta}
			GROUP BY post_id, meta_key
			HAVING count > 1
			ORDER BY count DESC
			LIMIT 10"
		);

		if ( ! empty( $duplicates ) ) {
			$duplicate_count = $wpdb->get_var(
				"SELECT COUNT(*)
				FROM (
					SELECT post_id, meta_key
					FROM {$wpdb->postmeta}
					GROUP BY post_id, meta_key
					HAVING COUNT(*) > 1
				) as dupes"
			);

			$issues[] = sprintf(
				/* translators: %d: number of posts with duplicate meta */
				_n(
					'Found %d post with duplicate meta keys (indicates save conflicts)',
					'Found %d posts with duplicate meta keys (indicates save conflicts)',
					(int) $duplicate_count,
					'wpshadow'
				),
				number_format_i18n( (int) $duplicate_count )
			);

			$details['duplicate_meta'] = array_map(
				function( $dup ) {
					return array(
						'post_id'  => $dup->post_id,
						'meta_key' => $dup->meta_key,
						'count'    => $dup->count,
					);
				},
				$duplicates
			);
		}

		// Check for serialized data corruption.
		$corrupted = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->postmeta}
			WHERE meta_value LIKE 'a:%'
			AND meta_value NOT REGEXP '^a:[0-9]+:\\{.*\\}$'"
		);

		if ( $corrupted && (int) $corrupted > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of corrupted meta entries */
				_n(
					'Found %d meta entry with corrupted serialized data',
					'Found %d meta entries with corrupted serialized data',
					(int) $corrupted,
					'wpshadow'
				),
				number_format_i18n( (int) $corrupted )
			);

			$details['corrupted_serialized'] = (int) $corrupted;
		}

		// Check for very large meta values (may fail to save).
		$large_meta = $wpdb->get_results(
			"SELECT post_id, meta_key, LENGTH(meta_value) as size
			FROM {$wpdb->postmeta}
			WHERE LENGTH(meta_value) > 1000000
			ORDER BY size DESC
			LIMIT 10"
		);

		if ( ! empty( $large_meta ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of large meta entries */
				_n(
					'Found %d meta entry over 1MB (may cause save timeouts)',
					'Found %d meta entries over 1MB (may cause save timeouts)',
					count( $large_meta ),
					'wpshadow'
				),
				number_format_i18n( count( $large_meta ) )
			);

			$details['large_meta'] = array_map(
				function( $meta ) {
					return array(
						'post_id'  => $meta->post_id,
						'meta_key' => $meta->meta_key,
						'size_mb'  => round( (int) $meta->size / 1048576, 2 ),
					);
				},
				$large_meta
			);
		}

		// Check for orphaned meta (post deleted but meta remains).
		$orphaned = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->postmeta} pm
			LEFT JOIN {$wpdb->posts} p ON pm.post_id = p.ID
			WHERE p.ID IS NULL"
		);

		if ( $orphaned && (int) $orphaned > 100 ) {
			$issues[] = sprintf(
				/* translators: %d: number of orphaned meta entries */
				_n(
					'Found %d orphaned meta entry (post deleted)',
					'Found %d orphaned meta entries (posts deleted)',
					(int) $orphaned,
					'wpshadow'
				),
				number_format_i18n( (int) $orphaned )
			);

			$details['orphaned_meta'] = (int) $orphaned;
		}

		// Check for meta keys with leading/trailing spaces.
		$bad_keys = $wpdb->get_var(
			"SELECT COUNT(DISTINCT meta_key)
			FROM {$wpdb->postmeta}
			WHERE meta_key != TRIM(meta_key)"
		);

		if ( $bad_keys && (int) $bad_keys > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of bad meta keys */
				_n(
					'Found %d meta key with whitespace (prevents reliable lookups)',
					'Found %d meta keys with whitespace (prevents reliable lookups)',
					(int) $bad_keys,
					'wpshadow'
				),
				number_format_i18n( (int) $bad_keys )
			);

			$details['bad_meta_keys'] = (int) $bad_keys;
		}

		// Check if protected meta is being saved as public.
		$protected_as_public = $wpdb->get_var(
			"SELECT COUNT(DISTINCT meta_key)
			FROM {$wpdb->postmeta}
			WHERE meta_key LIKE '\_%'
			AND meta_key IN (
				SELECT meta_key FROM {$wpdb->postmeta}
				WHERE meta_key NOT LIKE '\_%'
			)"
		);

		if ( $protected_as_public && (int) $protected_as_public > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of affected meta keys */
				__( 'Found %d protected meta keys also saved without underscore prefix', 'wpshadow' ),
				number_format_i18n( (int) $protected_as_public )
			);

			$details['protected_as_public'] = (int) $protected_as_public;
		}

		// Check postmeta table size and suggest optimization.
		$table_size = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT ROUND((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024, 2)
				FROM information_schema.TABLES
				WHERE TABLE_SCHEMA = %s
				AND TABLE_NAME = %s",
				DB_NAME,
				$wpdb->postmeta
			)
		);

		$details['table_size_mb'] = (float) $table_size;

		if ( $table_size && (float) $table_size > 500 ) {
			$issues[] = sprintf(
				/* translators: %s: table size in MB */
				__( 'Postmeta table is %s MB - consider optimization', 'wpshadow' ),
				number_format_i18n( (float) $table_size, 2 )
			);
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => implode( '. ', $issues ),
			'severity'    => 'medium',
			'threat_level' => 60,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/post-meta-save-reliability',
			'details'     => $details,
		);
	}
}
