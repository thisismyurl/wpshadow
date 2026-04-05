<?php
/**
 * CSS Minification Diagnostic
 *
 * Checks whether a CSS minification strategy is active to reduce payload
 * sizes and improve page load performance.
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
 * Diagnostic_Css_Minification Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_Css_Minification extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'css-minification';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'CSS Minification';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether CSS assets are being minified to reduce file sizes and improve page load times.';

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
	 * Plugins that provide CSS minification.
	 *
	 * @var array<string,string>
	 */
	private const CSS_MINIFY_PLUGINS = array(
		'wp-rocket/wp-rocket.php'                     => 'WP Rocket',
		'autoptimize/autoptimize.php'                 => 'Autoptimize',
		'litespeed-cache/litespeed-cache.php'         => 'LiteSpeed Cache',
		'w3-total-cache/w3-total-cache.php'           => 'W3 Total Cache',
		'wp-super-cache/wp-cache.php'                 => 'WP Super Cache',
		'sg-cachepress/sg-cachepress.php'             => 'SiteGround Optimizer',
		'nitropack/nitropack-plugin.php'              => 'NitroPack',
		'perfmatters/perfmatters.php'                 => 'Perfmatters',
		'fast-velocity-minify/fast-velocity-minify.php' => 'Fast Velocity Minify',
		'flying-press/flying-press.php'               => 'FlyingPress',
		'swift-performance/swift-performance.php'     => 'Swift Performance',
		'hummingbird-performance/wp-hummingbird.php'  => 'Hummingbird',
		'jetpack-boost/jetpack-boost.php'             => 'Jetpack Boost',
	);

	/**
	 * Run the diagnostic check.
	 *
	 * For WP Rocket and Autoptimize, verifies the specific CSS minification
	 * setting is enabled rather than just checking the plugin is installed.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array if issue exists, null if healthy.
	 */
	public static function check() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		foreach ( self::CSS_MINIFY_PLUGINS as $plugin_file => $name ) {
			if ( ! is_plugin_active( $plugin_file ) ) {
				continue;
			}

			// WP Rocket: verify minify_css is on.
			if ( 'wp-rocket/wp-rocket.php' === $plugin_file ) {
				$options = get_option( 'wp_rocket_settings', array() );
				if ( empty( $options['minify_css'] ) ) {
					continue;
				}
			}

			// Autoptimize: verify CSS optimisation is enabled.
			if ( 'autoptimize/autoptimize.php' === $plugin_file ) {
				if ( 'on' !== get_option( 'autoptimize_css', '' ) ) {
					continue;
				}
			}

			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'No CSS minification strategy was detected. Unminified stylesheets increase network transfer size and slow down page rendering.', 'wpshadow' ),
			'severity'     => 'low',
			'threat_level' => 20,
			'kb_link'      => '',
			'details'      => array(
				'fix' => __( 'Enable CSS minification through a caching or performance plugin. Autoptimize (free) provides straightforward CSS aggregation and minification. WP Rocket, LiteSpeed Cache, and Hummingbird also include this feature. After enabling, clear all caches and test your site for visual regressions.', 'wpshadow' ),
			),
		);
	}
}

