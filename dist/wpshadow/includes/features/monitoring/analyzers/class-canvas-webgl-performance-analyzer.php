<?php
declare(strict_types=1);

namespace WPShadow\Guardian;

/**
 * Canvas WebGL Performance Analyzer
 *
 * Monitors Canvas and WebGL usage to detect performance-impacting graphics features.
 * Identifies heavy graphics rendering that may slow down browsers.
 *
 * Philosophy: Show value (#9) - Optimize graphics performance.
 *
 * @package WPShadow
 * @subpackage Guardian
 * @since 0.6093.1200
 */
class Canvas_WebGL_Performance_Analyzer {

	/**
	 * Analyze Canvas/WebGL usage
	 *
	 * @return array Analysis results
	 */
	public static function analyze(): array {
		// Check cache first (hourly)
		$cached = \WPShadow\Core\Cache_Manager::get( 'canvas_webgl_performance', 'wpshadow_monitoring' );
		if ( $cached && is_array( $cached ) ) {
			return $cached;
		}

		$results = array(
			'has_canvas'         => false,
			'has_webgl'          => false,
			'canvas_scripts'     => array(),
			'estimated_impact'   => 'low',
			'graphics_libraries' => array(),
		);

		// Get enqueued scripts
		global $wp_scripts;

		if ( ! isset( $wp_scripts ) || ! ( $wp_scripts instanceof \WP_Scripts ) ) {
			\WPShadow\Core\Cache_Manager::set( 'canvas_webgl_performance', $results, HOUR_IN_SECONDS , 'wpshadow_monitoring');
			return $results;
		}

		// Known graphics libraries
		$graphics_libs = array(
			'three.js'   => 'Three.js (WebGL)',
			'babylon.js' => 'Babylon.js (WebGL)',
			'pixi.js'    => 'PixiJS (WebGL/Canvas)',
			'p5.js'      => 'p5.js (Canvas)',
			'fabric.js'  => 'Fabric.js (Canvas)',
			'konva.js'   => 'Konva (Canvas)',
			'chart.js'   => 'Chart.js (Canvas)',
			'd3.js'      => 'D3.js (SVG/Canvas)',
			'plotly.js'  => 'Plotly (WebGL)',
		);

		// Check scripts for graphics libraries
		$found_libs = array();
		foreach ( $wp_scripts->registered as $handle => $script ) {
			if ( ! is_string( $script->src ) || empty( $script->src ) ) {
				continue;
			}

			foreach ( $graphics_libs as $lib => $name ) {
				if ( isset( $script->src ) && is_string( $script->src ) && stripos( $script->src, $lib ) !== false ) {
					$found_libs[]                = $name;
					$results['canvas_scripts'][] = array(
						'handle'  => $handle,
						'library' => $name,
					);

					// Detect WebGL vs Canvas
					if ( strpos( $lib, 'three' ) !== false ||
						strpos( $lib, 'babylon' ) !== false ||
						strpos( $lib, 'plotly' ) !== false ) {
						$results['has_webgl'] = true;
					} else {
						$results['has_canvas'] = true;
					}
				}
			}
		}

		$results['graphics_libraries'] = array_unique( $found_libs );

		// Check content for canvas/webgl usage
		if ( ! $results['has_canvas'] && ! $results['has_webgl'] ) {
			$content_usage         = self::check_content_for_graphics();
			$results['has_canvas'] = $content_usage['has_canvas'];
			$results['has_webgl']  = $content_usage['has_webgl'];
		}

		// Estimate performance impact
		if ( $results['has_webgl'] ) {
			$results['estimated_impact'] = 'high';
		} elseif ( $results['has_canvas'] && count( $results['canvas_scripts'] ) > 2 ) {
			$results['estimated_impact'] = 'medium';
		} elseif ( $results['has_canvas'] ) {
			$results['estimated_impact'] = 'low';
		}

		// Cache for 1 hour
		\WPShadow\Core\Cache_Manager::set( 'canvas_webgl_performance', $results, HOUR_IN_SECONDS , 'wpshadow_monitoring');

		return $results;
	}

	/**
	 * Check content for canvas/webgl usage
	 *
	 * @return array Usage detection
	 */
	private static function check_content_for_graphics(): array {
		$usage = array(
			'has_canvas' => false,
			'has_webgl'  => false,
		);

		// Check recent posts for canvas/webgl tags
		$posts = get_posts(
			array(
				'posts_per_page' => 10,
				'post_type'      => 'any',
				'post_status'    => 'publish',
			)
		);

		foreach ( $posts as $post ) {
			$content = $post->post_content;

			// Check for canvas tags
			if ( strpos( $content, '<canvas' ) !== false ) {
				$usage['has_canvas'] = true;
			}

			// Check for WebGL-related code
			$webgl_patterns = array(
				'getContext(\'webgl\')',
				'getContext("webgl")',
				'getContext(\'webgl2\')',
				'getContext("webgl2")',
				'THREE.',
				'BABYLON.',
			);

			foreach ( $webgl_patterns as $pattern ) {
				if ( strpos( $content, $pattern ) !== false ) {
					$usage['has_webgl'] = true;
					break;
				}
			}

			if ( $usage['has_canvas'] && $usage['has_webgl'] ) {
				break;
			}
		}

		return $usage;
	}

	/**
	 * Get summary
	 *
	 * @return array Summary data
	 */
	public static function get_summary(): array {
		$results = \WPShadow\Core\Cache_Manager::get( 'canvas_webgl_performance', 'wpshadow_monitoring' );
		return is_array( $results ) ? $results : array(
			'has_canvas'       => false,
			'has_webgl'        => false,
			'estimated_impact' => 'low',
		);
	}

	/**
	 * Clear cached data
	 *
	 * @return void
	 */
	public static function clear_cache(): void {
		\WPShadow\Core\Cache_Manager::delete( 'canvas_webgl_performance', 'wpshadow_monitoring' );
	}
}
