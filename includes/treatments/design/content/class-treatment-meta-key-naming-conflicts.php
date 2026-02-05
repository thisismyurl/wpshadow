<?php
/**
 * Meta Key Naming Conflicts Treatment
 *
 * Detects meta key naming conflicts between plugins that could cause data overwrites
 * or unexpected behavior. Tests for duplicate or conflicting key patterns.
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
 * Meta Key Naming Conflicts Treatment Class
 *
 * Checks for meta key naming conflicts between plugins.
 *
 * @since 1.6030.2148
 */
class Treatment_Meta_Key_Naming_Conflicts extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'meta-key-naming-conflicts';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Meta Key Naming Conflicts';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Detects meta key naming conflicts between plugins that cause data issues';

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

		// Get all meta keys with usage counts.
		$meta_keys = $wpdb->get_results(
			"SELECT meta_key, COUNT(*) as usage_count
			FROM {$wpdb->postmeta}
			GROUP BY meta_key
			HAVING usage_count > 10
			ORDER BY usage_count DESC
			LIMIT 200",
			ARRAY_A
		);

		// Check for keys that look generic (likely conflicts).
		$generic_patterns = array(
			'/^title$/',
			'/^description$/',
			'/^image$/',
			'/^url$/',
			'/^link$/',
			'/^price$/',
			'/^date$/',
			'/^author$/',
			'/^status$/',
			'/^type$/',
			'/^category$/',
			'/^tag$/',
			'/^value$/',
			'/^data$/',
			'/^content$/',
			'/^settings$/',
		);

		$generic_conflicts = 0;
		$conflicting_keys = array();

		foreach ( $meta_keys as $meta_key_data ) {
			$key = $meta_key_data['meta_key'];
			
			// Skip private meta keys.
			if ( strpos( $key, '_' ) === 0 ) {
				continue;
			}

			foreach ( $generic_patterns as $pattern ) {
				if ( preg_match( $pattern, $key ) ) {
					++$generic_conflicts;
					$conflicting_keys[] = $key;
					break;
				}
			}
		}

		if ( $generic_conflicts > 5 ) {
			$issues[] = sprintf(
				/* translators: 1: number of conflicts, 2: example keys */
				__( '%1$d generic meta keys likely causing conflicts (e.g., %2$s)', 'wpshadow' ),
				$generic_conflicts,
				implode( ', ', array_slice( $conflicting_keys, 0, 3 ) )
			);
		}

		// Check for keys with different data types for same key name.
		$type_conflicts = $wpdb->get_results(
			"SELECT meta_key, COUNT(DISTINCT 
				CASE 
					WHEN meta_value REGEXP '^[0-9]+$' THEN 'integer'
					WHEN meta_value REGEXP '^[0-9]+\\.[0-9]+$' THEN 'float'
					WHEN meta_value LIKE 'a:%' OR meta_value LIKE 'O:%' THEN 'serialized'
					WHEN meta_value IN ('0', '1') THEN 'boolean'
					ELSE 'string'
				END
			) as type_count
			FROM {$wpdb->postmeta}
			WHERE meta_key NOT LIKE '\\_%%'
			GROUP BY meta_key
			HAVING type_count > 2
			LIMIT 20",
			ARRAY_A
		);

		if ( ! empty( $type_conflicts ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of keys with type conflicts */
				__( '%d meta keys have mixed data types (indicates multiple plugins using same key)', 'wpshadow' ),
				count( $type_conflicts )
			);
		}

		// Check for keys that differ only by case.
		$all_keys = wp_list_pluck( $meta_keys, 'meta_key' );
		$lowercase_keys = array_map( 'strtolower', $all_keys );
		$case_duplicates = array_diff_assoc( $lowercase_keys, array_unique( $lowercase_keys ) );

		if ( ! empty( $case_duplicates ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of case-sensitive duplicates */
				__( '%d meta keys differ only by case (e.g., "Title" vs "title")', 'wpshadow' ),
				count( $case_duplicates )
			);
		}

		// Check for keys with similar prefixes that suggest namespace collisions.
		$prefix_groups = array();
		foreach ( $meta_keys as $meta_key_data ) {
			$key = $meta_key_data['meta_key'];
			
			// Skip private keys.
			if ( strpos( $key, '_' ) === 0 ) {
				continue;
			}

			// Extract prefix (first word before underscore or camelCase).
			if ( preg_match( '/^([a-z]+)[_A-Z]/', $key, $matches ) ) {
				$prefix = $matches[1];
				if ( ! isset( $prefix_groups[ $prefix ] ) ) {
					$prefix_groups[ $prefix ] = 0;
				}
				++$prefix_groups[ $prefix ];
			}
		}

		// Check for prefixes used by multiple plugins (likely conflicts).
		$common_prefixes = array_filter( $prefix_groups, function( $count ) {
			return $count > 20;
		} );

		if ( ! empty( $common_prefixes ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of overused prefixes */
				__( '%d meta key prefixes overused (plugins not properly namespacing keys)', 'wpshadow' ),
				count( $common_prefixes )
			);
		}

		// Check for keys registered by WordPress core being overridden.
		$core_meta_keys = array(
			'_edit_last',
			'_edit_lock',
			'_wp_page_template',
			'_thumbnail_id',
			'_wp_attached_file',
			'_wp_attachment_metadata',
		);

		foreach ( $core_meta_keys as $core_key ) {
			// Check if plugins are creating non-private versions.
			$public_version = ltrim( $core_key, '_' );
			$exists = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = %s",
					$public_version
				)
			);

			if ( $exists > 0 ) {
				$issues[] = sprintf(
					/* translators: %s: meta key name */
					__( 'Meta key "%s" conflicts with WordPress core naming pattern', 'wpshadow' ),
					esc_html( $public_version )
				);
				break; // Only report once.
			}
		}

		// Check for excessive unique meta keys (indicates poor key management).
		$unique_key_count = $wpdb->get_var(
			"SELECT COUNT(DISTINCT meta_key) FROM {$wpdb->postmeta}"
		);

		if ( $unique_key_count > 500 ) {
			$issues[] = sprintf(
				/* translators: %d: number of unique keys */
				__( '%d unique meta keys (excessive, indicates poor key management or conflicts)', 'wpshadow' ),
				$unique_key_count
			);
		}

		// Check for meta keys with non-ASCII characters (can cause encoding issues).
		$non_ascii_keys = $wpdb->get_var(
			"SELECT COUNT(DISTINCT meta_key)
			FROM {$wpdb->postmeta}
			WHERE meta_key NOT REGEXP '^[[:ascii:]]+$'"
		);

		if ( $non_ascii_keys > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of non-ASCII keys */
				__( '%d meta keys contain non-ASCII characters (can cause encoding conflicts)', 'wpshadow' ),
				$non_ascii_keys
			);
		}

		// Check for keys with spaces (bad practice).
		$keys_with_spaces = $wpdb->get_var(
			"SELECT COUNT(DISTINCT meta_key)
			FROM {$wpdb->postmeta}
			WHERE meta_key LIKE '%% %%'"
		);

		if ( $keys_with_spaces > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of keys with spaces */
				__( '%d meta keys contain spaces (bad practice, may cause conflicts)', 'wpshadow' ),
				$keys_with_spaces
			);
		}

		// Check for identical keys used by different post types (potential conflicts).
		$cross_type_keys = $wpdb->get_results(
			"SELECT pm.meta_key, COUNT(DISTINCT p.post_type) as type_count
			FROM {$wpdb->postmeta} pm
			INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
			WHERE pm.meta_key NOT LIKE '\\_%%'
			GROUP BY pm.meta_key
			HAVING type_count > 3
			ORDER BY type_count DESC
			LIMIT 10",
			ARRAY_A
		);

		if ( ! empty( $cross_type_keys ) && count( $cross_type_keys ) > 5 ) {
			$issues[] = sprintf(
				/* translators: %d: number of cross-type keys */
				__( '%d meta keys shared across 4+ post types (may indicate improper scoping)', 'wpshadow' ),
				count( $cross_type_keys )
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( '. ', $issues ),
				'severity'    => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/meta-key-naming-conflicts',
			);
		}

		return null;
	}
}
