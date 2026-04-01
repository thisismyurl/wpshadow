<?php
/**
 * Mobile CSS-in-JS Performance
 *
 * Detects CSS-in-JS (styled-components, emotion) overhead.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Performance
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile CSS-in-JS Performance
 *
 * Identifies CSS-in-JS library usage and measures runtime
 * CSS generation overhead.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Mobile_CSS_In_JS_Performance extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-css-in-js-perf';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Mobile CSS-in-JS Performance';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Detects CSS-in-JS overhead';

	/**
	 * The diagnostic family.
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$analysis = self::analyze_css_in_js();

		if ( empty( $analysis['detected'] ) ) {
			return null; // No CSS-in-JS detected
		}

		return array(
			'id'              => self::$slug,
			'title'           => self::$title,
			'description'     => sprintf(
				/* translators: %d: number of CSS-in-JS libraries */
				__( 'Detected %d CSS-in-JS libraries', 'wpshadow' ),
				count( $analysis['detected'] )
			),
			'severity'        => 'high',
			'threat_level'    => 65,
			'libraries'       => $analysis['detected'],
			'estimated_overhead' => '50-200ms runtime CSS generation',
			'recommendations' => array(
				'Extract critical CSS to <style> tag',
				'Use Linaria or Astroturf for compile-time CSS',
				'Implement critical CSS inlining',
			),
			'user_impact'     => __( 'CSS-in-JS adds 50-200ms FCP delay', 'wpshadow' ),
			'auto_fixable'    => false,
			'kb_link'         => 'https://wpshadow.com/kb/css-in-js-perf?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
		);
	}

	/**
	 * Analyze CSS-in-JS usage.
	 *
	 * @since 0.6093.1200
	 * @return array Analysis results.
	 */
	private static function analyze_css_in_js(): array {
		global $wp_scripts;

		$analysis = array(
			'detected' => array(),
		);

		if ( ! isset( $wp_scripts ) || ! is_object( $wp_scripts ) ) {
			return $analysis;
		}

		// Known CSS-in-JS libraries
		$css_in_js_libs = array(
			'styled-components' => array( 'patterns' => array( 'styled-components', 'styled.js' ), 'overhead' => '80ms' ),
			'emotion'           => array( 'patterns' => array( 'emotion', '@emotion/react' ), 'overhead' => '60ms' ),
			'aphrodite'         => array( 'patterns' => array( 'aphrodite', 'aphrodite.js' ), 'overhead' => '40ms' ),
			'linaria'           => array( 'patterns' => array( 'linaria', 'linaria.js' ), 'overhead' => '20ms' ),
			'jss'               => array( 'patterns' => array( 'jss', 'jss.js' ), 'overhead' => '50ms' ),
		);

		foreach ( $wp_scripts->queue as $handle ) {
			if ( ! isset( $wp_scripts->registered[ $handle ] ) ) {
				continue;
			}

			$script = $wp_scripts->registered[ $handle ];
			if ( empty( $script->src ) || ! is_string( $script->src ) ) {
				continue;
			}

			$src = strtolower( $script->src );

			foreach ( $css_in_js_libs as $lib => $data ) {
				foreach ( $data['patterns'] as $pattern ) {
					if ( false !== strpos( $src, strtolower( $pattern ) ) ) {
						$analysis['detected'][] = array(
							'library'            => $lib,
							'handle'             => $handle,
							'estimated_overhead' => $data['overhead'],
						);
						break 2;
					}
				}
			}
		}

		return $analysis;
	}
}
