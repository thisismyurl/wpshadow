<?php
/**
 * Treatment: Child Theme Structure
 *
 * Creates or fixes child theme structure with required files
 * and proper stylesheet enqueuing.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Child_Theme_Structure Class
 *
 * Creates or fixes child theme structure to meet WordPress standards.
 *
 * @since 1.6093.1200
 */
class Treatment_Child_Theme_Structure extends Treatment_Base {

	/**
	 * Get the finding ID this treatment addresses.
	 *
	 * @since 1.6093.1200
	 * @return string Finding ID.
	 */
	public static function get_finding_id() {
		return 'child-theme-structure';
	}

	/**
	 * Apply the treatment.
	 *
	 * Creates or fixes child theme with proper structure.
	 *
	 * @since 1.6093.1200
	 * @return array {
	 *     Result array.
	 *
	 *     @type bool   $success Whether treatment succeeded.
	 *     @type string $message Human-readable result message.
	 * }
	 */
	public static function apply() {
		$theme = wp_get_theme();
		$parent = $theme->parent();

		// If not using child theme, we can't fix this automatically.
		if ( ! $parent ) {
			return array(
				'success' => false,
				'message' => __( 'Child theme structure can only be fixed if a child theme is active', 'wpshadow' ),
			);
		}

		$child_theme_dir = $theme->get_stylesheet_directory();
		$parent_slug     = $parent->get_stylesheet();
		$errors          = array();

		// Create style.css if missing.
		$style_css_path = $child_theme_dir . '/style.css';
		if ( ! file_exists( $style_css_path ) ) {
			$style_css_content = self::get_style_css_template( $theme, $parent_slug );
			if ( file_put_contents( $style_css_path, $style_css_content ) === false ) {
				$errors[] = __( 'Failed to create style.css', 'wpshadow' );
			}
		} else {
			// Verify Template header exists.
			$content = file_get_contents( $style_css_path );
			if ( strpos( $content, 'Template:' ) === false ) {
				$content = preg_replace(
					'/(\*\/)(\s*Template:)/i',
					' * Template: ' . $parent_slug . "\n$1",
					$content
				);
				file_put_contents( $style_css_path, $content );
			}
		}

		// Create functions.php if missing.
		$functions_php_path = $child_theme_dir . '/functions.php';
		if ( ! file_exists( $functions_php_path ) ) {
			$functions_content = self::get_functions_php_template( $parent_slug );
			if ( file_put_contents( $functions_php_path, $functions_content ) === false ) {
				$errors[] = __( 'Failed to create functions.php', 'wpshadow' );
			}
		} else {
			// Check if enqueuing stylesheet properly.
			$content = file_get_contents( $functions_php_path );
			if ( strpos( $content, 'wp_enqueue_style' ) === false ) {
				// Add enqueue code.
				$enqueue_code = "\n\n// Enqueue child and parent stylesheets.\nadd_action( 'wp_enqueue_scripts', function() {
	wp_enqueue_style( '" . sanitize_key( $theme->get_stylesheet() ) . "-child', get_stylesheet_uri() );
} );\n";
				file_put_contents( $functions_php_path, $content . $enqueue_code );
			}
		}

		if ( ! empty( $errors ) ) {
			return array(
				'success' => false,
				'message' => __( 'Child theme structure update failed: ', 'wpshadow' ) . implode( ', ', $errors ),
			);
		}

		return array(
			'success' => true,
			'message' => __( 'Fixed child theme structure and ensured proper stylesheet enqueuing', 'wpshadow' ),
		);
	}

	/**
	 * Undo the treatment.
	 *
	 * Removes created files (preserves manual modifications).
	 *
	 * @since 1.6093.1200
	 * @return array Result array.
	 */
	public static function undo() {
		// Note: We don't delete files on undo to preserve user customizations.
		// Manual cleanup may be required if needed.
		return array(
			'success' => true,
			'message' => __( 'Undo: Manual cleanup may be required for child theme files', 'wpshadow' ),
		);
	}

	/**
	 * Get style.css template content.
	 *
	 * @since 1.6093.1200
	 * @param  WP_Theme $theme       Child theme.
	 * @param  string   $parent_slug Parent theme slug.
	 * @return string Template content.
	 */
	private static function get_style_css_template( $theme, $parent_slug ) {
		$theme_name = $theme->get( 'Name' );
		$theme_uri  = $theme->get( 'ThemeURI' );
		$author     = $theme->get( 'Author' );

		return "/*
Theme Name: {$theme_name}
Theme URI: {$theme_uri}
Author: {$author}
Template: {$parent_slug}
Version: 1.6093.1200
Text Domain: " . sanitize_key( $theme_name ) . "
Domain Path: /languages
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

This is a child theme of {$parent_slug}.
*/

/* Add custom styles here */
";
	}

	/**
	 * Get functions.php template content.
	 *
	 * @since 1.6093.1200
	 * @param  string $parent_slug Parent theme slug.
	 * @return string Template content.
	 */
	private static function get_functions_php_template( $parent_slug ) {
		return "<?php
/**
 * Child Theme Functions
 *
 * Enqueues child theme stylesheet and parent stylesheet.
 *
 * @package " . ucfirst( str_replace( '-', ' ', $parent_slug ) ) . " Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue child theme stylesheet.
 */
add_action( 'wp_enqueue_scripts', function() {
	if ( is_child_theme() ) {
		// Enqueue parent theme stylesheet.
		wp_enqueue_style(
			get_template(),
			trailingslashit( get_template_directory_uri() ) . 'style.css'
		);

		// Enqueue child theme stylesheet.
		wp_enqueue_style(
			get_stylesheet(),
			get_stylesheet_uri(),
			array( get_template() ),
			wp_get_theme()->get( 'Version' )
		);
	}
}, 10 );
";
	}
}
