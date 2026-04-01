<?php
/**
 * Lost Custom Taxonomies in Export Diagnostic
 *
 * Detects when custom taxonomies (beyond categories/tags) are
 * excluded from WordPress exports.
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
 * Lost Custom Taxonomies in Export Diagnostic Class
 *
 * Detects when custom taxonomies are excluded from WordPress exports.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Lost_Custom_Taxonomies_In_Export extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'lost-custom-taxonomies-in-export';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Lost Custom Taxonomies in Export';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects custom taxonomies excluded from WordPress exports';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'export';

	/**
	 * Run the diagnostic check.
	 *
	 * Verifies that all custom taxonomies are included in
	 * WordPress export files.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		global $wpdb;

		// Built-in taxonomies that are usually included.
		$built_in_taxonomies = array(
			'category',
			'post_tag',
			'link_category',
			'nav_menu',
			'post_format',
		);

		// Get all taxonomies.
		$all_taxonomies = get_taxonomies( array( 'public' => true ), 'objects' );

		$custom_taxonomies = array();
		$non_exportable = array();

		foreach ( $all_taxonomies as $tax ) {
			if ( ! in_array( $tax->name, $built_in_taxonomies, true ) ) {
				$custom_taxonomies[] = $tax;

				// Check export support.
				if ( ! isset( $tax->show_in_nav_menus ) || ! $tax->show_in_nav_menus ) {
					if ( ! isset( $tax->publicly_queryable ) || ! $tax->publicly_queryable ) {
						$non_exportable[] = $tax->name;
					}
				}
			}
		}

		// Count terms in custom taxonomies.
		$taxonomy_term_counts = array();
		$total_custom_terms = 0;

		foreach ( $custom_taxonomies as $tax ) {
			$terms = get_terms( array(
				'taxonomy'   => $tax->name,
				'hide_empty' => false,
			) );

			if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
				$count = count( $terms );
				$taxonomy_term_counts[ $tax->name ] = array(
					'count'  => $count,
					'label'  => $tax->label,
				);
				$total_custom_terms += $count;
			}
		}

		// Check if custom taxonomies have posts assigned.
		$custom_tax_post_relationships = 0;

		foreach ( array_keys( $taxonomy_term_counts ) as $tax_name ) {
			$count = (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(DISTINCT tr.object_id)
					FROM {$wpdb->term_relationships} tr
					INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
					WHERE tt.taxonomy = %s",
					$tax_name
				)
			);

			$custom_tax_post_relationships += $count;
		}

		// Check WXR export configuration.
		$wxr_taxonomies = apply_filters( 'wxr_export_taxonomies', array( 'category', 'post_tag' ) );

		$missing_tax_from_export = array();
		foreach ( $custom_taxonomies as $tax ) {
			if ( ! in_array( $tax->name, $wxr_taxonomies, true ) ) {
				$missing_tax_from_export[] = $tax->name;
			}
		}

		// Check for hierarchical taxonomy structure.
		$hierarchical_info = array();
		foreach ( $custom_taxonomies as $tax ) {
			if ( $tax->hierarchical ) {
				$parent_terms = get_terms( array(
					'taxonomy' => $tax->name,
					'parent'   => 0,
					'hide_empty' => false,
				) );

				if ( ! is_wp_error( $parent_terms ) && ! empty( $parent_terms ) ) {
					$hierarchical_info[] = array(
						'name'   => $tax->name,
						'label'  => $tax->label,
						'parent' => count( $parent_terms ),
					);
				}
			}
		}

		// Check for taxonomy metadata.
		$taxonomy_metadata_count = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT term_id)
				FROM {$wpdb->termmeta}
				WHERE term_id IN (
					SELECT t.term_id
					FROM {$wpdb->terms} t
					INNER JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id
					WHERE tt.taxonomy NOT IN (%s, %s)
				)",
				'category',
				'post_tag'
			)
		);

		if ( ! empty( $custom_taxonomies ) && ( $total_custom_terms > 0 || $custom_tax_post_relationships > 0 ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of custom taxonomies, %d: number of terms */
					__( '%d custom taxonomies with %d terms are not included in WordPress exports', 'wpshadow' ),
					count( $custom_taxonomies ),
					$total_custom_terms
				),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/lost-custom-taxonomies-in-export?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'custom_taxonomies_found'          => count( $custom_taxonomies ),
					'custom_taxonomies_list'           => array_column( $custom_taxonomies, 'label' ),
					'total_custom_terms'               => $total_custom_terms,
					'taxonomy_term_counts'             => $taxonomy_term_counts,
					'custom_taxonomy_post_assignments' => $custom_tax_post_relationships,
					'non_exportable_types'             => $non_exportable,
					'missing_from_wxr_export'          => $missing_tax_from_export,
					'current_wxr_taxonomies'           => $wxr_taxonomies,
					'hierarchical_taxonomies'          => $hierarchical_info,
					'taxonomy_metadata_count'          => $taxonomy_metadata_count,
					'organization_impact'              => sprintf(
						/* translators: %d: number of terms */
						__( 'Site organization with %d taxonomy terms will be lost in backup/export', 'wpshadow' ),
						$total_custom_terms
					),
					'navigation_impact'                => __( 'Custom taxonomy-based navigation will break after restore', 'wpshadow' ),
					'content_discovery'                => __( 'Users will not be able to find content by custom taxonomies', 'wpshadow' ),
					'fix_methods'                      => array(
						__( 'Use export plugin with custom taxonomy support', 'wpshadow' ),
						__( 'Register custom taxonomies with public=true', 'wpshadow' ),
						__( 'Add filter to wxr_export_taxonomies hook', 'wpshadow' ),
						__( 'Ensure taxonomies exportable before export', 'wpshadow' ),
						__( 'Use admin export with all taxonomy types selected', 'wpshadow' ),
					),
					'verification'                     => array(
						__( 'Download and inspect WXR export file', 'wpshadow' ),
						__( 'Search XML for custom taxonomy terms', 'wpshadow' ),
						__( 'Count term entries vs actual count', 'wpshadow' ),
						__( 'Test import on staging site', 'wpshadow' ),
						__( 'Verify taxonomy relationships intact', 'wpshadow' ),
					),
					'critical_note'                    => __( 'Lost taxonomies destroy site organization and navigation - backup exports must include all taxonomies', 'wpshadow' ),
				),
			);
		}

		return null;
	}
}
