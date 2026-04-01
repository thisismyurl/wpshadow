<?php
/**
 * Custom Taxonomy Valid Diagnostic
 *
 * Checks if custom taxonomies are properly configured and registered.
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
 * Custom Taxonomy Valid Diagnostic Class
 *
 * Verifies that custom taxonomies are properly configured and registered
 * without conflicts or configuration errors.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Custom_Taxonomy_Valid extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'custom-taxonomy-valid';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Custom Taxonomy Valid';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if custom taxonomies are properly configured and registered';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'developer';

	/**
	 * Run the custom taxonomy valid diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if taxonomy issues detected, null otherwise.
	 */
	public static function check() {
		$issues     = array();
		$warnings   = array();
		$taxonomies = array();

		// Get all registered taxonomies.
		$all_taxonomies = get_taxonomies( array( '_builtin' => false ), 'objects' );

		if ( empty( $all_taxonomies ) ) {
			return null; // No custom taxonomies registered.
		}

		// Check each custom taxonomy.
		foreach ( $all_taxonomies as $taxonomy ) {
			$tax_name     = $taxonomy->name;
			$taxonomies[] = $tax_name;

			// Check for reserved taxonomy names.
			$reserved_names = array(
				'category',
				'post_tag',
				'post_format',
				'nav_menu',
				'link_category',
				'post_type',
			);

			if ( in_array( $tax_name, $reserved_names, true ) ) {
				$issues[] = sprintf(
					/* translators: %s: taxonomy name */
					__( 'Custom taxonomy uses reserved name: %s', 'wpshadow' ),
					$tax_name
				);
			}

			// Check for missing labels.
			if ( empty( $taxonomy->labels ) || ! isset( $taxonomy->labels->name ) ) {
				$warnings[] = sprintf(
					/* translators: %s: taxonomy name */
					__( 'Custom taxonomy "%s" missing labels', 'wpshadow' ),
					$tax_name
				);
			}

			// Check for public visibility.
			if ( false === $taxonomy->public && false === $taxonomy->show_ui ) {
				$warnings[] = sprintf(
					/* translators: %s: taxonomy name */
					__( 'Custom taxonomy "%s" is not public and hidden from UI', 'wpshadow' ),
					$tax_name
				);
			}

			// Check for associated post types.
			if ( empty( $taxonomy->object_type ) ) {
				$issues[] = sprintf(
					/* translators: %s: taxonomy name */
					__( 'Custom taxonomy "%s" not associated with any post type', 'wpshadow' ),
					$tax_name
				);
			}

			// Check for rewrite rules.
			if ( empty( $taxonomy->rewrite ) && true === $taxonomy->public ) {
				$warnings[] = sprintf(
					/* translators: %s: taxonomy name */
					__( 'Public custom taxonomy "%s" missing rewrite rules', 'wpshadow' ),
					$tax_name
				);
			}

			// Check for hierarchical vs flat.
			$is_hierarchical = $taxonomy->hierarchical ?? false;

			// Warn if hierarchical but no terms.
			if ( $is_hierarchical ) {
				$term_count = wp_count_terms( $tax_name );
				if ( 0 === $term_count ) {
					$warnings[] = sprintf(
						/* translators: %s: taxonomy name */
						__( 'Hierarchical taxonomy "%s" has no terms', 'wpshadow' ),
						$tax_name
					);
				}
			}

			// Check term count.
			$term_count = wp_count_terms( $tax_name );
			if ( $term_count > 5000 ) {
				$warnings[] = sprintf(
					/* translators: 1: taxonomy name, 2: term count */
					__( 'Custom taxonomy "%1$s" has many terms (%2$d) - performance may suffer', 'wpshadow' ),
					$tax_name,
					$term_count
				);
			}

			// Check capabilities.
			if ( empty( $taxonomy->cap ) ||
				( 'edit_posts' === $taxonomy->cap->edit_terms && 'manage_posts' === $taxonomy->cap->manage_terms ) ) {
				$warnings[] = sprintf(
					/* translators: %s: taxonomy name */
					__( 'Custom taxonomy "%s" using default capabilities', 'wpshadow' ),
					$tax_name
				);
			}

			// Check REST API support.
			if ( true === $taxonomy->publicly_queryable && false === $taxonomy->rest_base ) {
				$warnings[] = sprintf(
					/* translators: %s: taxonomy name */
					__( 'Public taxonomy "%s" not exposed to REST API', 'wpshadow' ),
					$tax_name
				);
			}

			// Check for slug conflicts.
			if ( strlen( $tax_name ) > 32 ) {
				$warnings[] = sprintf(
					/* translators: 1: taxonomy name, 2: length */
					__( 'Taxonomy name too long (%1$s - %2$d chars), recommend < 32', 'wpshadow' ),
					$tax_name,
					strlen( $tax_name )
				);
			}
		}

		// Check for taxonomy/post type conflicts.
		$post_types      = get_post_types( array( '_builtin' => false ), 'objects' );
		$post_type_names = array_keys( $post_types );
		$conflict_names  = array_intersect( array_keys( $all_taxonomies ), $post_type_names );

		if ( ! empty( $conflict_names ) ) {
			foreach ( $conflict_names as $conflict ) {
				$issues[] = sprintf(
					/* translators: %s: name */
					__( 'Post type and taxonomy name conflict: %s', 'wpshadow' ),
					$conflict
				);
			}
		}

		// Check for too many custom taxonomies.
		if ( count( $all_taxonomies ) > 15 ) {
			$warnings[] = sprintf(
				/* translators: %d: count */
				__( 'High number of custom taxonomies (%d) - consider consolidation', 'wpshadow' ),
				count( $all_taxonomies )
			);
		}

		// Check for orphaned terms.
		foreach ( $all_taxonomies as $taxonomy ) {
			$orphaned = get_terms(
				array(
					'taxonomy'   => $taxonomy->name,
					'object_ids' => array(),
					'hide_empty' => false,
				)
			);

			if ( ! empty( $orphaned ) && count( $orphaned ) > 100 ) {
				$warnings[] = sprintf(
					/* translators: 1: taxonomy name, 2: term count */
					__( 'Taxonomy "%1$s" has many unused terms (%2$d) - consider cleanup', 'wpshadow' ),
					$taxonomy->name,
					count( $orphaned )
				);
			}
		}

		// If critical issues found.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Custom taxonomies have critical issues: ', 'wpshadow' ) . implode( ', ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/custom-taxonomy-valid?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'      => array(
					'taxonomies' => $taxonomies,
					'tax_count'  => count( $all_taxonomies ),
					'issues'     => $issues,
					'warnings'   => $warnings,
				),
			);
		}

		// If only warnings.
		if ( ! empty( $warnings ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Custom taxonomies have recommendations: ', 'wpshadow' ) . implode( ', ', $warnings ),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/custom-taxonomy-valid?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'      => array(
					'taxonomies' => $taxonomies,
					'tax_count'  => count( $all_taxonomies ),
					'warnings'   => $warnings,
				),
			);
		}

		return null; // Custom taxonomies are properly configured.
	}
}
