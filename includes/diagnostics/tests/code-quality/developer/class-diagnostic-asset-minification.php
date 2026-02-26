<?php
/**
 * Asset Minification Diagnostic
 *
 * Checks if CSS/JS assets are minified and properly cached.
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
 * Asset Minification Diagnostic Class
 *
 * Verifies that CSS and JavaScript assets are minified and properly
 * cached for optimal performance.
 *
 * @since 1.6035.1300
 */
class Diagnostic_Asset_Minification extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'asset-minification';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Asset Minification';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if CSS/JS assets are minified and properly cached';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'developer';

	/**
	 * Run the asset minification diagnostic check.
	 *
	 * @since  1.6035.1300
	 * @return array|null Finding array if minification issues detected, null otherwise.
	 */
	public static function check() {
		$issues    = array();
		$warnings  = array();
		$stats     = array();
		$theme     = wp_get_theme();
		$theme_dir = $theme->get_stylesheet_directory();

		// Check for minification plugins/settings.
		$minification_plugins = array(
			'wp-super-cache/wp-cache.php'         => 'WP Super Cache',
			'w3-total-cache/w3-total-cache.php'   => 'W3 Total Cache',
			'wp-fastest-cache/wpFastestCache.php' => 'WP Fastest Cache',
			'breeze/breeze.php'                   => 'Breeze',
			'autoptimize/autoptimize.php'         => 'Autoptimize',
			'flying-press/flying-press.php'       => 'Flying Press',
		);

		$has_minification_plugin    = false;
		$active_minification_plugin = null;

		foreach ( $minification_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_minification_plugin    = true;
				$active_minification_plugin = $name;
				break;
			}
		}

		$stats['has_minification_plugin'] = $has_minification_plugin;
		if ( $active_minification_plugin ) {
			$stats['active_plugin'] = $active_minification_plugin;
		}

		if ( ! $has_minification_plugin ) {
			$warnings[] = __( 'No CSS/JS minification plugin detected', 'wpshadow' );
		}

		// Check for unminified CSS files in theme.
		$css_files          = glob( $theme_dir . '/**/*.css', GLOB_RECURSIVE );
		$unminified_css     = array();
		$total_css_files    = 0;
		$minified_css_files = 0;

		foreach ( $css_files as $css_file ) {
			$filename = basename( $css_file );

			// Skip vendor, libraries, min files.
			if ( strpos( $css_file, '/vendor/' ) !== false ||
				strpos( $filename, '.min.css' ) !== false ) {
				continue;
			}

			++$total_css_files;
			$content = file_get_contents( $css_file );

			// Check if file looks minified (low newline count relative to size).
			$newline_ratio = substr_count( $content, "\n" ) / ( strlen( $content ) / 100 );

			if ( $newline_ratio > 0.5 && strlen( $content ) > 1024 ) {
				// Likely unminified.
				$unminified_css[] = $filename;
			} else {
				++$minified_css_files;
			}
		}

		$stats['total_css_files']    = $total_css_files;
		$stats['minified_css_files'] = $minified_css_files;

		if ( ! empty( $unminified_css ) ) {
			$warnings[] = sprintf(
				/* translators: %d: number of files */
				__( '%d unminified CSS files detected', 'wpshadow' ),
				count( $unminified_css )
			);
		}

		// Check for unminified JS files in theme.
		$js_files          = glob( $theme_dir . '/**/*.js', GLOB_RECURSIVE );
		$unminified_js     = array();
		$total_js_files    = 0;
		$minified_js_files = 0;

		foreach ( $js_files as $js_file ) {
			$filename = basename( $js_file );

			// Skip vendor, libraries, min files.
			if ( strpos( $js_file, '/vendor/' ) !== false ||
				strpos( $js_file, '/node_modules/' ) !== false ||
				strpos( $filename, '.min.js' ) !== false ) {
				continue;
			}

			++$total_js_files;
			$content = file_get_contents( $js_file );

			// Check if file looks minified.
			$newline_ratio = substr_count( $content, "\n" ) / ( strlen( $content ) / 100 );

			if ( $newline_ratio > 0.5 && strlen( $content ) > 2048 ) {
				// Likely unminified.
				$unminified_js[] = $filename;
			} else {
				++$minified_js_files;
			}
		}

		$stats['total_js_files']    = $total_js_files;
		$stats['minified_js_files'] = $minified_js_files;

		if ( ! empty( $unminified_js ) ) {
			$warnings[] = sprintf(
				/* translators: %d: number of files */
				__( '%d unminified JS files detected', 'wpshadow' ),
				count( $unminified_js )
			);
		}

		// Check for SCRIPT_DEBUG.
		$script_debug          = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG;
		$stats['script_debug'] = $script_debug;

		if ( ! $script_debug && ( ! empty( $unminified_css ) || ! empty( $unminified_js ) ) ) {
			$issues[] = __( 'SCRIPT_DEBUG disabled but unminified assets found - disable debug in production', 'wpshadow' );
		}

		// Check for version query strings on assets (cache busting).
		if ( ! defined( 'ASSET_VERSION' ) && ! defined( 'THEME_VERSION' ) ) {
			$warnings[] = __( 'No version constant defined for cache busting on assets', 'wpshadow' );
		}

		// Check wp_styles and wp_scripts globals.
		global $wp_styles, $wp_scripts;

		$unversioned_styles = array();
		if ( isset( $wp_styles->registered ) ) {
			foreach ( $wp_styles->registered as $handle => $style ) {
				if ( empty( $style->ver ) || '' === $style->ver ) {
					$unversioned_styles[] = $handle;
				}
			}
		}

		$unversioned_scripts = array();
		if ( isset( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $script ) {
				if ( empty( $script->ver ) || '' === $script->ver ) {
					$unversioned_scripts[] = $handle;
				}
			}
		}

		if ( count( $unversioned_styles ) > 3 ) {
			$warnings[] = sprintf(
				/* translators: %d: number of styles */
				__( '%d stylesheets without version numbers (for cache busting)', 'wpshadow' ),
				count( $unversioned_styles )
			);
		}

		if ( count( $unversioned_scripts ) > 3 ) {
			$warnings[] = sprintf(
				/* translators: %d: number of scripts */
				__( '%d scripts without version numbers (for cache busting)', 'wpshadow' ),
				count( $unversioned_scripts )
			);
		}

		// Check for font loading optimization.
		$stylesheet_file = $theme_dir . '/style.css';
		if ( file_exists( $stylesheet_file ) ) {
			$css_content = file_get_contents( $stylesheet_file );

			// Check for @import fonts (should use preconnect instead).
			if ( preg_match( '/@import.*fonts\.googleapis/', $css_content ) ) {
				$warnings[] = __( 'Using @import for Google Fonts - consider preconnect for performance', 'wpshadow' );
			}
		}

		// Check functions.php for proper enqueuing.
		$functions_file = $theme_dir . '/functions.php';
		if ( file_exists( $functions_file ) ) {
			$functions_content = file_get_contents( $functions_file );

			// Check if enqueuing properly.
			if ( strpos( $functions_content, 'wp_enqueue_style' ) === false &&
				strpos( $functions_content, 'wp_enqueue_script' ) === false ) {
				$warnings[] = __( 'No proper asset enqueuing found in functions.php', 'wpshadow' );
			}
		}

		// If critical issues found.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Asset minification has critical issues: ', 'wpshadow' ) . implode( ', ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/asset-minification',
				'context'      => array(
					'stats'                => $stats,
					'unminified_css_count' => count( $unminified_css ),
					'unminified_js_count'  => count( $unminified_js ),
					'unversioned_styles'   => count( $unversioned_styles ),
					'unversioned_scripts'  => count( $unversioned_scripts ),
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
				'description'  => __( 'Asset minification has recommendations: ', 'wpshadow' ) . implode( ', ', $warnings ),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/asset-minification',
				'context'      => array(
					'stats'                => $stats,
					'unminified_css_count' => count( $unminified_css ),
					'unminified_js_count'  => count( $unminified_js ),
					'unversioned_styles'   => count( $unversioned_styles ),
					'unversioned_scripts'  => count( $unversioned_scripts ),
					'warnings'             => $warnings,
				),
			);
		}

		return null; // Assets are properly minified.
	}
}
