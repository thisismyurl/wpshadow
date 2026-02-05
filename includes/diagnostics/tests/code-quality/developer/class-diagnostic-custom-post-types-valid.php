<?php
/**
 * Custom Post Types Valid Diagnostic
 *
 * Checks if custom post types are properly registered without errors.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.1300
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Custom Post Types Valid Diagnostic Class
 *
 * Verifies that custom post types (CPTs) are properly registered and
 * configured without conflicts or errors.
 *
 * @since 1.6035.1300
 */
class Diagnostic_Custom_Post_Types_Valid extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'custom-post-types-valid';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Custom Post Types Valid';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if custom post types are properly registered without errors';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'developer';

	/**
	 * Run the custom post types valid diagnostic check.
	 *
	 * @since  1.6035.1300
	 * @return array|null Finding array if CPT issues detected, null otherwise.
	 */
	public static function check() {
		$issues   = array();
		$warnings = array();
		$cpts     = array();

		// Get all registered post types.
		$post_types = get_post_types( array( '_builtin' => false ), 'objects' );

		if ( empty( $post_types ) ) {
			return null; // No custom post types registered.
		}

		// Check each custom post type.
		foreach ( $post_types as $post_type ) {
			$cpt_name = $post_type->name;
			$cpts[] = $cpt_name;

			// Check for reserved post type names.
			$reserved_names = array(
				'post', 'page', 'attachment', 'revision', 'nav_menu_item',
				'custom_css', 'customize_changeset', 'user_request',
				'wp_navigation', 'wp_template', 'wp_template_part',
			);

			if ( in_array( $cpt_name, $reserved_names, true ) ) {
				$issues[] = sprintf(
					/* translators: %s: post type name */
					__( 'Custom post type uses reserved name: %s', 'wpshadow' ),
					$cpt_name
				);
			}

			// Check for slug conflicts.
			$labels = $post_type->labels;
			if ( empty( $labels ) || ! isset( $labels->name ) ) {
				$warnings[] = sprintf(
					/* translators: %s: post type name */
					__( 'Custom post type "%s" missing labels', 'wpshadow' ),
					$cpt_name
				);
			}

			// Check if CPT is public.
			if ( $post_type->public === false && $post_type->show_ui === false ) {
				$warnings[] = sprintf(
					/* translators: %s: post type name */
					__( 'Custom post type "%s" is not public and hidden from UI', 'wpshadow' ),
					$cpt_name
				);
			}

			// Check for archive support.
			if ( $post_type->has_archive === false && $post_type->public === true ) {
				$warnings[] = sprintf(
					/* translators: %s: post type name */
					__( 'Public custom post type "%s" has archive disabled', 'wpshadow' ),
					$cpt_name
				);
			}

			// Check for rewrite rules.
			if ( empty( $post_type->rewrite ) && $post_type->public === true ) {
				$warnings[] = sprintf(
					/* translators: %s: post type name */
					__( 'Public custom post type "%s" missing rewrite rules', 'wpshadow' ),
					$cpt_name
				);
			}

			// Check capability type.
			if ( empty( $post_type->capability_type ) || $post_type->capability_type === 'post' ) {
				$warnings[] = sprintf(
					/* translators: %s: post type name */
					__( 'Custom post type "%s" using default capability type', 'wpshadow' ),
					$cpt_name
				);
			}

			// Check for proper support settings.
			$default_support = array( 'title', 'editor' );
			if ( empty( $post_type->supports ) ) {
				$warnings[] = sprintf(
					/* translators: %s: post type name */
					__( 'Custom post type "%s" has no support settings', 'wpshadow' ),
					$cpt_name
				);
			}
		}

		// Check for CPT conflicts with existing terms/taxonomies.
		global $wp_taxonomies;
		$taxonomy_names = array_keys( $wp_taxonomies );
		$cpt_tax_conflicts = array_intersect( $cpts, $taxonomy_names );

		if ( ! empty( $cpt_tax_conflicts ) ) {
			foreach ( $cpt_tax_conflicts as $conflict ) {
				$issues[] = sprintf(
					/* translators: %s: name */
					__( 'Post type and taxonomy name conflict: %s', 'wpshadow' ),
					$conflict
				);
			}
		}

		// Check for too many custom post types.
		if ( count( $post_types ) > 20 ) {
			$warnings[] = sprintf(
				/* translators: %d: count */
				__( 'High number of custom post types (%d) - consider consolidation', 'wpshadow' ),
				count( $post_types )
			);
		}

		// Check post count for each CPT.
		foreach ( $post_types as $post_type ) {
			$count = wp_count_posts( $post_type->name );
			
			if ( ! isset( $count->publish ) || $count->publish === 0 ) {
				$warnings[] = sprintf(
					/* translators: %s: post type name */
					__( 'Custom post type "%s" has no published posts', 'wpshadow' ),
					$post_type->name
				);
			}
		}

		// Check if CPTs are using standard WordPress admin capabilities.
		$admin_cap_issues = 0;
		foreach ( $post_types as $post_type ) {
			// Check if using proper map_meta_cap.
			if ( isset( $post_type->cap ) && $post_type->cap->edit_posts === 'edit_posts' ) {
				$admin_cap_issues++;
			}
		}

		if ( $admin_cap_issues > 0 ) {
			$warnings[] = sprintf(
				/* translators: %d: count */
				__( '%d custom post types using default capabilities - should map custom caps', 'wpshadow' ),
				$admin_cap_issues
			);
		}

		// Check if post type slug is too long.
		foreach ( $post_types as $post_type ) {
			if ( strlen( $post_type->name ) > 20 ) {
				$warnings[] = sprintf(
					/* translators: 1: post type name, 2: length */
					__( 'Post type name too long (%1$s - %2$d chars), recommend < 20', 'wpshadow' ),
					$post_type->name,
					strlen( $post_type->name )
				);
			}
		}

		// If critical issues found.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Custom post types have critical issues: ', 'wpshadow' ) . implode( ', ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/custom-post-types-valid',
				'context'      => array(
					'cpts'     => $cpts,
					'cpt_count' => count( $post_types ),
					'issues'   => $issues,
					'warnings' => $warnings,
				),
			);
		}

		// If only warnings.
		if ( ! empty( $warnings ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Custom post types have recommendations: ', 'wpshadow' ) . implode( ', ', $warnings ),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/custom-post-types-valid',
				'context'      => array(
					'cpts'     => $cpts,
					'cpt_count' => count( $post_types ),
					'warnings' => $warnings,
				),
			);
		}

		return null; // Custom post types are properly configured.
	}
}
