<?php
/**
 * Theme Data Provider
 *
 * Centralized theme data extraction with fallback chains.
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Data Provider
 *
 * Provides centralized access to theme color data including palettes,
 * backgrounds, and color contexts for accessibility testing.
 * Handles fallback chains from block themes → classic themes → defaults.
 */
class Theme_Data_Provider {
	/**
	 * Get theme color palette with fallbacks.
	 *
	 * Tries:
	 * 1. Block theme color palette (wp_get_global_settings)
	 * 2. Classic theme support (editor-color-palette)
	 * 3. Safe defaults
	 *
	 * @return array Array of color objects with name, slug, color keys.
	 */
	public static function get_palette() {
		$palette = array();

		// Try block theme settings first
		$settings_palette = wp_get_global_settings( array( 'color', 'palette', 'theme' ) );
		if ( is_array( $settings_palette ) && ! empty( $settings_palette ) ) {
			$palette = $settings_palette;
		}

		// Fall back to classic theme support
		if ( empty( $palette ) ) {
			$support_palette = get_theme_support( 'editor-color-palette' );
			if ( is_array( $support_palette ) && isset( $support_palette[0] ) ) {
				$palette = $support_palette[0];
			}
		}

		// Fall back to safe defaults
		if ( empty( $palette ) ) {
			$palette = array(
				array(
					'name'  => 'Black',
					'slug'  => 'black',
					'color' => '#000000',
				),
				array(
					'name'  => 'White',
					'slug'  => 'white',
					'color' => '#ffffff',
				),
				array(
					'name'  => 'Gray',
					'slug'  => 'gray',
					'color' => '#666666',
				),
			);
		}

		// Filter out items with missing colors
		return array_values(
			array_filter(
				$palette,
				function ( $item ) {
					return ! empty( $item['color'] );
				}
			)
		);
	}

	/**
	 * Get theme background color with fallbacks.
	 *
	 * Tries:
	 * 1. Block theme background
	 * 2. Classic theme background color support
	 * 3. White (#ffffff)
	 *
	 * @return string Hex color code.
	 */
	public static function get_background_color() {
		// Try block theme settings
		$bg_color = wp_get_global_settings( array( 'color', 'background' ) );
		if ( ! empty( $bg_color ) ) {
			return $bg_color;
		}

		// Try classic theme support
		$support = get_theme_support( 'custom-background' );
		if ( is_array( $support ) && isset( $support[0]['default-color'] ) ) {
			return '#' . ltrim( (string) $support[0]['default-color'], '#' );
		}

		// Try background color option
		$bg_option = get_option( 'background_color' );
		if ( ! empty( $bg_option ) ) {
			return '#' . ltrim( (string) $bg_option, '#' );
		}

		// Default to white
		return '#ffffff';
	}

	/**
	 * Get color context combinations for a11y testing.
	 *
	 * Returns pairs of foreground/background colors that should be tested
	 * for contrast compliance.
	 *
	 * @return array Array of context objects with label, fg, bg keys.
	 */
	public static function get_color_contexts() {
		$contexts   = array();
		$palette    = self::get_palette();
		$background = self::get_background_color();

		if ( empty( $palette ) ) {
			return $contexts;
		}

		// Get theme text and link colors
		$text_color  = wp_get_global_settings( array( 'color', 'text' ) ) ?: '#000000';
		$link_color  = wp_get_global_settings( array( 'color', 'link' ) ) ?: '#0073aa';
		$button_bg   = wp_get_global_settings( array( 'color', 'button' ) ) ?: '#0073aa';
		$button_text = wp_get_global_settings( array( 'color', 'buttontext' ) ) ?: '#ffffff';

		// Text on background
		if ( $text_color ) {
			$contexts[] = array(
				'label' => __( 'Body text on background', 'wpshadow' ),
				'fg'    => $text_color,
				'bg'    => $background,
			);
		}

		// Links on background
		if ( $link_color ) {
			$contexts[] = array(
				'label' => __( 'Links on background', 'wpshadow' ),
				'fg'    => $link_color,
				'bg'    => $background,
			);
		}

		// Button text on button
		if ( $button_bg && $button_text ) {
			$contexts[] = array(
				'label' => __( 'Button text on button', 'wpshadow' ),
				'fg'    => $button_text,
				'bg'    => $button_bg,
			);
		}

		return $contexts;
	}
}
