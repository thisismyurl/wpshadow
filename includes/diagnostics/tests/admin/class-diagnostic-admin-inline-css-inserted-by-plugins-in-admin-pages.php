<?php
/**
 * Admin Inline CSS Inserted By Plugins In Admin Pages Diagnostic
 *
 * Detects inline CSS inserted directly by plugins in admin pages.
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
 * Admin Inline CSS Inserted By Plugins In Admin Pages Diagnostic Class
 *
 * Scans for inline CSS blocks inserted by plugins in admin pages,
 * which indicates improper asset loading practices.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Admin_Inline_Css_Inserted_By_Plugins_In_Admin_Pages extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'admin-inline-css-inserted-by-plugins-in-admin-pages';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Inline CSS Inserted By Plugins In Admin Pages';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects inline CSS inserted directly by plugins in admin pages';

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
		if ( ! is_admin() ) {
			return null;
		}

		$plugin_inline_styles = array();

		// Check for inline styles registered by plugins (not core).
		global $wp_styles;

		if ( ! empty( $wp_styles ) && isset( $wp_styles->registered ) ) {
			$core_handles = array(
				'admin-bar',
				'admin-menu',
				'admin-notices',
				'editor-buttons',
				'common',
				'forms',
				'colors',
				'media-views',
				'dashboard',
				'nav-menu',
				'customize-controls',
				'wp-codemirror',
				'wp-pointer',
				'theme-switcher',
				'login',
				'buttons',
			);

			foreach ( $wp_styles->registered as $handle => $style_obj ) {
				// Skip core WordPress styles.
				if ( in_array( $handle, $core_handles, true ) ) {
					continue;
				}

				// Skip styles without inline content.
				$has_inline = false;

				if ( isset( $style_obj->extra['before'] ) && is_array( $style_obj->extra['before'] ) && ! empty( $style_obj->extra['before'] ) ) {
					$has_inline = true;
				}

				if ( isset( $style_obj->extra['after'] ) && is_array( $style_obj->extra['after'] ) && ! empty( $style_obj->extra['after'] ) ) {
					$has_inline = true;
				}

				if ( $has_inline ) {
					// Get size of inline content.
					$size = 0;

					if ( isset( $style_obj->extra['before'] ) && is_array( $style_obj->extra['before'] ) ) {
						foreach ( $style_obj->extra['before'] as $code ) {
							$size += strlen( (string) $code );
						}
					}

					if ( isset( $style_obj->extra['after'] ) && is_array( $style_obj->extra['after'] ) ) {
						foreach ( $style_obj->extra['after'] as $code ) {
							$size += strlen( (string) $code );
						}
					}

					$plugin_inline_styles[] = array(
						'handle'  => $handle,
						'size'    => $size,
						'size_kb' => round( $size / 1024, 2 ),
						'reason'  => __( 'Plugin registered style with inline code', 'wpshadow' ),
					);
				}
			}
		}

		if ( empty( $plugin_inline_styles ) ) {
			return null;
		}

		$items_list = '';
		$max_items  = 5;

		foreach ( array_slice( $plugin_inline_styles, 0, $max_items ) as $style ) {
			$items_list .= sprintf(
				"\n- %s: %s KB",
				esc_html( $style['handle'] ),
				$style['size_kb']
			);
		}

		if ( count( $plugin_inline_styles ) > $max_items ) {
			$items_list .= sprintf(
				/* translators: %d: count */
				__( "\n...and %d more plugin styles with inline code", 'wpshadow' ),
				count( $plugin_inline_styles ) - $max_items
			);
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: count, 2: list */
				__( 'Found %1$d plugin style(s) with inline CSS. Plugins should use wp_enqueue_style() with external files instead of inserting inline code. This improves caching, performance, and maintainability.%2$s', 'wpshadow' ),
				count( $plugin_inline_styles ),
				$items_list
			),
			'severity'     => 'low',
			'threat_level' => 25,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/admin-inline-css-inserted-by-plugins-in-admin-pages',
			'meta'         => array(
				'plugin_styles' => $plugin_inline_styles,
			),
		);
	}
}
