<?php
/**
 * CSS and JavaScript Minification Status Diagnostic
 *
 * Checks if CSS and JavaScript files are minified to reduce file sizes
 * and improve page load performance.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Minification_Status Class
 *
 * Detects whether CSS and JS are minified for better performance.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Minification_Status extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'minification-status';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'CSS/JavaScript Minification Status';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies CSS and JavaScript files are minified';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if minification issues detected, null otherwise.
	 */
	public static function check() {
		global $wp_styles, $wp_scripts;

		$unminified_files = array(
			'css' => array(),
			'js'  => array(),
		);

		// Check CSS files
		if ( $wp_styles && isset( $wp_styles->registered ) ) {
			foreach ( $wp_styles->registered as $handle => $style ) {
				if ( isset( $style->src ) && self::is_unminified( $style->src ) ) {
					$unminified_files['css'][] = $style->src;
				}
			}
		}

		// Check JavaScript files
		if ( $wp_scripts && isset( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $script ) {
				if ( isset( $script->src ) && self::is_unminified( $script->src ) ) {
					$unminified_files['js'][] = $script->src;
				}
			}
		}

		$total_unminified = count( $unminified_files['css'] ) + count( $unminified_files['js'] );

		if ( $total_unminified === 0 ) {
			return null; // All files minified
		}

		return array(
			'id'            => self::$slug,
			'title'         => self::$title,
			'description'   => sprintf(
				/* translators: %d: file count */
				__( 'Found %d unminified CSS/JavaScript files. Minification could reduce file sizes by 20-40%%.', 'wpshadow' ),
				$total_unminified
			),
			'severity'      => ( $total_unminified > 5 ) ? 'high' : 'medium',
			'threat_level'  => ( $total_unminified > 5 ) ? 60 : 45,
			'auto_fixable'  => false,
			'kb_link'       => 'https://wpshadow.com/kb/enable-minification',
			'family'        => self::$family,
			'meta'          => array(
				'unminified_css_files'  => count( $unminified_files['css'] ),
				'unminified_js_files'   => count( $unminified_files['js'] ),
				'total_unminified'      => $total_unminified,
				'estimated_savings'     => '20-40% reduction in CSS/JS file size',
				'potential_speedup'     => 'Up to 500ms faster page load',
			),
			'details'       => array(
				'explanation'  => __( 'Minification removes unnecessary characters (spaces, comments) from code without affecting functionality. Minified files are 20-40% smaller and load faster.' ),
				'solutions'    => array(
					'Option 1: Use Caching Plugin (Best)' => array(
						'NitroPack' => 'All-in-one: minification + compression + CDN',
						'WP Super Cache' => 'Free, built-in minification option',
						'WP Fastest Cache' => 'Affordable, easy setup',
						'LiteSpeed Cache' => 'Best for LiteSpeed hosting, free tier',
					),
					'Option 2: Use Minification Plugin' => array(
						'Autoptimize' => 'Free, lightweight, JavaScript + CSS minification',
						'WP Optimize' => 'Free tier includes minification',
						'Complianz' => 'Premium minification with compliance',
					),
					'Option 3: Manual (Theme/Plugin Development)' => array(
						'Webpack' => 'JavaScript bundling and minification',
						'Gulp/Grunt' => 'Build tools for CSS/JS optimization',
						'UglifyJS' => 'JavaScript minifier',
					),
				),
				'quick_setup'  => array(
					'Step 1' => __( 'Install Autoptimize or WP Super Cache plugin' ),
					'Step 2' => __( 'Activate and configure minification options' ),
					'Step 3' => __( 'Enable JavaScript minification' ),
					'Step 4' => __( 'Enable CSS minification' ),
					'Step 5' => __( 'Test site functionality (JavaScript errors sometimes occur)' ),
					'Step 6' => __( 'Monitor page speed improvement with PageSpeed Insights' ),
				),
				'unminified_examples' => array_slice( $unminified_files['css'], 0, 3 ) + array_slice( $unminified_files['js'], 0, 2 ),
				'performance_impact'  => array(
					__( 'JavaScript minification: 25-40% size reduction' ),
					__( 'CSS minification: 15-30% size reduction' ),
					__( 'Combined savings: 500KB-2MB typical site' ),
					__( 'Page load improvement: 200-500ms typical' ),
				),
			),
		);
	}

	/**
	 * Check if a file appears to be unminified.
	 *
	 * @since  1.2601.2148
	 * @param  string $url File URL.
	 * @return bool True if appears unminified.
	 */
	private static function is_unminified( $url ) {
		// Check for .min in filename (minified)
		if ( strpos( $url, '.min.css' ) !== false || strpos( $url, '.min.js' ) !== false ) {
			return false;
		}

		// Remove protocol and domain
		$path = wp_parse_url( $url, PHP_URL_PATH );

		// Files in specific directories are usually unminified
		$unminified_indicators = array(
			'src/' => true,
			'source/' => true,
			'dev/' => true,
			'unminified/' => true,
		);

		foreach ( $unminified_indicators as $indicator => $is_unminified ) {
			if ( strpos( $path, $indicator ) !== false ) {
				return $is_unminified;
			}
		}

		// Default: assume minified if not obvious
		return false;
	}
}
