<?php
/**
 * CPT Taxonomy Association Diagnostic
 *
 * Validates taxonomies are properly associated with custom post types. Checks for
 * registration issues, missing associations, and orphaned taxonomy terms.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CPT Taxonomy Association Diagnostic Class
 *
 * Checks for taxonomy association issues with custom post types.
 *
 * @since 1.2601.2148
 */
class Diagnostic_CPT_Taxonomy_Association extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'cpt-taxonomy-association';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'CPT Taxonomy Association';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates taxonomies are properly associated with custom post types';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'cpt';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues = array();

		// Get all registered post types.
		$post_types = get_post_types( array(), 'objects' );

		// Filter to only custom post types.
		$built_in = array( 'post', 'page', 'attachment', 'revision', 'nav_menu_item', 'custom_css', 'customize_changeset', 'oembed_cache', 'user_request', 'wp_block', 'wp_template', 'wp_template_part', 'wp_global_styles', 'wp_navigation' );
		$custom_post_types = array_filter(
			$post_types,
			function ( $pt ) use ( $built_in ) {
				return ! in_array( $pt->name, $built_in, true );
			}
		);

		if ( empty( $custom_post_types ) ) {
			return null;
		}

		// Get all taxonomies.
		$taxonomies = get_taxonomies( array(), 'objects' );

		foreach ( $custom_post_types as $cpt ) {
			// Get taxonomies for this CPT.
			$cpt_taxonomies = get_object_taxonomies( $cpt->name, 'objects' );

			// Check if CPT has no taxonomies (might be intentional but unusual).
			if ( empty( $cpt_taxonomies ) && $cpt->show_ui ) {
				$post_count = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_status = 'publish'",
						$cpt->name
					)
				);

				if ( $post_count > 10 ) {
					$issues[] = sprintf(
						/* translators: 1: post type slug, 2: number of posts */
						__( 'CPT "%1$s" has %2$d posts but no taxonomies (may be hard to organize)', 'wpshadow' ),
						esc_html( $cpt->name ),
						$post_count
					);
				}
			}

			// Check each taxonomy association.
			foreach ( $cpt_taxonomies as $taxonomy ) {
				// Verify taxonomy is registered.
				if ( ! taxonomy_exists( $taxonomy->name ) ) {
					$issues[] = sprintf(
						/* translators: 1: post type slug, 2: taxonomy name */
						__( 'CPT "%1$s" references non-existent taxonomy "%2$s"', 'wpshadow' ),
						esc_html( $cpt->name ),
						esc_html( $taxonomy->name )
					);
					continue;
				}

				// Check if taxonomy is properly registered for this post type.
				$tax_post_types = get_taxonomy( $taxonomy->name )->object_type;
				if ( ! in_array( $cpt->name, $tax_post_types, true ) ) {
					$issues[] = sprintf(
						/* translators: 1: taxonomy name, 2: post type slug */
						__( 'Taxonomy "%1$s" not registered for CPT "%2$s" (association broken)', 'wpshadow' ),
						esc_html( $taxonomy->name ),
						esc_html( $cpt->name )
					);
				}

				// Check for orphaned terms (taxonomy has terms but no posts use them).
				$term_count = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT COUNT(DISTINCT t.term_id)
						FROM {$wpdb->terms} t
						INNER JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id
						WHERE tt.taxonomy = %s
						AND tt.count = 0",
						$taxonomy->name
					)
				);

				if ( $term_count > 10 ) {
					$issues[] = sprintf(
						/* translators: 1: number of terms, 2: taxonomy name, 3: post type slug */
						__( '%1$d unused terms in "%2$s" taxonomy for CPT "%3$s"', 'wpshadow' ),
						$term_count,
						esc_html( $taxonomy->name ),
						esc_html( $cpt->name )
					);
				}

				// Check if taxonomy is hierarchical but show_ui is false.
				if ( $taxonomy->hierarchical && ! $taxonomy->show_ui ) {
					$issues[] = sprintf(
						/* translators: 1: taxonomy name, 2: post type slug */
						__( 'Hierarchical taxonomy "%1$s" for CPT "%2$s" has no UI (can\'t manage terms)', 'wpshadow' ),
						esc_html( $taxonomy->name ),
						esc_html( $cpt->name )
					);
				}

				// Check for taxonomy/CPT capability mismatch.
				if ( ! empty( $taxonomy->cap->manage_terms ) && ! empty( $cpt->cap->edit_posts ) ) {
					// Verify users who can edit CPT can also manage taxonomy.
					$user = wp_get_current_user();
					if ( $user && $user->ID > 0 ) {
						if ( current_user_can( $cpt->cap->edit_posts ) && ! current_user_can( $taxonomy->cap->manage_terms ) ) {
							$issues[] = sprintf(
								/* translators: 1: post type slug, 2: taxonomy name */
								__( 'Current user can edit "%1$s" but cannot manage "%2$s" terms (permission mismatch)', 'wpshadow' ),
								esc_html( $cpt->name ),
								esc_html( $taxonomy->name )
							);
						}
					}
				}
			}
		}

		// Check for custom taxonomies not associated with any post type.
		$built_in_taxonomies = array( 'category', 'post_tag', 'nav_menu', 'link_category', 'post_format' );
		foreach ( $taxonomies as $taxonomy ) {
			if ( in_array( $taxonomy->name, $built_in_taxonomies, true ) ) {
				continue;
			}

			if ( empty( $taxonomy->object_type ) || ( is_array( $taxonomy->object_type ) && count( $taxonomy->object_type ) === 0 ) ) {
				$issues[] = sprintf(
					/* translators: %s: taxonomy name */
					__( 'Custom taxonomy "%s" registered but not associated with any post type', 'wpshadow' ),
					esc_html( $taxonomy->name )
				);
			}
		}

		// Check for term_relationships orphaned entries (terms assigned to non-existent posts).
		$orphaned_relationships = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->term_relationships} tr
			LEFT JOIN {$wpdb->posts} p ON tr.object_id = p.ID
			WHERE p.ID IS NULL"
		);

		if ( $orphaned_relationships > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of orphaned relationships */
				__( '%d orphaned term relationships (terms assigned to deleted posts)', 'wpshadow' ),
				$orphaned_relationships
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( '. ', $issues ),
				'severity'    => 'high',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/cpt-taxonomy-association',
			);
		}

		return null;
	}
}
