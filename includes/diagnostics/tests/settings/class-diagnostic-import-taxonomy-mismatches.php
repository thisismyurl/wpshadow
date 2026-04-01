<?php
/**
 * Import Taxonomy and Category Mismatches Diagnostic
 *
 * Tests whether categories, tags, and custom taxonomies import correctly with
 * proper parent/child relationships.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Import Taxonomy Mismatches Diagnostic Class
 *
 * Checks for taxonomy import issues.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Import_Taxonomy_Mismatches extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'import-taxonomy-mismatches';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Import Taxonomy and Category Mismatches';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests taxonomy import accuracy and parent/child relationships';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'import-export';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues = array();

		// Check for orphaned term relationships (post doesn't exist).
		$orphaned_relationships = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->term_relationships} tr
			LEFT JOIN {$wpdb->posts} p ON tr.object_id = p.ID
			WHERE p.ID IS NULL"
		);

		if ( $orphaned_relationships > 20 ) {
			$issues[] = sprintf(
				/* translators: %d: number of orphaned relationships */
				__( '%d orphaned term relationships (import cleanup needed)', 'wpshadow' ),
				$orphaned_relationships
			);
		}

		// Check for terms without taxonomy.
		$orphaned_terms = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->terms} t
			LEFT JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id
			WHERE tt.term_id IS NULL"
		);

		if ( $orphaned_terms > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of orphaned terms */
				__( '%d terms without taxonomy assignment (import error)', 'wpshadow' ),
				$orphaned_terms
			);
		}

		// Check for broken parent relationships in categories.
		$categories_with_parents = $wpdb->get_results(
			"SELECT term_id, parent
			FROM {$wpdb->term_taxonomy}
			WHERE taxonomy = 'category'
			AND parent > 0",
			ARRAY_A
		);

		$broken_parents = 0;
		foreach ( $categories_with_parents as $cat ) {
			$parent_exists = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT term_id
					FROM {$wpdb->term_taxonomy}
					WHERE term_id = %d
					AND taxonomy = 'category'",
					$cat['parent']
				)
			);

			if ( ! $parent_exists ) {
				++$broken_parents;
			}
		}

		if ( $broken_parents > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of broken relationships */
				__( '%d categories reference non-existent parents', 'wpshadow' ),
				$broken_parents
			);
		}

		// Check for circular parent relationships.
		$circular_refs = 0;
		foreach ( array_slice( $categories_with_parents, 0, 50 ) as $cat ) {
			$visited = array( $cat['term_id'] );
			$current_parent = $cat['parent'];
			$depth = 0;

			while ( $current_parent > 0 && $depth < 20 ) {
				if ( in_array( $current_parent, $visited, true ) ) {
					++$circular_refs;
					break;
				}

				$visited[] = $current_parent;

				$next_parent = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT parent
						FROM {$wpdb->term_taxonomy}
						WHERE term_id = %d",
						$current_parent
					)
				);

				$current_parent = (int) $next_parent;
				++$depth;
			}
		}

		if ( $circular_refs > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of circular references */
				__( '%d categories have circular parent references', 'wpshadow' ),
				$circular_refs
			);
		}

		// Check for duplicate term slugs in same taxonomy.
		$duplicate_slugs = $wpdb->get_results(
			"SELECT t.slug, tt.taxonomy, COUNT(*) as count
			FROM {$wpdb->terms} t
			INNER JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id
			GROUP BY t.slug, tt.taxonomy
			HAVING count > 1",
			ARRAY_A
		);

		if ( ! empty( $duplicate_slugs ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of duplicates */
				__( '%d duplicate term slugs found (import duplication)', 'wpshadow' ),
				count( $duplicate_slugs )
			);
		}

		// Check for posts with no categories (if default category not set).
		$default_category = get_option( 'default_category' );

		$posts_no_categories = $wpdb->get_var(
			"SELECT COUNT(DISTINCT p.ID)
			FROM {$wpdb->posts} p
			LEFT JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
			LEFT JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
			WHERE p.post_type = 'post'
			AND p.post_status = 'publish'
			AND (tt.taxonomy != 'category' OR tt.taxonomy IS NULL)"
		);

		if ( $posts_no_categories > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts */
				__( '%d posts have no categories (import issue or default category unset)', 'wpshadow' ),
				$posts_no_categories
			);
		}

		// Check for custom taxonomies.
		$custom_taxonomies = get_taxonomies( array( '_builtin' => false ), 'names' );

		foreach ( $custom_taxonomies as $taxonomy ) {
			$tax_object = get_taxonomy( $taxonomy );

			if ( ! $tax_object ) {
				continue;
			}

			// Check if taxonomy has terms.
			$term_count = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*)
					FROM {$wpdb->term_taxonomy}
					WHERE taxonomy = %s",
					$taxonomy
				)
			);

			// Check if taxonomy is used on posts.
			$usage_count = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(DISTINCT tr.object_id)
					FROM {$wpdb->term_relationships} tr
					INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
					WHERE tt.taxonomy = %s",
					$taxonomy
				)
			);

			if ( $term_count > 0 && $usage_count === 0 ) {
				$issues[] = sprintf(
					/* translators: 1: taxonomy name, 2: term count */
					__( 'Custom taxonomy "%1$s" has %2$d terms but not assigned to any posts', 'wpshadow' ),
					$taxonomy,
					$term_count
				);
			}
		}

		// Check for term meta (requires WP 4.4+).
		$term_meta_count = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->termmeta}"
		);

		if ( $term_meta_count > 0 ) {
			// Check for orphaned term meta.
			$orphaned_term_meta = $wpdb->get_var(
				"SELECT COUNT(*)
				FROM {$wpdb->termmeta} tm
				LEFT JOIN {$wpdb->terms} t ON tm.term_id = t.term_id
				WHERE t.term_id IS NULL"
			);

			if ( $orphaned_term_meta > 0 ) {
				$issues[] = sprintf(
					/* translators: %d: number of orphaned entries */
					__( '%d orphaned term meta entries (import cleanup needed)', 'wpshadow' ),
					$orphaned_term_meta
				);
			}
		}

		// Check for terms with zero count but have relationships.
		$zero_count_terms = $wpdb->get_results(
			"SELECT tt.term_id, tt.taxonomy, tt.count
			FROM {$wpdb->term_taxonomy} tt
			INNER JOIN {$wpdb->term_relationships} tr ON tt.term_taxonomy_id = tr.term_taxonomy_id
			WHERE tt.count = 0
			GROUP BY tt.term_id, tt.taxonomy
			LIMIT 20",
			ARRAY_A
		);

		if ( ! empty( $zero_count_terms ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of terms */
				__( '%d terms have zero count but have relationships (needs recount)', 'wpshadow' ),
				count( $zero_count_terms )
			);
		}

		// Check for taxonomy registration timing.
		foreach ( $custom_taxonomies as $taxonomy ) {
			if ( ! taxonomy_exists( $taxonomy ) ) {
				$term_count = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT COUNT(*)
						FROM {$wpdb->term_taxonomy}
						WHERE taxonomy = %s",
						$taxonomy
					)
				);

				if ( $term_count > 0 ) {
					$issues[] = sprintf(
						/* translators: 1: taxonomy name, 2: term count */
						__( 'Taxonomy "%1$s" not registered but has %2$d terms (plugin inactive)', 'wpshadow' ),
						$taxonomy,
						$term_count
					);
				}
			}
		}

		// Check for empty term names.
		$empty_term_names = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->terms}
			WHERE name = ''"
		);

		if ( $empty_term_names > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of terms */
				__( '%d terms have empty names (import error)', 'wpshadow' ),
				$empty_term_names
			);
		}

		// Check for term descriptions.
		$terms_with_descriptions = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->term_taxonomy}
			WHERE description != ''"
		);

		$all_terms = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->term_taxonomy}"
		);

		// Check for hierarchical taxonomy with deep nesting.
		$max_depth = 0;
		foreach ( array_slice( $categories_with_parents, 0, 20 ) as $cat ) {
			$depth = 0;
			$current = $cat['parent'];

			while ( $current > 0 && $depth < 20 ) {
				++$depth;

				$current = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT parent
						FROM {$wpdb->term_taxonomy}
						WHERE term_id = %d",
						$current
					)
				);

				$current = (int) $current;
			}

			if ( $depth > $max_depth ) {
				$max_depth = $depth;
			}
		}

		if ( $max_depth > 5 ) {
			$issues[] = sprintf(
				/* translators: %d: nesting depth */
				__( 'Categories nested %d levels deep (consider flattening)', 'wpshadow' ),
				$max_depth
			);
		}

		// Check for duplicate term names in same taxonomy.
		$duplicate_names = $wpdb->get_results(
			"SELECT t.name, tt.taxonomy, COUNT(*) as count
			FROM {$wpdb->terms} t
			INNER JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id
			GROUP BY t.name, tt.taxonomy
			HAVING count > 1
			LIMIT 10",
			ARRAY_A
		);

		if ( ! empty( $duplicate_names ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of duplicates */
				__( '%d duplicate term names in same taxonomy', 'wpshadow' ),
				count( $duplicate_names )
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( '. ', $issues ),
				'severity'    => 'medium',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/import-taxonomy-mismatches?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
