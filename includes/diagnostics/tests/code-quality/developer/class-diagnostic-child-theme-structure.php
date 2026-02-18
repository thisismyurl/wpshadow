<?php
/**
 * Child Theme Structure Diagnostic
 *
 * Checks if a child theme is properly structured with required files.
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
 * Child Theme Structure Diagnostic Class
 *
 * Verifies that if a child theme is in use, it's properly structured
 * with required files and correct template hierarchy.
 *
 * @since 1.6035.1300
 */
class Diagnostic_Child_Theme_Structure extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'child-theme-structure';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Child Theme Structure';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if a child theme is properly structured with required files';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'developer';

	/**
	 * Run the child theme structure diagnostic check.
	 *
	 * @since  1.6035.1300
	 * @return array|null Finding array if child theme issues detected, null otherwise.
	 */
	public static function check() {
		$theme = wp_get_theme();
		
		// If not using a child theme, check if they should be.
		if ( ! $theme->parent() ) {
			// Check if they're modifying a third-party theme directly.
			$theme_dir  = $theme->get_stylesheet_directory();
			$is_custom  = false;
			
			// Check if this is likely a custom theme (not from wp.org).
			$theme_uri = $theme->get( 'ThemeURI' );
			if ( empty( $theme_uri ) || strpos( $theme_uri, 'wordpress.org' ) === false ) {
				$is_custom = true;
			}
			
			// If it's a popular third-party theme, recommend child theme.
			$popular_themes = array( 'twentytwentyfour', 'twentytwentythree', 'twentytwentytwo', 'astra', 'hello-elementor', 'kadence', 'generatepress', 'oceanwp' );
			$theme_slug     = $theme->get_stylesheet();
			
			if ( in_array( $theme_slug, $popular_themes, true ) ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => sprintf(
						/* translators: %s: theme name */
						__( 'You are using %s directly. Consider creating a child theme to preserve customizations during theme updates.', 'wpshadow' ),
						$theme->get( 'Name' )
					),
					'severity'     => 'low',
					'threat_level' => 30,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/child-theme-structure',
					'context'      => array(
						'theme_slug' => $theme_slug,
						'theme_name' => $theme->get( 'Name' ),
					),
				);
			}
			
			return null; // Not using child theme but it's acceptable.
		}

		// Using a child theme - verify structure.
		$child_theme_dir = $theme->get_stylesheet_directory();
		$issues          = array();
		$warnings        = array();

		// Check for required style.css.
		$style_css = $child_theme_dir . '/style.css';
		if ( ! file_exists( $style_css ) ) {
			$issues[] = __( 'Missing style.css file', 'wpshadow' );
		} else {
			// Verify Template header.
			$style_contents = file_get_contents( $style_css );
			if ( strpos( $style_contents, 'Template:' ) === false ) {
				$issues[] = __( 'style.css missing "Template:" header', 'wpshadow' );
			}
		}

		// Check for functions.php.
		$functions_php = $child_theme_dir . '/functions.php';
		if ( ! file_exists( $functions_php ) ) {
			$warnings[] = __( 'Missing functions.php - recommended for proper stylesheet enqueuing', 'wpshadow' );
		} else {
			// Check if properly enqueuing parent stylesheet.
			$functions_contents = file_get_contents( $functions_php );
			if ( strpos( $functions_contents, 'wp_enqueue_style' ) === false &&
				 strpos( $functions_contents, 'get_stylesheet_uri' ) === false ) {
				$warnings[] = __( 'functions.php should enqueue parent and child stylesheets', 'wpshadow' );
			}
		}

		// Check if overriding parent files correctly.
		$parent_theme_dir = $theme->get_template_directory();
		$child_files      = glob( $child_theme_dir . '/*.php' );
		
		if ( ! empty( $child_files ) ) {
			foreach ( $child_files as $child_file ) {
				$filename = basename( $child_file );
				
				// Skip functions.php (that's expected).
				if ( 'functions.php' === $filename ) {
					continue;
				}
				
				// Check if parent file exists (proper override).
				$parent_file = $parent_theme_dir . '/' . $filename;
				if ( ! file_exists( $parent_file ) ) {
					$warnings[] = sprintf(
						/* translators: %s: filename */
						__( 'Child theme has %s but parent theme does not', 'wpshadow' ),
						$filename
					);
				}
			}
		}

		// Check screenshot.
		$has_screenshot = file_exists( $child_theme_dir . '/screenshot.png' ) ||
						  file_exists( $child_theme_dir . '/screenshot.jpg' );
		if ( ! $has_screenshot ) {
			$warnings[] = __( 'Missing screenshot.png/jpg - recommended for theme recognition', 'wpshadow' );
		}

		// If critical issues found.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Child theme structure has issues: ', 'wpshadow' ) . implode( ', ', $issues ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/child-theme-structure',
				'context'      => array(
					'child_theme_dir' => $child_theme_dir,
					'issues'          => $issues,
					'warnings'        => $warnings,
				),
			);
		}

		// If only warnings.
		if ( ! empty( $warnings ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Child theme structure has recommendations: ', 'wpshadow' ) . implode( ', ', $warnings ),
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/child-theme-structure',
				'context'      => array(
					'child_theme_dir' => $child_theme_dir,
					'warnings'        => $warnings,
				),
			);
		}

		return null; // Child theme is properly structured.
	}
}
