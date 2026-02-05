<?php
/**
 * Hidden Meta Field Bloat Treatment
 *
 * Identifies excessive hidden meta fields that bloat the postmeta table. Measures
 * meta table size and detects plugins creating unnecessary hidden fields.
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
 * Hidden Meta Field Bloat Treatment Class
 *
 * Checks for excessive hidden meta field bloat in the postmeta table.
 *
 * @since 1.6030.2148
 */
class Treatment_Hidden_Meta_Field_Bloat extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'hidden-meta-field-bloat';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Hidden Meta Field Bloat';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Identifies excessive hidden meta fields bloating the database';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'posts';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6030.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues = array();

		// Count total postmeta rows.
		$total_meta = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->postmeta}" );

		// Count hidden meta (prefixed with underscore).
		$hidden_meta = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key LIKE '\\_%%'"
		);

		if ( $total_meta > 0 ) {
			$hidden_percentage = ( $hidden_meta / $total_meta ) * 100;

			if ( $hidden_percentage > 80 ) {
				$issues[] = sprintf(
					/* translators: %d: percentage */
					__( '%d%% of post meta is hidden (excessive hidden field bloat)', 'wpshadow' ),
					round( $hidden_percentage )
				);
			}
		}

		// Find meta keys with excessive usage.
		$excessive_meta_keys = $wpdb->get_results(
			"SELECT meta_key, COUNT(*) as count
			FROM {$wpdb->postmeta}
			WHERE meta_key LIKE '\\_%%'
			GROUP BY meta_key
			HAVING count > 5000
			ORDER BY count DESC
			LIMIT 10",
			ARRAY_A
		);

		if ( ! empty( $excessive_meta_keys ) ) {
			foreach ( $excessive_meta_keys as $meta_key_data ) {
				$issues[] = sprintf(
					/* translators: 1: meta key, 2: count */
					__( 'Hidden meta key "%1$s" has %2$s entries (excessive bloat)', 'wpshadow' ),
					esc_html( $meta_key_data['meta_key'] ),
					number_format_i18n( $meta_key_data['count'] )
				);
			}
		}

		// Check for meta keys that look like temporary/cache data.
		$temp_meta_patterns = array( '%cache%', '%temp%', '%transient%', '%lock%', '%queue%' );
		$temp_meta_count = 0;

		foreach ( $temp_meta_patterns as $pattern ) {
			$temp_meta_count += (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key LIKE %s",
					$pattern
				)
			);
		}

		if ( $temp_meta_count > 1000 ) {
			$issues[] = sprintf(
				/* translators: %d: count of temporary entries */
				__( '%s temporary/cache meta entries in postmeta table (should use transients)', 'wpshadow' ),
				number_format_i18n( $temp_meta_count )
			);
		}

		// Check for meta associated with deleted posts (orphaned).
		$orphaned_meta = $wpdb->get_var(
			"SELECT COUNT(pm.meta_id)
			FROM {$wpdb->postmeta} pm
			LEFT JOIN {$wpdb->posts} p ON pm.post_id = p.ID
			WHERE p.ID IS NULL"
		);

		if ( $orphaned_meta > 500 ) {
			$issues[] = sprintf(
				/* translators: %s: count of orphaned entries */
				__( '%s orphaned meta entries (attached to deleted posts)', 'wpshadow' ),
				number_format_i18n( $orphaned_meta )
			);
		}

		// Check average meta per post.
		$post_count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status != 'auto-draft'"
		);

		if ( $post_count > 0 && $total_meta > 0 ) {
			$avg_meta_per_post = $total_meta / $post_count;

			if ( $avg_meta_per_post > 50 ) {
				$issues[] = sprintf(
					/* translators: %d: average count */
					__( 'Average %d meta fields per post (excessive, may slow queries)', 'wpshadow' ),
					round( $avg_meta_per_post )
				);
			}
		}

		// Check postmeta table size.
		$table_size = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT ROUND((data_length + index_length) / 1024 / 1024, 2)
				FROM information_schema.TABLES
				WHERE table_schema = %s
				AND table_name = %s",
				DB_NAME,
				$wpdb->postmeta
			)
		);

		if ( $table_size > 500 ) {
			$issues[] = sprintf(
				/* translators: %s: table size in MB */
				__( 'Postmeta table is %s MB (consider cleanup or archiving)', 'wpshadow' ),
				number_format_i18n( $table_size )
			);
		}

		// Check for duplicate meta entries (same post_id, meta_key, meta_value).
		$duplicates = $wpdb->get_var(
			"SELECT COUNT(*) FROM (
				SELECT post_id, meta_key, meta_value, COUNT(*) as count
				FROM {$wpdb->postmeta}
				GROUP BY post_id, meta_key, meta_value
				HAVING count > 1
			) as duplicates"
		);

		if ( $duplicates > 100 ) {
			$issues[] = sprintf(
				/* translators: %d: number of duplicate sets */
				__( '%d sets of duplicate meta entries (database bloat)', 'wpshadow' ),
				$duplicates
			);
		}

		// Check for meta keys that are excessively long.
		$long_meta_keys = $wpdb->get_var(
			"SELECT COUNT(DISTINCT meta_key)
			FROM {$wpdb->postmeta}
			WHERE LENGTH(meta_key) > 100"
		);

		if ( $long_meta_keys > 10 ) {
			$issues[] = sprintf(
				/* translators: %d: number of long keys */
				__( '%d meta keys longer than 100 characters (inefficient indexing)', 'wpshadow' ),
				$long_meta_keys
			);
		}

		// Check for meta values that are excessively large.
		$large_meta_values = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->postmeta}
			WHERE LENGTH(meta_value) > 1048576"
		);

		if ( $large_meta_values > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of large values */
				__( '%d meta values larger than 1MB (should use separate storage)', 'wpshadow' ),
				$large_meta_values
			);
		}

		// Check if table has fragmentation.
		$data_free = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT ROUND(data_free / 1024 / 1024, 2)
				FROM information_schema.TABLES
				WHERE table_schema = %s
				AND table_name = %s",
				DB_NAME,
				$wpdb->postmeta
			)
		);

		if ( $data_free > 100 ) {
			$issues[] = sprintf(
				/* translators: %s: fragmentation size in MB */
				__( 'Postmeta table has %s MB fragmentation (run OPTIMIZE TABLE)', 'wpshadow' ),
				number_format_i18n( $data_free )
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( '. ', $issues ),
				'severity'    => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/hidden-meta-field-bloat',
			);
		}

		return null;
	}
}
