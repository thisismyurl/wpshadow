<?php
/**
 * Cross-Post Type References Diagnostic
 *
 * Validates references between different post types. Tests relationship plugin
 * compatibility and detects broken cross-type connections.
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
 * Cross-Post Type References Diagnostic Class
 *
 * Checks for issues in cross-post-type relationships.
 *
 * @since 1.6030.2148
 */
class Diagnostic_Cross_Post_Type_References extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'cross-post-type-references';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Cross-Post Type References';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates references between different post types for data integrity';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'posts';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues = array();

		// Get all public post types.
		$post_types = get_post_types( array( 'public' => true ), 'names' );
		
		if ( count( $post_types ) <= 1 ) {
			return null; // Need at least 2 post types for cross-type references.
		}

		// Check for meta fields that reference post IDs across types.
		$cross_reference_keys = $wpdb->get_results(
			"SELECT DISTINCT meta_key, COUNT(*) as usage
			FROM {$wpdb->postmeta}
			WHERE meta_key LIKE '%post%'
			OR meta_key LIKE '%ref%'
			OR meta_key LIKE '%id%'
			OR meta_key LIKE '%link%'
			GROUP BY meta_key
			HAVING usage > 10
			ORDER BY usage DESC
			LIMIT 30",
			ARRAY_A
		);

		$has_cross_references = false;

		foreach ( $cross_reference_keys as $key_data ) {
			$key = $key_data['meta_key'];

			// Sample values to detect numeric post ID references.
			$sample_values = $wpdb->get_col(
				$wpdb->prepare(
					"SELECT DISTINCT meta_value
					FROM {$wpdb->postmeta}
					WHERE meta_key = %s
					AND meta_value REGEXP '^[0-9]+$'
					LIMIT 50",
					$key
				)
			);

			if ( empty( $sample_values ) ) {
				continue;
			}

			$has_cross_references = true;

			// Check if these IDs reference posts of different types.
			$broken_references = 0;
			$type_mismatches = 0;
			$post_types_found = array();

			foreach ( $sample_values as $post_id ) {
				$referenced_post = $wpdb->get_row(
					$wpdb->prepare(
						"SELECT ID, post_type, post_status FROM {$wpdb->posts} WHERE ID = %d",
						(int) $post_id
					)
				);

				if ( ! $referenced_post ) {
					++$broken_references;
					continue;
				}

				$post_types_found[] = $referenced_post->post_type;

				// Check if the meta key is being used across different post types.
				$source_post_types = $wpdb->get_col(
					$wpdb->prepare(
						"SELECT DISTINCT p.post_type
						FROM {$wpdb->postmeta} pm
						INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
						WHERE pm.meta_key = %s
						AND pm.meta_value = %s
						LIMIT 5",
						$key,
						(string) $post_id
					)
				);

				if ( count( array_unique( $source_post_types ) ) > 1 ) {
					++$type_mismatches;
				}
			}

			// Report broken references.
			if ( $broken_references > 5 ) {
				$issues[] = sprintf(
					/* translators: 1: meta key, 2: number of broken references */
					__( 'Meta key "%1$s" has %2$d broken post references across types', 'wpshadow' ),
					esc_html( $key ),
					$broken_references
				);
				break; // Only report once per scan.
			}

			// Report type mismatches if significant.
			if ( $type_mismatches > 3 && count( array_unique( $post_types_found ) ) > 1 ) {
				$issues[] = sprintf(
					/* translators: 1: meta key, 2: number of post types */
					__( 'Meta key "%1$s" references %2$d different post types (potential compatibility issue)', 'wpshadow' ),
					esc_html( $key ),
					count( array_unique( $post_types_found ) )
				);
				break;
			}
		}

		// If no cross-references detected, return early.
		if ( ! $has_cross_references && empty( $issues ) ) {
			return null;
		}

		// Check for taxonomy terms assigned to wrong post types.
		$taxonomies = get_taxonomies( array( 'public' => true ), 'objects' );
		
		foreach ( $taxonomies as $taxonomy ) {
			$allowed_types = $taxonomy->object_type;
			
			if ( empty( $allowed_types ) || count( $allowed_types ) >= count( $post_types ) ) {
				continue; // Skip universal taxonomies.
			}

			$type_placeholders = implode( ', ', array_fill( 0, count( $allowed_types ), '%s' ) );

			// Check for posts with this taxonomy but wrong post type.
			$wrong_type_terms = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(DISTINCT tr.object_id)
					FROM {$wpdb->term_relationships} tr
					INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
					INNER JOIN {$wpdb->posts} p ON tr.object_id = p.ID
					WHERE tt.taxonomy = %s
					AND p.post_type NOT IN ({$type_placeholders})
					AND p.post_status NOT IN ('trash', 'auto-draft')",
					$taxonomy->name,
					...$allowed_types
				)
			);

			if ( $wrong_type_terms > 0 ) {
				$issues[] = sprintf(
					/* translators: 1: taxonomy name, 2: number of wrong-type posts */
					__( 'Taxonomy "%1$s" assigned to %2$d posts of wrong type', 'wpshadow' ),
					esc_html( $taxonomy->label ),
					$wrong_type_terms
				);
			}
		}

		// Check for ACF field groups assigned to multiple post types.
		$acf_field_groups = $wpdb->get_results(
			"SELECT ID, post_title, post_content
			FROM {$wpdb->posts}
			WHERE post_type = 'acf-field-group'
			AND post_status = 'publish'
			LIMIT 50",
			ARRAY_A
		);

		foreach ( $acf_field_groups as $field_group ) {
			$location_rules = get_post_meta( $field_group['ID'], 'rule', true );
			
			if ( empty( $location_rules ) ) {
				continue;
			}

			// Check if location rules reference multiple post types.
			$field_post_types = array();
			if ( is_array( $location_rules ) ) {
				foreach ( $location_rules as $rule_group ) {
					if ( is_array( $rule_group ) ) {
						foreach ( $rule_group as $rule ) {
							if ( isset( $rule['param'] ) && 'post_type' === $rule['param'] && isset( $rule['value'] ) ) {
								$field_post_types[] = $rule['value'];
							}
						}
					}
				}
			}

			if ( count( array_unique( $field_post_types ) ) > 3 ) {
				$issues[] = sprintf(
					/* translators: 1: field group name, 2: number of post types */
					__( 'ACF field group "%1$s" assigned to %2$d post types (may impact performance)', 'wpshadow' ),
					esc_html( $field_group['post_title'] ),
					count( array_unique( $field_post_types ) )
				);
				break;
			}
		}

		// Check for post type switching (posts that changed type).
		$type_switchers = $wpdb->get_results(
			"SELECT p.ID, p.post_type, pm.meta_value as original_type
			FROM {$wpdb->posts} p
			INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
			WHERE pm.meta_key = '_original_post_type'
			AND pm.meta_value != p.post_type
			AND p.post_status NOT IN ('trash', 'auto-draft')
			LIMIT 20",
			ARRAY_A
		);

		if ( ! empty( $type_switchers ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts that changed type */
				__( '%d posts changed post type (metadata may be incompatible)', 'wpshadow' ),
				count( $type_switchers )
			);
		}

		// Check for custom field compatibility issues between post types.
		$field_usage_by_type = $wpdb->get_results(
			"SELECT p.post_type, pm.meta_key, COUNT(*) as usage
			FROM {$wpdb->postmeta} pm
			INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
			WHERE p.post_status NOT IN ('trash', 'auto-draft')
			AND pm.meta_key NOT LIKE '\\_%'
			GROUP BY p.post_type, pm.meta_key
			HAVING usage > 10
			ORDER BY pm.meta_key, usage DESC
			LIMIT 200",
			ARRAY_A
		);

		// Group by meta key.
		$field_by_key = array();
		foreach ( $field_usage_by_type as $row ) {
			$key = $row['meta_key'];
			if ( ! isset( $field_by_key[ $key ] ) ) {
				$field_by_key[ $key ] = array();
			}
			$field_by_key[ $key ][] = $row['post_type'];
		}

		// Check for fields shared across many post types.
		$excessive_sharing = 0;
		foreach ( $field_by_key as $key => $types ) {
			if ( count( $types ) > 4 ) {
				++$excessive_sharing;
			}
		}

		if ( $excessive_sharing > 5 ) {
			$issues[] = sprintf(
				/* translators: %d: number of fields shared across types */
				__( '%d custom fields shared across 5+ post types (data inconsistency risk)', 'wpshadow' ),
				$excessive_sharing
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( '. ', $issues ),
				'severity'    => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/cross-post-type-references',
			);
		}

		return null;
	}
}
