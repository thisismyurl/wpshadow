<?php
/**
 * CSS-in-JS Performance Diagnostic
 *
 * Analyzes CSS-in-JS implementation and performance impact.
 *
 * @since 1.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CSS-in-JS Performance Diagnostic
 *
 * Evaluates CSS-in-JS patterns and identifies performance issues.
 *
 * @since 1.6093.1200
 */
class Diagnostic_CSS_In_JS_Performance extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'css-in-js-performance';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'CSS-in-JS Performance';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Analyzes CSS-in-JS implementation and performance impact';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wp_scripts;

		if ( ! isset( $wp_scripts->registered ) ) {
			return null;
		}

		// Check for CSS-in-JS libraries
		$css_in_js_libraries = array(
			'styled-components' => 'Styled Components',
			'emotion'           => 'Emotion',
			'jss'               => 'JSS',
			'styled-jsx'        => 'Styled JSX',
			'goober'            => 'Goober',
			'linaria'           => 'Linaria',
		);

		$detected_libraries = array();
		$style_tag_count    = 0;

		foreach ( $wp_scripts->registered as $handle => $script ) {
			if ( ! $wp_scripts->query( $handle ) ) {
				continue;
			}

			// Check for CSS-in-JS library presence
			foreach ( $css_in_js_libraries as $library => $name ) {
			if ( is_string( $handle ) && strpos( $handle, $library ) !== false || 
			     ( isset( $script->src ) && is_string( $script->src ) && strpos( $script->src, $library ) !== false ) ) {
				$detected_libraries[ $library ] = $name;
			}
			}

			// Check inline styles injected via JavaScript
			if ( isset( $script->extra['after'] ) ) {
				$inline_code = is_array( $script->extra['after'] ) ? implode( '', $script->extra['after'] ) : $script->extra['after'];
				if ( strpos( $inline_code, 'style' ) !== false && strpos( $inline_code, 'createElement' ) !== false ) {
					$style_tag_count++;
				}
			}
		}

		// Check for runtime CSS injection (common CSS-in-JS pattern)
		$runtime_injection = $style_tag_count > 0;

		// Generate findings if CSS-in-JS detected with performance concerns
		if ( ! empty( $detected_libraries ) || $runtime_injection ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: comma-separated list of libraries */
					__( 'CSS-in-JS detected (%s). Runtime style injection may impact FCP and TTI. Consider extracting critical CSS.', 'wpshadow' ),
					! empty( $detected_libraries ) ? implode( ', ', $detected_libraries ) : __( 'runtime injection', 'wpshadow' )
				),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/css-in-js-performance',
				'meta'         => array(
					'detected_libraries'  => array_values( $detected_libraries ),
					'runtime_injection'   => $runtime_injection,
					'style_tag_count'     => $style_tag_count,
					'recommendation'      => 'Use static CSS extraction or zero-runtime libraries',
					'impact_estimate'     => '50-150ms FCP delay from runtime style generation',
					'alternatives'        => array(
						'Linaria (zero-runtime)',
						'Vanilla Extract (zero-runtime)',
						'CSS Modules (static)',
						'Tailwind CSS (utility-first)',
					),
					'optimization_tips' => array(
						'Extract critical CSS at build time',
						'Use server-side rendering for styles',
						'Implement style caching',
						'Consider zero-runtime alternatives',
					),
				),
			);
		}

		return null;
	}
}
