<?php
/**
 * Theme Frontend Performance Diagnostic
 *
 * Analyzes theme's frontend performance and loading speed.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5049.1230
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Frontend Performance Diagnostic Class
 *
 * Checks theme for performance issues affecting page load times.
 *
 * @since 1.5049.1230
 */
class Diagnostic_Theme_Frontend_Performance extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-frontend-performance';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Frontend Performance';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Analyzes theme frontend loading performance';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.5049.1230
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wp_scripts, $wp_styles;

		$theme = wp_get_theme();
		$issues = array();

		// Count theme-enqueued assets.
		$theme_slug = get_stylesheet();
		$theme_scripts = 0;
		$theme_styles = 0;

		if ( isset( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $script ) {
				if ( isset( $script->src ) && strpos( $script->src, '/themes/' . $theme_slug ) !== false ) {
					$theme_scripts++;
				}
			}
		}

		if ( isset( $wp_styles->registered ) ) {
			foreach ( $wp_styles->registered as $handle => $style ) {
				if ( isset( $style->src ) && strpos( $style->src, '/themes/' . $theme_slug ) !== false ) {
					$theme_styles++;
				}
			}
		}

		// Alert if excessive assets.
		if ( $theme_scripts > 10 ) {
			$issues[] = sprintf(
				/* translators: %d: number of scripts */
				__( 'Theme enqueues %d JavaScript files (consider bundling)', 'wpshadow' ),
				$theme_scripts
			);
		}

		if ( $theme_styles > 5 ) {
			$issues[] = sprintf(
				/* translators: %d: number of stylesheets */
				__( 'Theme enqueues %d stylesheets (consider combining)', 'wpshadow' ),
				$theme_styles
			);
		}

		// Check for jQuery in theme (when not needed).
		if ( isset( $wp_scripts->queue ) && in_array( 'jquery', $wp_scripts->queue, true ) ) {
			$jquery_dependents = 0;
			foreach ( $wp_scripts->registered as $script ) {
				if ( isset( $script->deps ) && in_array( 'jquery', $script->deps, true ) ) {
					if ( isset( $script->src ) && strpos( $script->src, '/themes/' . $theme_slug ) !== false ) {
						$jquery_dependents++;
					}
				}
			}

			if ( $jquery_dependents > 3 ) {
				$issues[] = __( 'Theme heavily relies on jQuery (consider modern JavaScript)', 'wpshadow' );
			}
		}

		// Check for render-blocking resources.
		$render_blocking = 0;
		if ( isset( $wp_styles->registered ) ) {
			foreach ( $wp_styles->registered as $style ) {
				if ( isset( $style->src ) && 
					 strpos( $style->src, '/themes/' . $theme_slug ) !== false &&
					 ! isset( $style->extra['defer'] ) ) {
					$render_blocking++;
				}
			}
		}

		if ( $render_blocking > 3 ) {
			$issues[] = sprintf(
				/* translators: %d: number of render-blocking resources */
				__( '%d render-blocking stylesheets detected', 'wpshadow' ),
				$render_blocking
			);
		}

		// Check if theme uses async/defer for scripts.
		$has_async_defer = false;
		if ( isset( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $script ) {
				if ( isset( $script->extra['async'] ) || isset( $script->extra['defer'] ) ) {
					$has_async_defer = true;
					break;
				}
			}
		}

		if ( ! $has_async_defer && $theme_scripts > 3 ) {
			$issues[] = __( 'No scripts use async/defer loading', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Theme has frontend performance issues that may slow page loads', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'details'     => array(
					'theme'             => $theme->get( 'Name' ),
					'theme_scripts'     => $theme_scripts,
					'theme_styles'      => $theme_styles,
					'render_blocking'   => $render_blocking,
					'issues'            => $issues,
				),
				'kb_link'     => 'https://wpshadow.com/kb/theme-frontend-performance',
			);
		}

		return null;
	}
}
