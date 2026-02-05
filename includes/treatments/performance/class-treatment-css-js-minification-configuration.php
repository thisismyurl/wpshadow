<?php
/**
 * CSS/JS Minification Configuration Treatment
 *
 * Tests if CSS and JavaScript files are minified for performance.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.7034.1150
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CSS/JS Minification Configuration Treatment Class
 *
 * Validates that CSS and JavaScript assets are minified and
 * concatenated for optimal loading performance.
 *
 * @since 1.7034.1150
 */
class Treatment_CSS_JS_Minification_Configuration extends Treatment_Base {

	/**
	 * Treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'css-js-minification-configuration';

	/**
	 * Treatment title
	 *
	 * @var string
	 */
	protected static $title = 'CSS/JS Minification Configuration';

	/**
	 * Treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if CSS and JavaScript files are minified for performance';

	/**
	 * Treatment family
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * Tests if CSS/JS minification and concatenation is enabled
	 * via plugins or manual configuration.
	 *
	 * @since  1.7034.1150
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		// Check for optimization plugins.
		$optimization_plugins = array(
			'autoptimize/autoptimize.php'            => 'Autoptimize',
			'wp-rocket/wp-rocket.php'                => 'WP Rocket',
			'w3-total-cache/w3-total-cache.php'      => 'W3 Total Cache',
			'fast-velocity-minify/fvm.php'           => 'Fast Velocity Minify',
			'wp-optimize/wp-optimize.php'            => 'WP-Optimize',
			'asset-cleanup/wpacu.php'                => 'Asset CleanUp',
		);

		$active_optimization_plugins = array();
		foreach ( $optimization_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_optimization_plugins[] = $name;
			}
		}

		$has_optimization_plugin = ! empty( $active_optimization_plugins );

		// Check Autoptimize settings.
		$autoptimize_active = false;
		$autoptimize_settings = array();
		if ( is_plugin_active( 'autoptimize/autoptimize.php' ) ) {
			$autoptimize_active = true;
			$autoptimize_settings = array(
				'js_enabled'  => get_option( 'autoptimize_js' ),
				'css_enabled' => get_option( 'autoptimize_css' ),
			);
		}

		// Check WP Rocket settings.
		$wp_rocket_active = false;
		$wp_rocket_settings = array();
		if ( is_plugin_active( 'wp-rocket/wp-rocket.php' ) ) {
			$wp_rocket_active = true;
			$rocket_options = get_option( 'wp_rocket_settings' );
			$wp_rocket_settings = array(
				'minify_css'        => $rocket_options['minify_css'] ?? false,
				'minify_js'         => $rocket_options['minify_js'] ?? false,
				'minify_concatenate_js' => $rocket_options['minify_concatenate_js'] ?? false,
			);
		}

		// Check registered scripts and styles.
		global $wp_scripts, $wp_styles;

		$unminified_scripts = 0;
		$unminified_styles = 0;
		$total_scripts = 0;
		$total_styles = 0;

		// Check scripts.
		if ( isset( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $script ) {
				$total_scripts++;
				$src = $script->src ?? '';
				// Check if .min.js is in the src.
				if ( ! empty( $src ) && strpos( $src, '.min.js' ) === false && strpos( $src, '.js' ) !== false ) {
					$unminified_scripts++;
				}
			}
		}

		// Check styles.
		if ( isset( $wp_styles->registered ) ) {
			foreach ( $wp_styles->registered as $handle => $style ) {
				$total_styles++;
				$src = $style->src ?? '';
				// Check if .min.css is in the src.
				if ( ! empty( $src ) && strpos( $src, '.min.css' ) === false && strpos( $src, '.css' ) !== false ) {
					$unminified_styles++;
				}
			}
		}

		// Check combined file directory.
		$combined_dir = WP_CONTENT_DIR . '/cache/autoptimize';
		$has_combined_files = is_dir( $combined_dir );

		// Test homepage for minified assets.
		$home_url = home_url( '/' );
		$has_minified_in_html = false;

		if ( function_exists( 'wp_remote_get' ) ) {
			$response = wp_remote_get( $home_url, array( 'timeout' => 5 ) );
			if ( ! is_wp_error( $response ) ) {
				$body = wp_remote_retrieve_body( $response );
				$has_minified_in_html = ( strpos( $body, '.min.js' ) !== false ) || 
									   ( strpos( $body, '.min.css' ) !== false );
			}
		}

		// Check SCRIPT_DEBUG constant.
		$script_debug = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG;

		// Check for issues.
		$issues = array();

		// Issue 1: No optimization plugin.
		if ( ! $has_optimization_plugin ) {
			$issues[] = array(
				'type'        => 'no_optimization_plugin',
				'description' => __( 'No CSS/JS optimization plugin detected; assets are not minified', 'wpshadow' ),
			);
		}

		// Issue 2: Optimization plugin installed but not configured.
		if ( $autoptimize_active && ! $autoptimize_settings['js_enabled'] && ! $autoptimize_settings['css_enabled'] ) {
			$issues[] = array(
				'type'        => 'autoptimize_not_configured',
				'description' => __( 'Autoptimize installed but CSS/JS optimization not enabled', 'wpshadow' ),
			);
		}

		// Issue 3: Many unminified scripts.
		if ( $unminified_scripts > 5 ) {
			$issues[] = array(
				'type'        => 'unminified_scripts',
				'description' => sprintf(
					/* translators: 1: unminified count, 2: total count */
					__( '%1$d of %2$d scripts are not minified', 'wpshadow' ),
					$unminified_scripts,
					$total_scripts
				),
			);
		}

		// Issue 4: Many unminified styles.
		if ( $unminified_styles > 5 ) {
			$issues[] = array(
				'type'        => 'unminified_styles',
				'description' => sprintf(
					/* translators: 1: unminified count, 2: total count */
					__( '%1$d of %2$d stylesheets are not minified', 'wpshadow' ),
					$unminified_styles,
					$total_styles
				),
			);
		}

		// Issue 5: SCRIPT_DEBUG enabled in production.
		if ( $script_debug ) {
			$issues[] = array(
				'type'        => 'script_debug_enabled',
				'description' => __( 'SCRIPT_DEBUG is enabled; WordPress serves unminified core assets', 'wpshadow' ),
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'CSS and JavaScript files are not minified, which increases page load times and bandwidth usage', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/css-js-minification-configuration',
				'details'      => array(
					'has_optimization_plugin'  => $has_optimization_plugin,
					'active_plugins'           => $active_optimization_plugins,
					'autoptimize_active'       => $autoptimize_active,
					'autoptimize_settings'     => $autoptimize_settings,
					'wp_rocket_active'         => $wp_rocket_active,
					'wp_rocket_settings'       => $wp_rocket_settings,
					'total_scripts'            => $total_scripts,
					'unminified_scripts'       => $unminified_scripts,
					'total_styles'             => $total_styles,
					'unminified_styles'        => $unminified_styles,
					'has_combined_files'       => $has_combined_files,
					'has_minified_in_html'     => $has_minified_in_html,
					'script_debug_enabled'     => $script_debug,
					'issues_detected'          => $issues,
					'recommendation'           => __( 'Install Autoptimize or enable minification in WP Rocket', 'wpshadow' ),
					'performance_improvement'  => '30-50% reduction in CSS/JS file size',
					'optimization_techniques'  => array(
						'Minification'     => 'Remove whitespace, comments, shorten variables',
						'Concatenation'    => 'Combine multiple files into one',
						'Compression'      => 'Gzip or Brotli compression',
						'Defer Loading'    => 'Load non-critical JS after page render',
						'Async Loading'    => 'Load JS without blocking HTML parsing',
						'Critical CSS'     => 'Inline above-fold CSS',
					),
					'recommended_settings'     => array(
						'Minify CSS'       => 'Always enabled',
						'Minify JS'        => 'Always enabled',
						'Concatenate'      => 'Enabled (test for conflicts)',
						'Defer JS'         => 'Enabled for non-critical scripts',
						'Critical CSS'     => 'Extract and inline for faster FCP',
					),
				),
			);
		}

		return null;
	}
}
