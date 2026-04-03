<?php
/**
 * Critical Resources Preloaded Diagnostic
 *
 * Checks whether the site uses resource hints (preload/prefetch/dns-prefetch)
 * for critical assets to improve loading performance.
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
 * Diagnostic_Critical_Resources_Preloaded Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_Critical_Resources_Preloaded extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'critical-resources-preloaded';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Critical Resources Preloaded';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether the site uses resource hints such as preload or prefetch to load critical assets earlier and reduce page rendering delays.';

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
	 * Plugins that actively manage resource preloading or hints.
	 *
	 * @var array<string,string>
	 */
	private const PRELOAD_PLUGINS = array(
		'wp-rocket/wp-rocket.php'             => 'WP Rocket',
		'litespeed-cache/litespeed-cache.php' => 'LiteSpeed Cache',
		'jetpack-boost/jetpack-boost.php'     => 'Jetpack Boost',
		'autoptimize/autoptimize.php'         => 'Autoptimize',
		'nitropack/nitropack-plugin.php'      => 'NitroPack',
		'perfmatters/perfmatters.php'         => 'Perfmatters',
		'flying-press/flying-press.php'       => 'FlyingPress',
		'swift-performance/swift-performance.php' => 'Swift Performance',
	);

	/**
	 * Patterns that indicate a preload/prefetch link in PHP or HTML templates.
	 *
	 * @var string[]
	 */
	private const PRELOAD_PATTERNS = array(
		"rel='preload'",
		'rel="preload"',
		"rel='prefetch'",
		'rel="prefetch"',
		"rel='dns-prefetch'",
		'rel="dns-prefetch"',
		"rel='preconnect'",
		'rel="preconnect"',
		'wp_resource_hints',
	);

	/**
	 * Run the diagnostic check.
	 *
	 * First checks for active performance plugins that handle preloading.
	 * Then falls back to scanning theme template files for manual preload hints.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array if issue exists, null if healthy.
	 */
	public static function check() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		// Pass if a known performance/preload plugin is active.
		foreach ( self::PRELOAD_PLUGINS as $plugin_file => $name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				return null;
			}
		}

		// Scan theme templates for manual preload hints.
		$template_dirs = array_filter(
			array(
				get_stylesheet_directory(),
				get_template_directory(),
			),
			'is_dir'
		);
		$template_dirs = array_unique( $template_dirs );

		foreach ( $template_dirs as $dir ) {
			try {
				$iterator = new \RecursiveIteratorIterator(
					new \RecursiveDirectoryIterator( $dir, \RecursiveDirectoryIterator::SKIP_DOTS )
				);
				foreach ( $iterator as $file ) {
					$ext = strtolower( $file->getExtension() );
					if ( ! in_array( $ext, array( 'php', 'html' ), true ) ) {
						continue;
					}
					$contents = file_get_contents( $file->getPathname() );
					if ( false === $contents ) {
						continue;
					}
					foreach ( self::PRELOAD_PATTERNS as $pattern ) {
						if ( str_contains( $contents, $pattern ) ) {
							return null;
						}
					}
				}
			} catch ( \Exception $e ) {
				continue;
			}
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'No resource preloading or prefetching strategy was detected. Browsers cannot prioritise critical assets such as fonts, hero images, or key scripts, which delays First Contentful Paint and Largest Contentful Paint.', 'wpshadow' ),
			'severity'     => 'low',
			'threat_level' => 20,
			'kb_link'      => 'https://wpshadow.com/kb/critical-resources-preloaded?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'      => array(
				'fix' => __( 'Add resource hints via the wp_resource_hints filter or use a performance plugin such as WP Rocket or LiteSpeed Cache that includes built-in preload management. At minimum, add <link rel="preconnect"> for external font/CDN origins and <link rel="preload"> for your largest above-the-fold image.', 'wpshadow' ),
			),
		);
	}
}
