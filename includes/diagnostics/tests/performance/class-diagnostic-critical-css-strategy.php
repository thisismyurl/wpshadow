<?php
/**
 * Critical CSS Strategy Diagnostic
 *
 * Critical CSS is the subset of CSS required to render above-the-fold
 * content. Inlining it in the <head> eliminates a render-blocking request
 * and directly improves Largest Contentful Paint and First Contentful Paint.
 * This diagnostic checks whether a plugin or configuration handles this.
 *
 * @package WPShadow
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
 * Diagnostic_Critical_Css_Strategy Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_Critical_Css_Strategy extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'critical-css-strategy';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Critical CSS Strategy';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether a critical CSS strategy is in place to inline above-the-fold styles and eliminate render-blocking CSS requests.';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Confidence level of this diagnostic.
	 *
	 * @var string
	 */
	protected static $confidence = 'standard';

	/**
	 * Plugins that include critical CSS or CSS deferral features.
	 * Each entry maps plugin file → configuration check callback / null.
	 */
	private const CSS_PLUGINS = array(
		'wp-rocket/wp-rocket.php'                   => 'check_wp_rocket_css',
		'jetpack-boost/jetpack-boost.php'           => null,
		'jetpack/jetpack.php'                       => null,
		'autoptimize/autoptimize.php'               => 'check_autoptimize_css',
		'litespeed-cache/litespeed-cache.php'       => null,
		'nitropack/nitropack-plugin.php'            => null,
		'perfmatters/perfmatters.php'               => null,
		'ao-critical-css/ao-critical-css.php'       => null, // Autoptimize Critical CSS addon.
		'swift-performance-lite/swift-performance.php' => null,
		'swift-performance/swift-performance.php'   => null,
	);

	/**
	 * Run the diagnostic check.
	 *
	 * Checks for active CSS optimisation plugins. For WP Rocket and
	 * Autoptimize, it also verifies that the relevant CSS feature is
	 * enabled in settings, since the plugin alone does not guarantee
	 * critical CSS handling.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array if issue exists, null if healthy.
	 */
	public static function check() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		foreach ( self::CSS_PLUGINS as $plugin_file => $method ) {
			if ( ! is_plugin_active( $plugin_file ) ) {
				continue;
			}

			// Plugin active but has no extra config check — consider it sufficient.
			if ( null === $method ) {
				return null;
			}

			// Run the config validator if the method exists on this class.
			if ( method_exists( static::class, $method ) && static::$method() ) {
				return null;
			}
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'No critical CSS strategy was detected. Render-blocking CSS files are delaying the browser from painting above-the-fold content, negatively impacting First Contentful Paint and Largest Contentful Paint scores.', 'wpshadow' ),
			'severity'     => 'low',
			'threat_level' => 25,
			'kb_link'      => '',
			'details'      => array(
				'fix' => __( 'Install Jetpack Boost (free) and enable its Critical CSS feature. For more comprehensive optimisation, WP Rocket automatically handles critical CSS as part of its Remove Unused CSS feature. Alternatively, use Autoptimize combined with the AO Critical CSS addon.', 'wpshadow' ),
			),
		);
	}

	/**
	 * Check whether WP Rocket has CSS optimisation enabled.
	 *
	 * @return bool True if WP Rocket CSS features are active.
	 */
	private static function check_wp_rocket_css(): bool {
		$options = get_option( 'wp_rocket_settings', array() );
		return ! empty( $options['remove_unused_css'] ) || ! empty( $options['minify_css'] );
	}

	/**
	 * Check whether Autoptimize has CSS optimisation enabled.
	 *
	 * @return bool True if Autoptimize CSS aggregation is active.
	 */
	private static function check_autoptimize_css(): bool {
		$options = get_option( 'autoptimize_css', '' );
		return 'on' === $options;
	}
}
