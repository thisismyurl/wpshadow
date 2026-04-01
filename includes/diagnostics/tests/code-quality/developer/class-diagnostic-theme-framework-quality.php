<?php
/**
 * Theme Framework Quality Diagnostic
 *
 * Checks if the active theme is built on a solid, reputable framework.
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
 * Theme Framework Quality Diagnostic Class
 *
 * Verifies that the theme is built on a quality framework with regular
 * updates, good documentation, and community support.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Theme_Framework_Quality extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-framework-quality';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Framework Quality';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if the active theme is built on a solid, reputable framework';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'developer';

	/**
	 * Run the theme framework quality diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if framework issues detected, null otherwise.
	 */
	public static function check() {
		$theme     = wp_get_theme();
		$theme_dir = $theme->get_stylesheet_directory();
		$issues    = array();
		$warnings  = array();
		$framework = null;

		// List of quality frameworks.
		$frameworks = array(
			'Underscores'       => array( 'underscores', '_s' ),
			'Sage'              => array( 'sage', 'roots' ),
			'Genesis'           => array( 'genesis', 'studiopress' ),
			'Divi'              => array( 'divi', 'elegant themes' ),
			'Avada'             => array( 'avada', 'themefusion' ),
			'Kadence'           => array( 'kadence' ),
			'OceanWP'           => array( 'oceanwp', 'ocean' ),
			'Neve'              => array( 'neve', 'themeisle' ),
			'Astra'             => array( 'astra', 'brainstormforce' ),
			'Twenty Twenty'     => array( 'twenty' ),
			'WordPress Default' => array( 'default' ),
		);

		// Check theme name and author for framework indicators.
		$theme_name   = strtolower( $theme->get( 'Name' ) ?? '' );
		$theme_author = strtolower( $theme->get( 'Author' ) ?? '' );
		$theme_uri    = strtolower( $theme->get( 'ThemeURI' ) ?? '' );

		foreach ( $frameworks as $framework_name => $keywords ) {
			foreach ( $keywords as $keyword ) {
				if ( strpos( $theme_name, $keyword ) !== false ||
					strpos( $theme_author, $keyword ) !== false ||
					strpos( $theme_uri, $keyword ) !== false ) {
					$framework = $framework_name;
					break 2;
				}
			}
		}

		// Check for framework-specific files/structures.
		if ( ! $framework ) {

			// Check for Sage indicators.
			if ( file_exists( $theme_dir . '/resources/views' ) ||
				file_exists( $theme_dir . '/resources/styles' ) ) {
				$framework = 'Sage';
			} elseif ( file_exists( $theme_dir . '/lib/genesis' ) ||
					function_exists( 'genesis' ) ) {
				$framework = 'Genesis';
			} elseif ( file_exists( $theme_dir . '/inc' ) &&
					file_exists( $theme_dir . '/functions.php' ) ) {
				$inc_files = glob( $theme_dir . '/inc/*.php' );
				if ( count( $inc_files ) > 3 ) {
					$framework = 'Underscores (or similar)';
				}
			}
		}

		if ( ! $framework ) {
			$issues[] = __( 'Theme not based on recognized framework - consider using established framework', 'wpshadow' );
		}

		// Check for proper documentation in theme.
		$readme_file = $theme_dir . '/README.md';
		if ( ! file_exists( $readme_file ) ) {
			$warnings[] = __( 'Theme README.md missing - documentation should be included', 'wpshadow' );
		}

		// Check for theme version.
		$theme_version = $theme->get( 'Version' );
		if ( empty( $theme_version ) || '1.0' === $theme_version ) {
			$warnings[] = __( 'Theme version not specified or default - keep version updated', 'wpshadow' );
		}

		// Check for proper code structure.
		$has_proper_structure = true;
		$expected_dirs        = array( 'functions.php', 'header.php', 'footer.php', 'index.php' );
		$missing_files        = array();

		foreach ( $expected_dirs as $file ) {
			if ( ! file_exists( $theme_dir . '/' . $file ) ) {
				$missing_files[]      = $file;
				$has_proper_structure = false;
			}
		}

		if ( ! $has_proper_structure ) {
			$issues[] = sprintf(
				/* translators: %s: missing file names */
				__( 'Missing required theme files: %s', 'wpshadow' ),
				implode( ', ', $missing_files )
			);
		}

		// Check for theme screenshot.
		$screenshot_files = glob( $theme_dir . '/screenshot.{png,jpg,jpeg,gif}', GLOB_BRACE );
		if ( empty( $screenshot_files ) ) {
			$warnings[] = __( 'Theme screenshot.png missing', 'wpshadow' );
		}

		// Check for style.css header completeness.
		$style_file = $theme_dir . '/style.css';
		if ( file_exists( $style_file ) ) {
			$style_content = file_get_contents( $style_file, false, null, 0, 500 );

			$required_headers = array( 'Theme Name', 'Author' );
			$missing_headers  = array();

			foreach ( $required_headers as $header ) {
				if ( strpos( $style_content, $header ) === false ) {
					$missing_headers[] = $header;
				}
			}

			if ( ! empty( $missing_headers ) ) {
				$issues[] = sprintf(
					/* translators: %s: header names */
					__( 'style.css missing headers: %s', 'wpshadow' ),
					implode( ', ', $missing_headers )
				);
			}
		}

		// Check for unminified assets in production.
		$css_files      = glob( $theme_dir . '/*.css' );
		$unminified_css = array();

		foreach ( $css_files as $css_file ) {
			if ( basename( $css_file ) !== 'style.css' &&
				strpos( basename( $css_file ), '.min.css' ) === false ) {
				// Check file size to estimate if minified.
				$content = file_get_contents( $css_file );
				if ( strlen( $content ) > 5000 && substr_count( $content, "\n" ) > 50 ) {
					$unminified_css[] = basename( $css_file );
				}
			}
		}

		if ( ! empty( $unminified_css ) ) {
			$warnings[] = sprintf(
				/* translators: %s: file names */
				__( 'Unminified CSS files detected: %s', 'wpshadow' ),
				implode( ', ', $unminified_css )
			);
		}

		// Check for functions.php practices.
		$functions_file = $theme_dir . '/functions.php';
		if ( file_exists( $functions_file ) ) {
			$functions_content = file_get_contents( $functions_file );

			// Check for namespace/organization.
			if ( strpos( $functions_content, 'namespace' ) === false ) {
				$warnings[] = __( 'functions.php not using namespace - consider organizing code', 'wpshadow' );
			}

			// Check for hooks usage.
			if ( strpos( $functions_content, 'add_action' ) === false &&
				strpos( $functions_content, 'add_filter' ) === false ) {
				$issues[] = __( 'functions.php not using WordPress hooks - critical for extensibility', 'wpshadow' );
			}
		}

		// If critical issues found.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Theme framework has critical quality issues: ', 'wpshadow' ) . implode( ', ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/theme-framework-quality?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'      => array(
					'theme_name'           => $theme->get( 'Name' ),
					'framework'            => $framework,
					'has_proper_structure' => $has_proper_structure,
					'issues'               => $issues,
					'warnings'             => $warnings,
				),
			);
		}

		// If only warnings.
		if ( ! empty( $warnings ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Theme framework has recommendations: ', 'wpshadow' ) . implode( ', ', $warnings ),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/theme-framework-quality?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'      => array(
					'theme_name' => $theme->get( 'Name' ),
					'framework'  => $framework,
					'warnings'   => $warnings,
				),
			);
		}

		return null; // Theme framework quality is good.
	}
}
