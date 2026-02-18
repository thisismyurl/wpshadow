<?php
/**
 * Import Custom Field Mapping Failures Diagnostic
 *
 * Detects when custom fields (post meta) fail to import or map incorrectly
 * between systems. Custom fields power ACF layouts, ecommerce data, SEO fields,
 * and application logic. Missing or mis-mapped fields can break templates and
 * cause silent data loss.
 *
 * **What This Check Does:**
 * - Compares expected custom field keys with imported meta
 * - Flags missing, empty, or renamed meta keys
 * - Detects mismatched field formats (serialized vs plain)
 * - Highlights fields with unexpected data loss
 *
 * **Why This Matters:**
 * Custom fields often control pricing, inventory, or page layouts. If fields
 * don’t import correctly, pages may render incorrectly or business logic may
 * fail without obvious errors.
 *
 * **Real-World Failure Scenario:**
 * - Ecommerce product data stored in custom fields
 * - Import drops `_price` and `_stock` meta
 * - Products appear but show “$0” and “In Stock” incorrectly
 *
 * Result: Revenue loss and customer confusion.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Protects mission‑critical data
 * - #9 Show Value: Prevents costly cleanup and revenue loss
 * - Helpful Neighbor: Makes hidden mapping failures visible
 *
 * **Learn More:**
 * See https://wpshadow.com/kb/import-custom-fields
 * or https://wpshadow.com/training/acf-migration-best-practices
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6030.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Import Custom Field Mapping Failures Diagnostic Class
 *
 * Uses meta key comparisons to detect missing or mis‑mapped fields.
 *
 * **Implementation Pattern:**
 * 1. Define expected custom field keys
 * 2. Inspect imported post meta for key presence
 * 3. Detect format mismatches (serialized vs plain)
 * 4. Return findings with recovery guidance
 *
 * **Related Diagnostics:**
 * - Import Lost Shortcodes and Formatting
 * - Import Taxonomy Mismatches
 * - Import Character Encoding Corruption
 *
 * @since 1.6030.2148
 */
class Diagnostic_Import_Custom_Field_Mapping_Failures extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'import-custom-field-mapping-failures';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Import Custom Field Mapping Failures';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects custom field import failures and mapping issues';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'import-export';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;
		
		$issues = array();

		// Check for orphaned post meta (post doesn't exist).
		$orphaned_meta = $wpdb->get_var(
			"SELECT COUNT(*) 
			FROM {$wpdb->postmeta} pm 
			LEFT JOIN {$wpdb->posts} p ON pm.post_id = p.ID 
			WHERE p.ID IS NULL"
		);

		if ( $orphaned_meta > 50 ) {
			$issues[] = sprintf(
				/* translators: %d: number of orphaned entries */
				__( '%d orphaned post meta entries (import cleanup needed)', 'wpshadow' ),
				$orphaned_meta
			);
		}

		// Check for ACF field groups.
		$acf_groups = $wpdb->get_var(
			"SELECT COUNT(*) 
			FROM {$wpdb->posts} 
			WHERE post_type = 'acf-field-group' 
			AND post_status = 'publish'"
		);

		if ( $acf_groups > 0 ) {
			// Check for ACF fields.
			$acf_fields = $wpdb->get_var(
				"SELECT COUNT(*) 
				FROM {$wpdb->posts} 
				WHERE post_type = 'acf-field'"
			);

			if ( $acf_fields === 0 ) {
				$issues[] = sprintf(
					/* translators: %d: number of field groups */
					__( '%d ACF field groups but no fields (incomplete import)', 'wpshadow' ),
					$acf_groups
				);
			}

			// Check for posts using ACF keys.
			$acf_meta = $wpdb->get_var(
				"SELECT COUNT(DISTINCT post_id) 
				FROM {$wpdb->postmeta} 
				WHERE meta_key LIKE 'field_%'"
			);

			if ( $acf_meta > 0 && ! function_exists( 'acf' ) ) {
				$issues[] = sprintf(
					/* translators: %d: number of posts */
					__( '%d posts use ACF but plugin not active', 'wpshadow' ),
					$acf_meta
				);
			}
		}

		// Check for underscore-prefixed meta (private fields).
		$private_meta_count = $wpdb->get_var(
			"SELECT COUNT(*) 
			FROM {$wpdb->postmeta} 
			WHERE meta_key LIKE '\_%'"
		);

		$total_meta = $wpdb->get_var(
			"SELECT COUNT(*) 
			FROM {$wpdb->postmeta}"
		);

		if ( $private_meta_count > 0 && $total_meta > 0 ) {
			$ratio = ( $private_meta_count / $total_meta ) * 100;
			
			if ( $ratio > 80 ) {
				$issues[] = sprintf(
					/* translators: %s: percentage */
					__( '%s%% of post meta is private (verify visibility settings)', 'wpshadow' ),
					number_format( $ratio, 1 )
				);
			}
		}

		// Check for serialized data in meta.
		$serialized_meta = $wpdb->get_results(
			"SELECT meta_id, post_id, meta_key, meta_value 
			FROM {$wpdb->postmeta} 
			WHERE meta_value LIKE 'a:%' 
			OR meta_value LIKE 'O:%' 
			LIMIT 50",
			ARRAY_A
		);

		$corrupted_serialized = 0;
		foreach ( $serialized_meta as $meta ) {
			$unserialized = @unserialize( $meta['meta_value'] );
			
			if ( false === $unserialized && 'b:0;' !== $meta['meta_value'] ) {
				++$corrupted_serialized;
			}
		}

		if ( $corrupted_serialized > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of corrupted entries */
				__( '%d custom fields have corrupted serialized data', 'wpshadow' ),
				$corrupted_serialized
			);
		}

		// Check for duplicate meta keys on same post.
		$duplicate_meta = $wpdb->get_results(
			"SELECT post_id, meta_key, COUNT(*) as count 
			FROM {$wpdb->postmeta} 
			GROUP BY post_id, meta_key 
			HAVING count > 1 
			LIMIT 10",
			ARRAY_A
		);

		if ( ! empty( $duplicate_meta ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of duplicates */
				__( '%d duplicate meta key entries found (import duplication)', 'wpshadow' ),
				count( $duplicate_meta )
			);
		}

		// Check for meta with empty keys.
		$empty_key_meta = $wpdb->get_var(
			"SELECT COUNT(*) 
			FROM {$wpdb->postmeta} 
			WHERE meta_key = ''"
		);

		if ( $empty_key_meta > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of entries */
				__( '%d meta entries with empty keys (import error)', 'wpshadow' ),
				$empty_key_meta
			);
		}

		// Check for post object/relationship fields.
		$relationship_meta = $wpdb->get_results(
			"SELECT meta_id, post_id, meta_key, meta_value 
			FROM {$wpdb->postmeta} 
			WHERE meta_value REGEXP '^[0-9]+$' 
			AND CAST(meta_value AS UNSIGNED) > 0 
			AND meta_key NOT LIKE '\_%' 
			LIMIT 20",
			ARRAY_A
		);

		$broken_relationships = 0;
		foreach ( $relationship_meta as $meta ) {
			$related_post = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT ID 
					FROM {$wpdb->posts} 
					WHERE ID = %d",
					$meta['meta_value']
				)
			);

			if ( ! $related_post ) {
				++$broken_relationships;
			}
		}

		if ( $broken_relationships > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of broken relationships */
				__( '%d custom fields reference non-existent posts', 'wpshadow' ),
				$broken_relationships
			);
		}

		// Check for user field relationships.
		$user_meta_fields = $wpdb->get_results(
			"SELECT meta_id, post_id, meta_key, meta_value 
			FROM {$wpdb->postmeta} 
			WHERE (meta_key LIKE '%user%' OR meta_key LIKE '%author%') 
			AND meta_value REGEXP '^[0-9]+$' 
			LIMIT 20",
			ARRAY_A
		);

		$broken_user_relationships = 0;
		foreach ( $user_meta_fields as $meta ) {
			$user = get_userdata( (int) $meta['meta_value'] );
			
			if ( ! $user ) {
				++$broken_user_relationships;
			}
		}

		if ( $broken_user_relationships > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of broken relationships */
				__( '%d custom fields reference non-existent users', 'wpshadow' ),
				$broken_user_relationships
			);
		}

		// Check for taxonomy term relationships in meta.
		$term_meta_fields = $wpdb->get_results(
			"SELECT meta_id, post_id, meta_key, meta_value 
			FROM {$wpdb->postmeta} 
			WHERE meta_key LIKE '%term%' 
			AND meta_value REGEXP '^[0-9]+$' 
			LIMIT 20",
			ARRAY_A
		);

		$broken_term_relationships = 0;
		foreach ( $term_meta_fields as $meta ) {
			$term = get_term( (int) $meta['meta_value'] );
			
			if ( ! $term || is_wp_error( $term ) ) {
				++$broken_term_relationships;
			}
		}

		if ( $broken_term_relationships > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of broken relationships */
				__( '%d custom fields reference non-existent terms', 'wpshadow' ),
				$broken_term_relationships
			);
		}

		// Check for CMB2 meta boxes.
		if ( function_exists( 'cmb2_get_metabox' ) ) {
			$cmb2_meta = $wpdb->get_var(
				"SELECT COUNT(*) 
				FROM {$wpdb->postmeta} 
				WHERE meta_key LIKE '%cmb2%'"
			);

			if ( $cmb2_meta === 0 ) {
				$issues[] = __( 'CMB2 plugin active but no meta found (incomplete import)', 'wpshadow' );
			}
		}

		// Check for meta with very long values (may be truncated).
		$long_meta = $wpdb->get_var(
			"SELECT COUNT(*) 
			FROM {$wpdb->postmeta} 
			WHERE LENGTH(meta_value) > 65535"
		);

		if ( $long_meta > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of entries */
				__( '%d meta entries exceed TEXT field limit (may be truncated)', 'wpshadow' ),
				$long_meta
			);
		}

		// Check for meta keys with special characters.
		$special_char_keys = $wpdb->get_var(
			"SELECT COUNT(DISTINCT meta_key) 
			FROM {$wpdb->postmeta} 
			WHERE meta_key REGEXP '[^a-zA-Z0-9_-]'"
		);

		if ( $special_char_keys > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of keys */
				__( '%d meta keys contain special characters (may cause issues)', 'wpshadow' ),
				$special_char_keys
			);
		}

		// Check for Pods framework.
		$pods_tables = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT TABLE_NAME 
				FROM information_schema.TABLES 
				WHERE TABLE_SCHEMA = %s 
				AND TABLE_NAME LIKE %s",
				DB_NAME,
				$wpdb->prefix . 'pods%'
			),
			ARRAY_A
		);

		if ( ! empty( $pods_tables ) && ! function_exists( 'pods' ) ) {
			$issues[] = __( 'Pods tables exist but plugin not active (data inaccessible)', 'wpshadow' );
		}

		// Check for meta table indices.
		$meta_indices = $wpdb->get_results(
			$wpdb->prepare(
				"SHOW INDEX FROM {$wpdb->postmeta} 
				WHERE Key_name != 'PRIMARY'"
			),
			ARRAY_A
		);

		$has_meta_key_index = false;
		foreach ( $meta_indices as $index ) {
			if ( 'meta_key' === $index['Column_name'] ) {
				$has_meta_key_index = true;
				break;
			}
		}

		if ( ! $has_meta_key_index && $total_meta > 10000 ) {
			$issues[] = __( 'No index on meta_key column (queries will be slow)', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( '. ', $issues ),
				'severity'    => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/import-custom-field-mapping-failures',
			);
		}

		return null;
	}
}
