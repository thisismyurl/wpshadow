<?php
/**
 * CPT Taxonomy Association Diagnostic
 *
 * Validates taxonomies are properly associated with custom post types.
 * Tests taxonomy registration and relationship configurations.
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
 * CPT Taxonomy Association Class
 *
 * Verifies custom post types have proper taxonomy associations and
 * detects broken or missing taxonomy relationships.
 *
 * @since 0.6093.1200
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
	protected static $description = 'Validates taxonomies properly associated with CPTs';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content';

	/**
	 * Run the diagnostic check.
	 *
	 * Validates taxonomy associations for custom post types and detects
	 * missing or broken relationships.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if taxonomy issues found, null otherwise.
	 */
	public static function check() {
		global $wp_taxonomies;

		$issues = array();
		$problematic_cpts = array();

		// Get all custom post types.
		$post_types = get_post_types(
			array(
				'public'   => true,
				'_builtin' => false,
			),
			'objects'
		);

		if ( empty( $post_types ) ) {
			return null;
		}

		foreach ( $post_types as $post_type => $post_type_obj ) {
			$cpt_issues = array();

			// Get taxonomies registered for this CPT.
			$taxonomies = get_object_taxonomies( $post_type, 'objects' );

			// Check if CPT has any taxonomies.
			if ( empty( $taxonomies ) ) {
				// Check if posts exist without taxonomy.
				$post_count = wp_count_posts( $post_type );
				$total = isset( $post_count->publish ) ? $post_count->publish : 0;

				if ( $total > 10 ) {
					$cpt_issues[] = sprintf(
						/* translators: %d: number of posts */
						_n(
							'Has %d post but no taxonomies for organization',
							'Has %d posts but no taxonomies for organization',
							$total,
							'wpshadow'
						),
						number_format_i18n( $total )
					);
				}
			} else {
				// Check each taxonomy association.
				foreach ( $taxonomies as $tax_name => $taxonomy ) {
					// Verify bidirectional relationship.
					if ( ! in_array( $post_type, $taxonomy->object_type, true ) ) {
						$cpt_issues[] = sprintf(
							/* translators: %s: taxonomy name */
							__( 'Taxonomy "%s" association is broken', 'wpshadow' ),
							$taxonomy->label
						);
					}

					// Check if taxonomy has terms but no posts using them.
					$term_count = wp_count_terms(
						array(
							'taxonomy'   => $tax_name,
							'hide_empty' => false,
						)
					);

					if ( $term_count > 0 ) {
						$terms_with_posts = wp_count_terms(
							array(
								'taxonomy'   => $tax_name,
								'hide_empty' => true,
							)
						);

						if ( $terms_with_posts === 0 ) {
							$cpt_issues[] = sprintf(
								/* translators: 1: taxonomy label, 2: number of terms */
								__( 'Taxonomy "%1$s" has %2$d terms but none are used', 'wpshadow' ),
								$taxonomy->label,
								number_format_i18n( $term_count )
							);
						}
					}
				}
			}

			// Check for orphaned term relationships.
			global $wpdb;

			$orphaned = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(DISTINCT tr.object_id)
					FROM {$wpdb->term_relationships} tr
					LEFT JOIN {$wpdb->posts} p ON tr.object_id = p.ID
					WHERE p.ID IS NULL
					AND tr.object_id IN (
						SELECT ID FROM {$wpdb->posts} WHERE post_type = %s
					)",
					$post_type
				)
			);

			if ( $orphaned && (int) $orphaned > 0 ) {
				$cpt_issues[] = sprintf(
					/* translators: %d: number of orphaned relationships */
					_n(
						'Found %d orphaned term relationship',
						'Found %d orphaned term relationships',
						(int) $orphaned,
						'wpshadow'
					),
					number_format_i18n( (int) $orphaned )
				);
			}

			if ( ! empty( $cpt_issues ) ) {
				$problematic_cpts[ $post_type ] = array(
					'label'      => $post_type_obj->label,
					'taxonomies' => array_keys( $taxonomies ),
					'issues'     => $cpt_issues,
				);

				$issues[] = sprintf(
					/* translators: 1: post type label, 2: list of issues */
					__( '%1$s: %2$s', 'wpshadow' ),
					$post_type_obj->label,
					implode( ', ', $cpt_issues )
				);
			}
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %d: number of CPTs with issues */
				_n(
					'Found taxonomy issues in %d custom post type: ',
					'Found taxonomy issues in %d custom post types: ',
					count( $problematic_cpts ),
					'wpshadow'
				) . implode( ' ', $issues ),
				number_format_i18n( count( $problematic_cpts ) )
			),
			'severity'    => 'medium',
			'threat_level' => 55,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/cpt-taxonomy-association?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'     => array(
				'problematic_cpts' => $problematic_cpts,
			),
		);
	}
}
