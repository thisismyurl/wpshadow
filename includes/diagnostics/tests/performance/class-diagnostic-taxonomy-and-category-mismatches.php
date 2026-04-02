<?php
/**
 * Taxonomy and Category Mismatches Diagnostic
 *
 * Tests whether categories and taxonomies import correctly.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Taxonomy and Category Mismatches Diagnostic Class
 *
 * Tests whether categories, tags, and custom taxonomies import correctly.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Taxonomy_And_Category_Mismatches extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'taxonomy-and-category-mismatches';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Taxonomy and Category Mismatches';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether categories and taxonomies import correctly';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check for posts without categories.
		$posts_without_cat = get_posts( array(
			'post_type'      => 'post',
			'posts_per_page' => -1,
			'orderby'        => 'modified',
		) );

		if ( ! empty( $posts_without_cat ) ) {
			$uncategorized = 0;
			foreach ( $posts_without_cat as $post ) {
				$cats = get_the_category( $post->ID );
				if ( empty( $cats ) || ( count( $cats ) === 1 && $cats[0]->slug === 'uncategorized' ) ) {
					$uncategorized++;
				}
			}

			if ( $uncategorized > count( $posts_without_cat ) * 0.5 ) {
				$issues[] = sprintf(
					/* translators: %d: percentage of posts in uncategorized */
					__( '%d%% of posts are in Uncategorized category', 'wpshadow' ),
					round( ( $uncategorized / count( $posts_without_cat ) ) * 100 )
				);
			}
		}

		// Check for orphaned terms (terms with no posts).
		$all_taxonomies = get_taxonomies( array( 'public' => true ) );

		if ( ! empty( $all_taxonomies ) ) {
			$orphaned_terms = get_terms( array(
				'taxonomy'  => $all_taxonomies,
				'hide_empty' => false,
				'number'    => -1,
			) );

			if ( ! empty( $orphaned_terms ) && ! is_wp_error( $orphaned_terms ) ) {
				$empty_count = 0;
				foreach ( $orphaned_terms as $term ) {
					if ( $term->count === 0 ) {
						$empty_count++;
					}
				}

				if ( $empty_count > count( $orphaned_terms ) * 0.3 ) {
					$issues[] = sprintf(
						/* translators: %d: number of orphaned terms */
						__( '%d orphaned taxonomy terms with no posts', 'wpshadow' ),
						$empty_count
					);
				}
			}
		}

		// Check for custom taxonomy registration issues.
		$custom_taxonomies = get_taxonomies( array( '_builtin' => false ) );

		if ( ! empty( $custom_taxonomies ) ) {
			foreach ( $custom_taxonomies as $tax ) {
				$terms = get_terms( array( 'taxonomy' => $tax, 'hide_empty' => false ) );
				if ( is_wp_error( $terms ) ) {
					$issues[] = sprintf(
						/* translators: %s: taxonomy name */
						__( 'Error retrieving terms for taxonomy: %s', 'wpshadow' ),
						$tax
					);
				}
			}
		}

		// Check for term hierarchy issues.
		$hierarchical_taxonomy = 'category';
		$root_terms = get_terms( array(
			'taxonomy'   => $hierarchical_taxonomy,
			'parent'     => 0,
			'hide_empty' => false,
		) );

		if ( ! empty( $root_terms ) && ! is_wp_error( $root_terms ) ) {
			$hierarchy_issues = 0;
			foreach ( $root_terms as $term ) {
				$children = get_terms( array(
					'taxonomy'   => $hierarchical_taxonomy,
					'parent'     => $term->term_id,
					'hide_empty' => false,
				) );

				if ( is_wp_error( $children ) ) {
					$hierarchy_issues++;
				}
			}

			if ( $hierarchy_issues > 0 ) {
				$issues[] = __( 'Issues retrieving taxonomy hierarchy/parent-child relationships', 'wpshadow' );
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/taxonomy-and-category-mismatches',
			);
		}

		return null;
	}
}
