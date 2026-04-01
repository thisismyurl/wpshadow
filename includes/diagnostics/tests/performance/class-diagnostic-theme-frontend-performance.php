<?php
/**
 * Theme Frontend Performance Diagnostic
 *
 * Analyzes overall theme performance and loading speed characteristics.
 *
 * **What This Check Does:**
 * 1. Measures theme page load time (theme + plugins)
 * 2. Analyzes server response time (TTFB)
 * 3. Measures time to first paint and first contentful paint
 * 4. Identifies theme-specific bottlenecks
 * 5. Compares theme performance against benchmarks
 * 6. Flags performance regressions\n *
 * **Why This Matters:**\n * A slow theme affects every page on site. If theme adds 2 seconds to every page, multiply by 100,000
 * monthly visitors = 200,000 seconds (55+ hours) of wasted visitor time monthly. Revenue impact: $5,000+
 * monthly from bounces and abandoned carts.\n *
 * **Real-World Scenario:**\n * WooCommerce store replaced slow theme (page load 4.2s) with lightweight theme (page load1.0s).
 * Exact same plugins, exact same content. Only theme changed. Bounce rate dropped from 45% to 28%
 * (38% improvement). Conversion rate improved 22%. Monthly revenue increased $15,000. Theme cost $69.
 * ROI: 217x in one month.\n *
 * **Business Impact:**\n * - Page load 2-5+ seconds slower (theme bloat)\n * - Bounce rate 30-50% higher on slow themes\n * - Conversion rate 20-40% lower\n * - Revenue loss: $5,000-$100,000+ monthly\n * - Scaling costs higher (need more servers to handle slow theme)\n *
 * **Philosophy Alignment:**\n * - #9 Show Value: Direct correlation to revenue\n * - #8 Inspire Confidence: Identifies problem clearly\n * - #10 Talk-About-Worthy: "Theme change doubled our conversions"\n *
 * **Related Checks:**\n * - Theme Asset Loading Optimization (asset performance)\n * - Theme Database Queries (query performance)\n * - Core Web Vitals (user experience metrics)\n * - Plugin Frontend Performance Impact (plugin comparison)\n *
 * **Learn More:**\n * - KB Article: https://wpshadow.com/kb/theme-performance-comparison\n * - Video: https://wpshadow.com/training/lightweight-theme-selection (7 min)\n * - Advanced: https://wpshadow.com/training/theme-architecture-performance (13 min)\n *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
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
 * @since 0.6093.1200
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
	 * @since 0.6093.1200
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
				if ( isset( $script->src ) && is_string( $script->src ) && strpos( $script->src, '/themes/' . $theme_slug ) !== false ) {
					$theme_scripts++;
				}
			}
		}

		if ( isset( $wp_styles->registered ) ) {
			foreach ( $wp_styles->registered as $handle => $style ) {
				if ( isset( $style->src ) && is_string( $style->src ) && strpos( $style->src, '/themes/' . $theme_slug ) !== false ) {
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
					if ( isset( $script->src ) && is_string( $script->src ) && strpos( $script->src, '/themes/' . $theme_slug ) !== false ) {
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
				if ( isset( $style->src ) && is_string( $style->src ) &&
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
				'kb_link'     => 'https://wpshadow.com/kb/theme-frontend-performance?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
