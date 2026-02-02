<?php
/**
 * CPT Capability Mapping Diagnostic
 *
 * Validates capability mapping for custom post types. Tests if users have correct
 * permissions to create, edit, delete, and publish CPT content.
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
 * CPT Capability Mapping Diagnostic Class
 *
 * Checks for capability mapping issues with custom post types.
 *
 * @since 1.2601.2148
 */
class Diagnostic_CPT_Capability_Mapping extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'cpt-capability-mapping';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'CPT Capability Mapping';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates capability mapping for custom post types and user permissions';

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

		// Get all roles.
		global $wp_roles;
		if ( ! isset( $wp_roles ) ) {
			$wp_roles = new \WP_Roles();
		}

		foreach ( $custom_post_types as $cpt ) {
			// Check if capabilities are properly mapped.
			if ( empty( $cpt->cap ) ) {
				$issues[] = sprintf(
					/* translators: %s: post type slug */
					__( 'CPT "%s" has no capability mapping (no one can manage content)', 'wpshadow' ),
					esc_html( $cpt->name )
				);
				continue;
			}

			// Check if using default 'post' capabilities (usually wrong).
			if ( $cpt->capability_type === 'post' && 'post' !== $cpt->name ) {
				// This means it's using post capabilities - check if map_meta_cap is true.
				if ( ! $cpt->map_meta_cap ) {
					$issues[] = sprintf(
						/* translators: %s: post type slug */
						__( 'CPT "%s" uses post capabilities without map_meta_cap (permissions may not work)', 'wpshadow' ),
						esc_html( $cpt->name )
					);
				}
			}

			// Check if custom capabilities exist in any role.
			if ( $cpt->capability_type !== 'post' ) {
				$required_caps = array(
					'edit_posts'          => $cpt->cap->edit_posts,
					'edit_others_posts'   => $cpt->cap->edit_others_posts,
					'publish_posts'       => $cpt->cap->publish_posts,
					'read_private_posts'  => $cpt->cap->read_private_posts,
					'delete_posts'        => $cpt->cap->delete_posts,
					'delete_private_posts' => $cpt->cap->delete_private_posts,
					'delete_published_posts' => $cpt->cap->delete_published_posts,
					'delete_others_posts' => $cpt->cap->delete_others_posts,
					'edit_private_posts'  => $cpt->cap->edit_private_posts,
					'edit_published_posts' => $cpt->cap->edit_published_posts,
				);

				$caps_found = array();
				foreach ( $wp_roles->roles as $role_name => $role_info ) {
					if ( ! empty( $role_info['capabilities'] ) ) {
						foreach ( $required_caps as $cap_type => $cap_name ) {
							if ( isset( $role_info['capabilities'][ $cap_name ] ) ) {
								$caps_found[ $cap_name ] = true;
							}
						}
					}
				}

				$missing_caps = array_diff( $required_caps, array_keys( $caps_found ) );
				if ( ! empty( $missing_caps ) ) {
					$issues[] = sprintf(
						/* translators: 1: post type slug, 2: number of missing capabilities */
						__( 'CPT "%1$s" has %2$d custom capabilities not assigned to any role (no one can manage content)', 'wpshadow' ),
						esc_html( $cpt->name ),
						count( $missing_caps )
					);
				}
			}

			// Check if editor role has edit_posts capability for this CPT.
			$editor_role = get_role( 'editor' );
			if ( $editor_role && $cpt->show_ui ) {
				if ( ! $editor_role->has_cap( $cpt->cap->edit_posts ) ) {
					$issues[] = sprintf(
						/* translators: %s: post type slug */
						__( 'Editors cannot edit "%s" posts (capability not assigned to editor role)', 'wpshadow' ),
						esc_html( $cpt->name )
					);
				}

				if ( ! $editor_role->has_cap( $cpt->cap->publish_posts ) ) {
					$issues[] = sprintf(
						/* translators: %s: post type slug */
						__( 'Editors cannot publish "%s" posts (publish capability missing)', 'wpshadow' ),
						esc_html( $cpt->name )
					);
				}
			}

			// Check if author role has edit_posts capability (if CPT should support authors).
			$author_role = get_role( 'author' );
			if ( $author_role && $cpt->show_ui && 'post' === $cpt->capability_type ) {
				// If using post capabilities, authors should be able to edit their own.
				if ( ! $author_role->has_cap( $cpt->cap->edit_posts ) ) {
					$issues[] = sprintf(
						/* translators: %s: post type slug */
						__( 'Authors cannot create "%s" posts (capability inherited from posts but missing)', 'wpshadow' ),
						esc_html( $cpt->name )
					);
				}
			}

			// Check if administrator has all capabilities.
			$admin_role = get_role( 'administrator' );
			if ( $admin_role ) {
				$admin_missing_caps = array();
				foreach ( array( 'edit_posts', 'edit_others_posts', 'publish_posts', 'delete_posts' ) as $cap_type ) {
					$cap_property = $cap_type;
					if ( isset( $cpt->cap->$cap_property ) && ! $admin_role->has_cap( $cpt->cap->$cap_property ) ) {
						$admin_missing_caps[] = $cpt->cap->$cap_property;
					}
				}

				if ( ! empty( $admin_missing_caps ) ) {
					$issues[] = sprintf(
						/* translators: 1: post type slug, 2: number of missing capabilities */
						__( 'Administrators missing %2$d capabilities for "%1$s" (may not be able to manage)', 'wpshadow' ),
						esc_html( $cpt->name ),
						count( $admin_missing_caps )
					);
				}
			}

			// Check current user can access this CPT.
			$current_user = wp_get_current_user();
			if ( $current_user && $current_user->ID > 0 && $cpt->show_ui ) {
				if ( ! current_user_can( $cpt->cap->edit_posts ) ) {
					$issues[] = sprintf(
						/* translators: %s: post type slug */
						__( 'Current user cannot edit "%s" posts (check role capabilities)', 'wpshadow' ),
						esc_html( $cpt->name )
					);
				}
			}

			// Check for overly permissive capabilities (e.g., 'read' capability for edit).
			if ( 'read' === $cpt->cap->edit_posts || 'read' === $cpt->cap->edit_others_posts ) {
				$issues[] = sprintf(
					/* translators: %s: post type slug */
					__( 'CPT "%s" uses "read" capability for editing (security risk - anyone can edit)', 'wpshadow' ),
					esc_html( $cpt->name )
				);
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( '. ', $issues ),
				'severity'    => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/cpt-capability-mapping',
			);
		}

		return null;
	}
}
