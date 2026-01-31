<?php
/**
 * Render Blocking Resources Diagnostic
 *
 * Identifies CSS and JavaScript files that block page rendering,
 * causing delays in First Contentful Paint (FCP).
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
 * Diagnostic_Render_Blocking_Resources Class
 *
 * Detects CSS and JS that prevent page rendering.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Render_Blocking_Resources extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'render-blocking-resources';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Render Blocking Resources Analysis';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Identifies CSS and JavaScript blocking page rendering';

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
	 * @return array|null Finding array if render-blocking resources detected, null otherwise.
	 */
	public static function check() {
		global $wp_styles, $wp_scripts;

		$blocking_resources = array(
			'css' => 0,
			'js'  => 0,
		);

		// Count render-blocking CSS (all CSS blocks rendering)
		if ( $wp_styles && isset( $wp_styles->queue ) ) {
			$blocking_resources['css'] = count( $wp_styles->queue );
		}

		// Count render-blocking JS (sync scripts block rendering)
		if ( $wp_scripts && isset( $wp_scripts->queue ) ) {
			$blocking_count = 0;
			foreach ( $wp_scripts->queue as $handle ) {
				$script_obj = $wp_scripts->registered[ $handle ] ?? null;
				if ( $script_obj && ! isset( $script_obj->extra['async'] ) && ! isset( $script_obj->extra['defer'] ) ) {
					$blocking_count ++;
				}
			}
			$blocking_resources['js'] = $blocking_count;
		}

		$total_blocking = $blocking_resources['css'] + $blocking_resources['js'];

		if ( $total_blocking < 3 ) {
			return null; // Acceptable
		}

		$severity = ( $total_blocking > 10 ) ? 'high' : 'medium';
		$threat_level = ( $severity === 'high' ) ? 70 : 55;

		return array(
			'id'            => self::$slug,
			'title'         => self::$title,
			'description'   => sprintf(
				/* translators: %d: resource count, %d: CSS, %d: JS */
				__( 'Found %d render-blocking resources: %d CSS files, %d sync JavaScript files.', 'wpshadow' ),
				$total_blocking,
				$blocking_resources['css'],
				$blocking_resources['js']
			),
			'severity'      => $severity,
			'threat_level'  => $threat_level,
			'auto_fixable'  => false,
			'kb_link'       => 'https://wpshadow.com/kb/optimize-render-blocking',
			'family'        => self::$family,
			'meta'          => array(
				'render_blocking_css'       => $blocking_resources['css'],
				'render_blocking_js'        => $blocking_resources['js'],
				'total_blocking_resources'  => $total_blocking,
				'estimated_impact'          => sprintf(
					__( 'Each blocking resource adds 50-200ms to First Contentful Paint (FCP)' )
				),
				'potential_speedup'         => sprintf(
					__( 'Optimizing these could improve FCP by %d-%dms', 'wpshadow' ),
					$total_blocking * 50,
					$total_blocking * 200
				),
			),
			'details'       => array(
				'explanation'  => __( 'Render-blocking resources prevent browsers from displaying page content until the resource is downloaded and processed. CSS is always blocking, but JavaScript can be made async/defer.' ),
				'optimization_strategies' => array(
					'For CSS' => array(
						'Inline critical CSS' => __( 'Inline CSS needed for above-the-fold content' ),
						'Defer non-critical CSS' => __( 'Load secondary CSS asynchronously' ),
						'Minify and compress' => __( 'Reduce CSS file size by 50-70%' ),
						'Remove unused CSS' => __( 'Use tools like PurgeCSS' ),
					),
					'For JavaScript' => array(
						'Add async attribute' => __( 'Download in parallel, execute when ready' ),
						'Add defer attribute' => __( 'Execute after HTML parsing (safest)' ),
						'Code splitting' => __( 'Load only JS needed for current page' ),
						'Lazy loading' => __( 'Load JS only when interaction happens' ),
					),
				),
				'quick_optimizations' => array(
					__( 'Add async to: Google Analytics, Facebook Pixel, non-critical tracking' ),
					__( 'Add defer to: jQuery plugins, theme JS, most third-party scripts' ),
					__( 'Move critical CSS inline in <head>' ),
					__( 'Use plugin: Async JavaScript (NitroPack, LiteSpeed Cache, etc.)' ),
					__( 'Use plugin: Critical CSS Generator' ),
				),
				'tools'                => array(
					'Chrome DevTools' => 'Performance tab shows blocking resources',
					'Google PageSpeed Insights' => 'Identifies render-blocking resources',
					'Lighthouse' => 'Built into Chrome DevTools, shows opportunities',
					'WebPageTest' => 'Detailed waterfall showing blocking resources',
				),
			),
		);
	}
}
