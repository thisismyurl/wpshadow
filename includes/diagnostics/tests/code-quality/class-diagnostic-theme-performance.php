<?php
/**
 * Theme Performance and Optimization
 *
 * Validates theme performance and optimization.
 *
 * @since 0.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Theme_Performance Class
 *
 * Checks theme performance and optimization.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Theme_Performance extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-performance';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Performance';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates theme performance and optimization';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'theme-quality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Pattern 1: Theme loading too many stylesheets
		global $wp_styles;

		if ( isset( $wp_styles ) ) {
			$enqueued_styles = count( $wp_styles->queue );

			if ( $enqueued_styles > 20 ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => __( 'Theme loading excessive stylesheets', 'wpshadow' ),
					'severity'     => 'medium',
					'threat_level' => 50,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/theme-performance?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
					'details'      => array(
						'issue'                   => 'excessive_stylesheets',
						'stylesheet_count'        => $enqueued_styles,
						'message'                 => sprintf(
							/* translators: %d: count */
							__( '%d stylesheets loaded (recommended: 5-10)', 'wpshadow' ),
							$enqueued_styles
						),
						'performance_impact'      => __( 'Each stylesheet adds 50-300ms to page load', 'wpshadow' ),
						'optimization_strategies' => array(
							'Combine stylesheets' => 'Merge into 1-2 files',
							'Remove unused CSS'   => 'PurgeCSS, UnCSS',
							'Inline critical CSS' => 'Speed up rendering',
							'Defer non-critical'  => 'Load after page display',
						),
						'combining_stylesheets'   => "// Remove individual enqueues, combine into one
wp_enqueue_style('my-theme-combined', get_template_directory_uri() . '/dist/styles.css');

// Or use style concatenation in wp_head
add_action('wp_print_styles', function() {
	// Check what's enqueued
	global \$wp_styles;
	foreach (\$wp_styles->queue as \$style) {
		if (strpos(\$style, 'plugin') !== false) {
			// Combine multiple plugin styles
		}
	}
});",
						'removing_unused_css'     => "// Don't load CSS on non-necessary pages
if (is_admin()) {
	wp_dequeue_style('plugin-bootstrap');
	wp_dequeue_style('plugin-fontawesome');
}",
						'critical_css'            => "// Inline critical CSS to speed up first paint
echo '<style>';
include get_template_directory() . '/dist/critical.css';
echo '</style>';",
						'measuring_improvement'   => array(
							'Before: 15 stylesheets, 3.5s load',
							'After combining: 3 stylesheets,1.0s load',
							'65% improvement in load time',
						),
						'tools'                   => array(
							'Webpack'    => 'Bundle and combine CSS',
							'Gulp/Grunt' => 'CSS concatenation',
							'PurgeCSS'   => 'Remove unused styles',
							'Critical'   => 'Extract critical CSS',
						),
						'recommendation'          => __( 'Optimize theme stylesheet loading - target 5-10 total stylesheets', 'wpshadow' ),
					),
				);
			}
		}

		// Pattern 2: Theme loading too many scripts
		global $wp_scripts;

		if ( isset( $wp_scripts ) ) {
			$enqueued_scripts = count( $wp_scripts->queue );

			if ( $enqueued_scripts > 25 ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => __( 'Theme loading excessive scripts', 'wpshadow' ),
					'severity'     => 'medium',
					'threat_level' => 55,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/theme-performance?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
					'details'      => array(
						'issue'                   => 'excessive_scripts',
						'script_count'            => $enqueued_scripts,
						'message'                 => sprintf(
							/* translators: %d: count */
							__( '%d scripts loaded (recommended: 8-15)', 'wpshadow' ),
							$enqueued_scripts
						),
						'performance_impact'      => __( 'Each script adds 100-500ms to execution time', 'wpshadow' ),
						'optimization_strategies' => array(
							'Defer script loading' => 'Load after page renders',
							'Async loading'        => 'Don\'t block rendering',
							'Lazy load'            => 'Load on demand',
							'Combine scripts'      => 'Single bundle',
						),
						'defer_scripts'           => "wp_enqueue_script('my-theme-js',
	get_template_directory_uri() . '/dist/app.js',
	array(),
	'1.0',
	true // Load in footer
);",
						'async_loading'           => "// Add async attribute
add_filter('script_loader_tag', function(\$tag, \$handle) {
	if ('my-analytics' === \$handle) {
		return str_replace(' src', ' async=\"async\" src', \$tag);
	}
	return \$tag;
}, 10, 2);",
						'lazy_loading'            => "// Load scripts on user interaction
document.addEventListener('click', function() {
	if (!window.heavyScript) {
		var script = document.createElement('script');
		script.src = '/path/to/heavy-script.js';
		document.head.appendChild(script);
	}
});",
						'bundling'                => array(
							'Webpack'       => 'Bundle and minify',
							'Rollup'        => 'ES module bundling',
							'Gulp/Grunt'    => 'Task runners',
							'Build process' => 'Optimize before deploy',
						),
						'measuring'               => array(
							'Before: 30 scripts, 5.5s TTI',
							'After deferring: 15 scripts, 2.1s TTI',
							'62% improvement',
						),
						'recommendation'          => __( 'Optimize theme script loading - target 8-15 total scripts', 'wpshadow' ),
					),
				);
			}
		}

		// Pattern 3: Theme not minified
		$theme_dir  = get_template_directory();
		$theme_file = $theme_dir . '/style.css';

		if ( file_exists( $theme_file ) ) {
			$size_normal = filesize( $theme_file );

			// Check if .min.css exists
			$min_file = str_replace( '.css', '.min.css', $theme_file );

			if ( ! file_exists( $min_file ) && $size_normal > 50000 ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => __( 'Theme not using minified assets', 'wpshadow' ),
					'severity'     => 'low',
					'threat_level' => 35,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/theme-performance?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
					'details'      => array(
						'issue'                  => 'not_minified',
						'file_size'              => size_format( $size_normal ),
						'message'                => __( 'Theme assets not minified', 'wpshadow' ),
						'minification_benefits'  => array(
							'Remove unnecessary characters',
							'Reduce file size by 30-70%',
							'Faster download and parse',
							'Improved page speed',
						),
						'minifiable_assets'      => array(
							'CSS'        => 'Remove whitespace, shorten names',
							'JavaScript' => 'Remove comments, compress',
							'HTML'       => 'Remove unused attributes',
							'JSON'       => 'Single line format',
						),
						'size_reduction'         => '// Example minification results
Original style.css:  125 KB
Minified style.min.css: 45 KB
Reduction: 64%

Original app.js: 250 KB
Minified app.min.js: 75 KB
Reduction: 70%',
						'minification_tools'     => array(
							'CSSNano'      => 'CSS minification',
							'Terser'       => 'JavaScript minification',
							'HTMLMinifier' => 'HTML minification',
							'Webpack'      => 'Built-in minification',
							'Gulp/Grunt'   => 'Task-based minification',
						),
						'gulp_example'           => "// gulpfile.js
const gulp = require('gulp');
const minify = require('gulp-minify');

gulp.task('minify', () => {
	gulp.src('src/**/*.css')
		.pipe(minify({cssmin: {}}))
		.pipe(gulp.dest('dist'));
});",
						'webpack_config'         => "// webpack.config.js
module.exports = {
	mode: 'production',
	entry: './src/index.js',
	output: {
		filename: '[name].min.js',
		path: __dirname + '/dist',
	},
	// Automatically minifies in production mode
};",
						'enabling_in_wordpress'  => "// In functions.php - use minified in production
\$debug = (defined('WP_DEBUG') && WP_DEBUG);
\$ext = \$debug ? '.css' : '.min.css';

wp_enqueue_style('my-theme',
	get_template_directory_uri() . '/style' . \$ext
);",
						'gzip_with_minification' => __( 'Combine minification with gzip compression for maximum reduction', 'wpshadow' ),
						'source_maps'            => __( 'Use source maps for debugging minified code', 'wpshadow' ),
						'recommendation'         => __( 'Minify theme CSS and JavaScript files', 'wpshadow' ),
					),
				);
			}
		}

		// Pattern 4: Theme not caching output
		return null;
	}
}
