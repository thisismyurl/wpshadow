<?php
/**
 * CPT Menu Visibility Diagnostic
 *
 * Verifies custom post types appear in admin menu. Tests show_in_menu and menu_position
 * settings to ensure CPTs are accessible to users in the WordPress admin.
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
 * CPT Menu Visibility Diagnostic Class
 *
 * Checks for custom post types that should be visible but aren't in the admin menu.
 *
 * @since 0.6093.1200
 */
class Diagnostic_CPT_Menu_Visibility extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'cpt-menu-visibility';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'CPT Menu Visibility';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies custom post types appear in admin menu with correct positioning';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'cpt';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $menu, $submenu;

		$issues = array();

		// Get all registered post types.
		$post_types = get_post_types( array(), 'objects' );

		// Filter to only custom post types (exclude built-in).
		$built_in          = array( 'post', 'page', 'attachment', 'revision', 'nav_menu_item', 'custom_css', 'customize_changeset', 'oembed_cache', 'user_request', 'wp_block', 'wp_template', 'wp_template_part', 'wp_global_styles', 'wp_navigation' );
		$custom_post_types = array_filter(
			$post_types,
			function ( $pt ) use ( $built_in ) {
				return ! in_array( $pt->name, $built_in, true );
			}
		);

		if ( empty( $custom_post_types ) ) {
			// No custom post types - nothing to check.
			return null;
		}

		foreach ( $custom_post_types as $cpt ) {
			// Skip if show_in_menu is explicitly false.
			if ( false === $cpt->show_in_menu ) {
				continue;
			}

			// Check if CPT should appear in menu.
			if ( $cpt->show_ui && $cpt->show_in_menu ) {
				// Verify it actually appears in $menu or as a submenu.
				$found_in_menu = false;

				// Check main menu.
				if ( is_array( $menu ) ) {
					foreach ( $menu as $menu_item ) {
						if ( isset( $menu_item[2] ) && false !== strpos( $menu_item[2], 'edit.php?post_type=' . $cpt->name ) ) {
							$found_in_menu = true;
							break;
						}
					}
				}

				// Check submenu (if show_in_menu is a parent slug).
				if ( ! $found_in_menu && is_string( $cpt->show_in_menu ) && is_array( $submenu ) ) {
					foreach ( $submenu as $parent => $items ) {
						foreach ( $items as $item ) {
							if ( isset( $item[2] ) && false !== strpos( $item[2], 'edit.php?post_type=' . $cpt->name ) ) {
								$found_in_menu = true;
								break 2;
							}
						}
					}
				}

				if ( ! $found_in_menu ) {
					$issues[] = sprintf(
						/* translators: %s: post type slug */
						__( 'CPT "%s" configured to show in menu but not found', 'wpshadow' ),
						esc_html( $cpt->name )
					);
				}
			}

			// Check for invalid parent menu (if show_in_menu is a string).
			if ( is_string( $cpt->show_in_menu ) && '' !== $cpt->show_in_menu ) {
				$parent_exists = false;
				if ( is_array( $menu ) ) {
					foreach ( $menu as $menu_item ) {
						if ( isset( $menu_item[2] ) && $menu_item[2] === $cpt->show_in_menu ) {
							$parent_exists = true;
							break;
						}
					}
				}

				if ( ! $parent_exists ) {
					$issues[] = sprintf(
						/* translators: 1: post type slug, 2: parent menu slug */
						__( 'CPT "%1$s" references non-existent parent menu "%2$s"', 'wpshadow' ),
						esc_html( $cpt->name ),
						esc_html( $cpt->show_in_menu )
					);
				}
			}

			// Check for conflicting menu positions.
			if ( $cpt->show_in_menu && is_numeric( $cpt->menu_position ) ) {
				// Common WordPress menu positions to avoid conflicts with.
				$reserved_positions = array(
					5  => 'Posts',
					10 => 'Media',
					15 => 'Links',
					20 => 'Pages',
					25 => 'Comments',
					60 => 'Appearance',
					65 => 'Plugins',
					70 => 'Users',
					75 => 'Tools',
					80 => 'Settings',
				);

				if ( isset( $reserved_positions[ $cpt->menu_position ] ) ) {
					$issues[] = sprintf(
						/* translators: 1: post type slug, 2: menu position, 3: conflicting item */
						__( 'CPT "%1$s" menu position %2$d conflicts with "%3$s"', 'wpshadow' ),
						esc_html( $cpt->name ),
						$cpt->menu_position,
						$reserved_positions[ $cpt->menu_position ]
					);
				}

				// Check if multiple CPTs share the same position.
				foreach ( $custom_post_types as $other_cpt ) {
					if ( $cpt->name === $other_cpt->name ) {
						continue;
					}
					if ( $other_cpt->menu_position === $cpt->menu_position ) {
						$issues[] = sprintf(
							/* translators: 1: post type slug, 2: conflicting post type */
							__( 'CPT "%1$s" shares menu position with "%2$s"', 'wpshadow' ),
							esc_html( $cpt->name ),
							esc_html( $other_cpt->name )
						);
					}
				}
			}

			// Check if menu icon is missing or invalid.
			if ( $cpt->show_in_menu && empty( $cpt->menu_icon ) ) {
				$issues[] = sprintf(
					/* translators: %s: post type slug */
					__( 'CPT "%s" has no menu icon (will use default post icon)', 'wpshadow' ),
					esc_html( $cpt->name )
				);
			} elseif ( ! empty( $cpt->menu_icon ) && 0 === strpos( $cpt->menu_icon, 'dashicons-' ) ) {
				// Verify dashicon exists (basic validation).
				$valid_dashicons = array( 'dashicons-admin-appearance', 'dashicons-admin-comments', 'dashicons-admin-customizer', 'dashicons-admin-generic', 'dashicons-admin-home', 'dashicons-admin-media', 'dashicons-admin-page', 'dashicons-admin-post', 'dashicons-admin-site', 'dashicons-admin-tools', 'dashicons-admin-users' );
				if ( ! in_array( $cpt->menu_icon, $valid_dashicons, true ) ) {
					// Just a warning - dashicon might be valid even if not in common list.
					$issues[] = sprintf(
						/* translators: 1: post type slug, 2: dashicon name */
						__( 'CPT "%1$s" uses uncommon dashicon "%2$s" (verify it exists)', 'wpshadow' ),
						esc_html( $cpt->name ),
						esc_html( $cpt->menu_icon )
					);
				}
			}

			// Check if current user can access the menu item.
			$user = wp_get_current_user();
			if ( $user && $user->ID > 0 ) {
				if ( ! current_user_can( $cpt->cap->edit_posts ) ) {
					$issues[] = sprintf(
						/* translators: %s: post type slug */
						__( 'Current user cannot access CPT "%s" menu (insufficient permissions)', 'wpshadow' ),
						esc_html( $cpt->name )
					);
				}
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'high',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/cpt-menu-visibility?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
