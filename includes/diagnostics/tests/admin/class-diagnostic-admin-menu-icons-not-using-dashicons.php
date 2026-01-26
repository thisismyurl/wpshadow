<?php
/**
 * Admin Menu Icons Not Using Dashicons Diagnostic
 *
 * Detects admin menu items that use custom icons instead of standard WordPress Dashicons.
 * While custom icons are valid, Dashicons provide better consistency and performance.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Admin
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Menu Icons Not Using Dashicons Diagnostic Class
 *
 * Scans WordPress admin menu items for icons that don't use
 * the standard Dashicons font, which may impact consistency.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Admin_Menu_Icons_Not_Using_Dashicons extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'admin-menu-icons-not-using-dashicons';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Menu Icons Not Using Dashicons';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects admin menu items using custom icons instead of Dashicons';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'admin';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Only run in admin context.
		if ( ! is_admin() ) {
			return null;
		}

		global $menu;

		$non_dashicon_items = array();

		// Check main menu items.
		if ( ! empty( $menu ) && is_array( $menu ) ) {
			foreach ( $menu as $menu_item ) {
				if ( empty( $menu_item ) || ! is_array( $menu_item ) ) {
					continue;
				}

				// Menu item structure: [0] = title, [1] = capability, [2] = slug, [3] = page_title, [4] = classes, [5] = id, [6] = icon.
				$title = isset( $menu_item[0] ) ? wp_strip_all_tags( $menu_item[0] ) : '';
				$slug  = isset( $menu_item[2] ) ? $menu_item[2] : '';
				$icon  = isset( $menu_item[6] ) ? $menu_item[6] : '';

				if ( empty( $icon ) || empty( $title ) ) {
					continue;
				}

				// Skip separators.
				if ( strpos( $slug, 'separator' ) !== false ) {
					continue;
				}

				// Check if icon is using Dashicons.
				$icon_type = self::determine_icon_type( $icon );

				if ( $icon_type !== 'dashicons' ) {
					$non_dashicon_items[] = array(
						'title' => $title,
						'slug'  => $slug,
						'icon'  => $icon,
						'type'  => $icon_type,
					);
				}
			}
		}

		// If non-Dashicon items found, return finding.
		if ( ! empty( $non_dashicon_items ) ) {
			$item_count = count( $non_dashicon_items );

			// Build detailed description.
			$items_list = '';
			$max_items_to_show = 5;
			$shown_items = array_slice( $non_dashicon_items, 0, $max_items_to_show );

			foreach ( $shown_items as $item ) {
				$items_list .= sprintf(
					"\n- %s [%s: %s]",
					esc_html( $item['title'] ),
					esc_html( ucfirst( $item['type'] ) ),
					esc_html( self::truncate_icon_value( $item['icon'] ) )
				);
			}

			if ( $item_count > $max_items_to_show ) {
				$items_list .= sprintf(
					/* translators: %d: number of additional items */
					__( "\n...and %d more items", 'wpshadow' ),
					$item_count - $max_items_to_show
				);
			}

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: 1: number of menu items, 2: list of affected items */
					__(
						'Found %1$d admin menu item(s) using custom icons instead of WordPress Dashicons. While custom icons are functional, Dashicons provide better visual consistency, faster loading, and native WordPress integration.%2$s',
						'wpshadow'
					),
					$item_count,
					$items_list
				),
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/admin-menu-icons-not-using-dashicons',
				'meta'         => array(
					'item_count'     => $item_count,
					'affected_items' => $non_dashicon_items,
				),
			);
		}

		return null; // All menu icons use Dashicons or are empty.
	}

	/**
	 * Determine the type of icon being used.
	 *
	 * @since  1.2601.2148
	 * @param  string $icon Icon value from menu item.
	 * @return string Icon type: 'dashicons', 'svg', 'img', 'data-uri', 'base64', or 'unknown'.
	 */
	private static function determine_icon_type( string $icon ): string {
		// Dashicons use the format 'dashicons-*'.
		if ( strpos( $icon, 'dashicons-' ) === 0 ) {
			return 'dashicons';
		}

		// SVG inline markup.
		if ( stripos( $icon, '<svg' ) !== false ) {
			return 'svg';
		}

		// Base64 encoded image.
		if ( strpos( $icon, 'data:image/' ) === 0 ) {
			return 'data-uri';
		}

		// External image URL.
		if ( preg_match( '/^https?:\/\/.+\.(png|jpg|jpeg|gif|svg)$/i', $icon ) ) {
			return 'img';
		}

		// WordPress 'none' value.
		if ( $icon === 'none' || $icon === '' ) {
			return 'none';
		}

		// Check if it's a div wrapper (used for custom icons).
		if ( strpos( $icon, '<div' ) === 0 || strpos( $icon, '<img' ) === 0 ) {
			return 'html';
		}

		return 'unknown';
	}

	/**
	 * Truncate icon value for display purposes.
	 *
	 * @since  1.2601.2148
	 * @param  string $icon Icon value.
	 * @return string Truncated icon value.
	 */
	private static function truncate_icon_value( string $icon ): string {
		$max_length = 50;

		if ( strlen( $icon ) <= $max_length ) {
			return $icon;
		}

		return substr( $icon, 0, $max_length ) . '...';
	}
}
