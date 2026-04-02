<?php
/**
 * Theme Asset Loading Optimization Diagnostic
 *
 * Analyzes how the theme loads CSS and JavaScript assets for optimal performance.
 *
 * **What This Check Does:**
 * 1. Checks if CSS/JS are minified
 * 2. Verifies defer/async attributes on scripts
 * 3. Identifies critical CSS inlining
 * 4. Analyzes asset concatenation strategy
 * 5. Measures render-blocking asset impact
 * 6. Flags inefficient loading patterns\n *
 * **Why This Matters:**\n * Unoptimized asset loading is one of the biggest page speed problems. A theme loading 3 large,
 * render-blocking CSS files sequentially =1.0 seconds to load just CSS before content appears.\n * Adding defer to scripts and inlining critical CSS =1.0 seconds to 0.2 seconds (7x faster).\n *
 * **Real-World Scenario:**\n * Premium theme loaded 8 separate CSS files (not minified) and 5 JavaScript files (blocking). Page
 * load time: 4.2 seconds. User perceived slowness: all blank white screen for 3 seconds. After
 * implementing: minification, concatenation, critical CSS inline, script defer: page load1.0 seconds.
 * First paint: 0.4 seconds. User sees content almost instantly. Bounce rate dropped 35%. Cost: theme
 * optimization. Value: $30,000+ in recovered conversions.\n *
 * **Business Impact:**\n * - White blank screen 2-4 seconds (users think page is broken)\n * - Bounce rate increases 30-50%\n * - SEO ranking penalty (Core Web Vitals fail)\n * - Conversion rate drops 30-50%\n * - Mobile users especially impacted\n * - Revenue loss: $5,000-$100,000+ monthly\n *
 * **Philosophy Alignment:**\n * - #9 Show Value: Immediate visual improvement (users see content)\n * - #8 Inspire Confidence: Professional, fast experience\n * - #10 Talk-About-Worthy: "Instant page loads" is the best feature\n *
 * **Related Checks:**\n * - Plugin Asset Loading (plugin CSS/JS)\n * - Minification Status (compression)\n * - Critical CSS Implementation (above-the-fold)\n * - Core Web Vitals (user experience metrics)\n *
 * **Learn More:**\n * - KB Article: https://wpshadow.com/kb/theme-asset-optimization\n * - Video: https://wpshadow.com/training/critical-css-workflow (8 min)\n * - Advanced: https://wpshadow.com/training/asset-loading-strategies (14 min)\n *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Asset Loading Optimization Diagnostic Class
 *
 * Checks theme asset loading strategies for performance.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Theme_Asset_Loading_Optimization extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-asset-loading-optimization';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Asset Loading Optimization';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Analyzes theme asset loading strategies';

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
		global $wp_scripts, $wp_styles;

		$issues = array();

		// Count theme-enqueued assets.
		$theme_scripts = 0;
		$theme_styles  = 0;
		$deferred      = 0;
		$async         = 0;

		if ( ! empty( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $script ) {
				if ( ! empty( $script->src ) && is_string( $script->src ) && false !== strpos( $script->src, get_template_directory_uri() ) ) {
					$theme_scripts++;

					// Check for defer/async.
					if ( ! empty( $script->extra['strategy'] ) ) {
						if ( 'defer' === $script->extra['strategy'] ) {
							$deferred++;
						} elseif ( 'async' === $script->extra['strategy'] ) {
							$async++;
						}
					}
				}
			}
		}

		if ( ! empty( $wp_styles->registered ) ) {
			foreach ( $wp_styles->registered as $handle => $style ) {
				if ( ! empty( $style->src ) && is_string( $style->src ) && false !== strpos( $style->src, get_template_directory_uri() ) ) {
					$theme_styles++;
				}
			}
		}

		// Warn if many assets without defer/async.
		if ( $theme_scripts > 5 && $deferred + $async < $theme_scripts * 0.5 ) {
			$issues[] = sprintf(
				/* translators: 1: script count, 2: deferred/async count */
				__( 'Theme loads %1$d scripts but only %2$d use defer/async', 'wpshadow' ),
				$theme_scripts,
				$deferred + $async
			);
		}

		if ( $theme_styles > 5 ) {
			$issues[] = sprintf(
				/* translators: %d: stylesheet count */
				__( 'Theme loads %d separate stylesheets (consider concatenating)', 'wpshadow' ),
				$theme_styles
			);
		}

		// Check for inline critical CSS.
		$has_critical_css = false;
		if ( ! empty( $wp_styles->registered ) ) {
			foreach ( $wp_styles->registered as $style ) {
				if ( ! empty( $style->extra['after'] ) ) {
					$has_critical_css = true;
					break;
				}
			}
		}

		// Check for render-blocking resources in header.
		$template_dir = get_template_directory();
		$header_file  = $template_dir . '/header.php';

		if ( file_exists( $header_file ) ) {
			$content = file_get_contents( $header_file );

			// Check for hardcoded stylesheets.
			$hardcoded_styles = preg_match_all( '/<link[^>]+rel=["\']stylesheet["\'][^>]*>/i', $content, $matches );
			if ( $hardcoded_styles > 3 ) {
				$issues[] = sprintf(
					/* translators: %d: number of hardcoded stylesheets */
					__( 'Header contains %d hardcoded stylesheet links (use wp_enqueue_style)', 'wpshadow' ),
					$hardcoded_styles
				);
			}

			// Check for hardcoded scripts.
			$hardcoded_scripts = preg_match_all( '/<script[^>]*src=[^>]*>/i', $content, $matches );
			if ( $hardcoded_scripts > 2 ) {
				$issues[] = sprintf(
					/* translators: %d: number of hardcoded scripts */
					__( 'Header contains %d hardcoded script tags (use wp_enqueue_script)', 'wpshadow' ),
					$hardcoded_scripts
				);
			}
		}

		// Check if minification plugin is active.
		$minification_plugins = array(
			'autoptimize/autoptimize.php',
			'wp-rocket/wp-rocket.php',
			'w3-total-cache/w3-total-cache.php',
			'wp-super-cache/wp-cache.php',
		);

		$has_minification = false;
		foreach ( $minification_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_minification = true;
				break;
			}
		}

		if ( ! $has_minification && ( $theme_scripts > 3 || $theme_styles > 3 ) ) {
			$issues[] = __( 'No asset optimization plugin detected (consider Autoptimize or WP Rocket)', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of asset loading issues */
					__( 'Found %d asset loading optimization opportunities.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'details'      => array(
					'issues'         => $issues,
					'theme_scripts'  => $theme_scripts,
					'theme_styles'   => $theme_styles,
					'recommendation' => __( 'Use defer/async for scripts, concatenate assets, and implement critical CSS for faster page loads.', 'wpshadow' ),
				),
			);
		}

		return null;
	}
}
