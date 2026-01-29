<?php
/**
 * Render-Blocking Resource Identification Diagnostic
 *
 * Counts CSS/JS files blocking initial page render.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26029.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render-Blocking Resource Identification Class
 *
 * Tests render blocking.
 *
 * @since 1.26029.0000
 */
class Diagnostic_Render_Blocking_Resource_Identification extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'render-blocking-resource-identification';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Render-Blocking Resource Identification';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Counts CSS/JS files blocking initial page render';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26029.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$blocking_check = self::check_render_blocking();
		
		if ( $blocking_check['has_issues'] ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $blocking_check['issues'] ),
				'severity'     => 'medium',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/render-blocking-resource-identification',
				'meta'         => array(
					'blocking_css_count'  => $blocking_check['blocking_css_count'],
					'blocking_js_count'   => $blocking_check['blocking_js_count'],
					'total_blocking'      => $blocking_check['total_blocking'],
					'blocking_resources'  => $blocking_check['blocking_resources'],
					'recommendations'     => $blocking_check['recommendations'],
				),
			);
		}

		return null;
	}

	/**
	 * Check render blocking resources.
	 *
	 * @since  1.26029.0000
	 * @return array Check results.
	 */
	private static function check_render_blocking() {
		global $wp_scripts, $wp_styles;

		$check = array(
			'has_issues'          => false,
			'issues'              => array(),
			'blocking_css_count'  => 0,
			'blocking_js_count'   => 0,
			'total_blocking'      => 0,
			'blocking_resources'  => array(),
			'recommendations'     => array(),
		);

		// Check render-blocking CSS.
		if ( ! empty( $wp_styles->queue ) ) {
			foreach ( $wp_styles->queue as $handle ) {
				if ( ! isset( $wp_styles->registered[ $handle ] ) ) {
					continue;
				}

				$style = $wp_styles->registered[ $handle ];

				// CSS is render-blocking unless media is print/speech/specific.
				$is_blocking = true;
				
				if ( ! empty( $style->args ) && 'all' !== $style->args && 'screen' !== $style->args ) {
					// Has media query limiting blocking.
					$is_blocking = false;
				}

				if ( $is_blocking ) {
					$check['blocking_css_count']++;
					$check['blocking_resources'][] = array(
						'type'   => 'css',
						'handle' => $handle,
						'src'    => ! empty( $style->src ) ? basename( $style->src ) : 'inline',
					);
				}
			}
		}

		// Check render-blocking JavaScript.
		if ( ! empty( $wp_scripts->queue ) ) {
			foreach ( $wp_scripts->queue as $handle ) {
				if ( ! isset( $wp_scripts->registered[ $handle ] ) ) {
					continue;
				}

				$script = $wp_scripts->registered[ $handle ];

				// Check if in header AND lacks async/defer.
				$in_header = ! isset( $script->extra['group'] ) || 0 === $script->extra['group'];
				$is_async = isset( $script->extra['async'] );
				$is_defer = isset( $script->extra['defer'] );

				if ( $in_header && ! $is_async && ! $is_defer ) {
					$check['blocking_js_count']++;
					$check['blocking_resources'][] = array(
						'type'   => 'js',
						'handle' => $handle,
						'src'    => ! empty( $script->src ) ? basename( $script->src ) : 'inline',
					);
				}
			}
		}

		$check['total_blocking'] = $check['blocking_css_count'] + $check['blocking_js_count'];

		// Detect issues.
		if ( $check['blocking_css_count'] > 3 ) {
			$check['has_issues'] = true;
			$check['issues'][] = sprintf(
				/* translators: %d: number of blocking CSS files */
				__( '%d render-blocking CSS files detected', 'wpshadow' ),
				$check['blocking_css_count']
			);
			$check['recommendations'][] = __( 'Inline critical CSS and defer non-critical stylesheets', 'wpshadow' );
		}

		if ( $check['blocking_js_count'] > 2 ) {
			$check['has_issues'] = true;
			$check['issues'][] = sprintf(
				/* translators: %d: number of blocking JS files */
				__( '%d render-blocking JavaScript files in header', 'wpshadow' ),
				$check['blocking_js_count']
			);
			$check['recommendations'][] = __( 'Move JavaScript to footer or add async/defer attributes', 'wpshadow' );
		}

		if ( $check['total_blocking'] > 8 ) {
			$check['has_issues'] = true;
			$check['issues'][] = sprintf(
				/* translators: %d: total blocking resources */
				__( 'Total %d render-blocking resources (add 100-500ms each to initial render)', 'wpshadow' ),
				$check['total_blocking']
			);
			$check['recommendations'][] = __( 'Reduce number of blocking resources through bundling or lazy loading', 'wpshadow' );
		}

		return $check;
	}
}
