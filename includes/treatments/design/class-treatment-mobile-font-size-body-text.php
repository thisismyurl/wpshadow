<?php
/**
 * Mobile Font Size - Body Text Treatment
 *
 * Validates minimum font size for body text on mobile to ensure readability.
 *
 * @since   1.6033.1645
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Font Size - Body Text Treatment Class
 *
 * Validates minimum font size for body text on mobile to ensure readability
 * without forcing pinch-zoom, prevents iOS auto-zoom issues.
 *
 * @since 1.6033.1645
 */
class Treatment_Mobile_Font_Size_Body_Text extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-font-size-body-text';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Font Size - Body Text';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validate minimum font size for body text on mobile (16px minimum to prevent iOS auto-zoom)';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.1645
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if theme has defined font sizes
		$supports_font_sizes = current_theme_supports( 'editor-font-sizes' );
		if ( ! $supports_font_sizes ) {
			$issues[] = __( 'Theme does not declare font size support', 'wpshadow' );
		}

		// Get theme's base font size
		$base_font_size = apply_filters( 'wpshadow_theme_base_font_size', '16px' );
		if ( '16px' !== $base_font_size && ! preg_match( '/1[6-9]px|[2-9][0-9]px|1\d{2}%/', $base_font_size ) ) {
			$issues[] = sprintf(
				/* translators: %s: font size */
				__( 'Body text font size is %s; minimum 16px recommended to prevent iOS auto-zoom', 'wpshadow' ),
				$base_font_size
			);
		}

		// Check for responsive font scaling
		$has_responsive_fonts = apply_filters( 'wpshadow_theme_has_responsive_fonts', false );
		if ( ! $has_responsive_fonts ) {
			$issues[] = __( 'Theme does not appear to use responsive font scaling', 'wpshadow' );
		}

		// Check for fluid typography support
		$supports_fluid_typography = current_theme_supports( 'fluid-typography' );
		if ( ! $supports_fluid_typography ) {
			$issues[] = __( 'Theme does not support fluid typography for adaptive scaling', 'wpshadow' );
		}

		// Check for typography plugins
		$typography_plugins = array(
			'elementor' => 'Elementor',
			'divi-builder' => 'Divi',
		);

		$has_typography_plugin = false;
		foreach ( $typography_plugins as $plugin_slug => $plugin_name ) {
			if ( is_plugin_active( "$plugin_slug/$plugin_slug.php" ) ) {
				$has_typography_plugin = true;
				break;
			}
		}

		if ( ! $has_typography_plugin && ! $has_responsive_fonts ) {
			$issues[] = __( 'No typography optimization plugin detected', 'wpshadow' );
		}

		// Check if font scaling uses px instead of em/rem
		$uses_relative_units = apply_filters( 'wpshadow_css_uses_relative_units', false );
		if ( ! $uses_relative_units ) {
			$issues[] = __( 'CSS may use fixed pixels instead of relative units (em/rem) for better scaling', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/mobile-font-size-body-text',
			);
		}

		return null;
	}
}
