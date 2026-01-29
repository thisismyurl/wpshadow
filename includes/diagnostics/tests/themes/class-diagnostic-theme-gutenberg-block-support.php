<?php
/**
 * Theme Gutenberg Block Support Diagnostic
 *
 * Verifies theme properly supports WordPress Gutenberg block editor and
 * provides necessary styling and functionality.
 *
 * @package    WPShadow\Diagnostics
 * @subpackage Tests
 * @since      1.2601.2201
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Gutenberg Block Support Diagnostic Class
 *
 * Checks for:
 * - add_theme_support( 'wp-block-styles' )
 * - add_theme_support( 'align-wide' )
 * - add_theme_support( 'editor-styles' )
 * - Block editor stylesheet loaded
 * - Core block patterns support
 * - Responsive embeds support
 *
 * @since 1.2601.2201
 */
class Diagnostic_Theme_Gutenberg_Block_Support extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-gutenberg-block-support';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Gutenberg Block Support';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies theme properly supports WordPress Gutenberg blocks';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'themes';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2201
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check for wp-block-styles support.
		if ( ! current_theme_supports( 'wp-block-styles' ) ) {
			$issues[] = __( 'Missing add_theme_support( \'wp-block-styles\' ) - blocks may not display correctly', 'wpshadow' );
		}

		// Check for align-wide support.
		if ( ! current_theme_supports( 'align-wide' ) ) {
			$issues[] = __( 'Missing add_theme_support( \'align-wide\' ) - wide/full alignments unavailable', 'wpshadow' );
		}

		// Check for editor-styles support.
		if ( ! current_theme_supports( 'editor-styles' ) ) {
			$issues[] = __( 'Missing add_theme_support( \'editor-styles\' ) - editor won\'t match frontend', 'wpshadow' );
		}

		// Check for responsive embeds.
		if ( ! current_theme_supports( 'responsive-embeds' ) ) {
			$issues[] = __( 'Missing add_theme_support( \'responsive-embeds\' ) - embeds may not be mobile-friendly', 'wpshadow' );
		}

		// Check for editor color palette.
		if ( ! current_theme_supports( 'editor-color-palette' ) ) {
			$issues[] = __( 'No custom editor color palette defined - users limited to default colors', 'wpshadow' );
		}

		// Check if theme has editor stylesheet.
		$theme = wp_get_theme();
		$editor_style_exists = false;

		$possible_editor_styles = array(
			get_stylesheet_directory() . '/editor-style.css',
			get_stylesheet_directory() . '/assets/css/editor-style.css',
			get_stylesheet_directory() . '/css/editor-style.css',
			get_stylesheet_directory() . '/editor.css',
		);

		foreach ( $possible_editor_styles as $path ) {
			if ( file_exists( $path ) ) {
				$editor_style_exists = true;
				break;
			}
		}

		if ( ! $editor_style_exists ) {
			$issues[] = __( 'No editor stylesheet found - editor appearance won\'t match frontend', 'wpshadow' );
		}

		// Check for block pattern support.
		if ( ! current_theme_supports( 'core-block-patterns' ) ) {
			$issues[] = __( 'Block patterns disabled - users missing helpful content templates', 'wpshadow' );
		}

		// Check theme.json exists (modern approach).
		$theme_json_path = get_stylesheet_directory() . '/theme.json';
		if ( ! file_exists( $theme_json_path ) ) {
			$issues[] = __( 'No theme.json found - missing modern theme configuration', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => implode( "\n", $issues ),
			'severity'     => 'medium',
			'threat_level' => 35,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/gutenberg-block-support',
		);
	}
}
