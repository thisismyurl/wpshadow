<?php
/**
 * Lazy Loading Not Implemented Diagnostic
 *
 * Detects images loading eagerly when lazy loading would improve
 * initial page render time and performance.
 *
 * @since   1.6028.1535
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Lazy_Loading Class
 *
 * Checks if lazy loading is implemented for below-fold images to
 * improve initial page load performance.
 *
 * @since 1.6028.1535
 */
class Diagnostic_Lazy_Loading extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'lazy-loading-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Lazy Loading Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects images loading eagerly when lazy loading would improve initial page render';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks if native lazy loading or plugin-based solution is active.
	 * WordPress 5.5+ enables native lazy loading by default.
	 *
	 * @since  1.6028.1535
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		global $wp_version;

		// WordPress 5.5+ has native lazy loading
		if ( version_compare( $wp_version, '5.5', '>=' ) ) {
			// Check if lazy loading is disabled
			if ( has_filter( 'wp_lazy_loading_enabled', '__return_false' ) ) {
				return array(
					'id'            => self::$slug,
					'title'         => self::$title,
					'description'   => __( 'Native lazy loading has been disabled on your site. Re-enabling it can improve page load performance by deferring below-fold images.', 'wpshadow' ),
					'severity'      => 'low',
					'threat_level'  => 30,
					'auto_fixable'  => false,
					'kb_link'       => 'https://wpshadow.com/kb/lazy-loading',
					'family'        => self::$family,
					'meta'          => array(
						'wp_version'        => $wp_version,
						'native_available'  => true,
						'manually_disabled' => true,
						'impact_level'      => __( 'Low - Performance optimization opportunity', 'wpshadow' ),
						'immediate_actions' => array(
							__( 'Remove filter disabling lazy loading', 'wpshadow' ),
							__( 'Test page load performance before/after', 'wpshadow' ),
						),
					),
					'details'       => array(
						'why_important'    => __( 'Lazy loading defers loading of below-fold images until they\'re about to enter the viewport. This reduces initial page weight by 30-50% on image-heavy pages, improving LCP (Largest Contentful Paint) and overall page speed.', 'wpshadow' ),
						'solution_options' => array(
							'Re-enable Native' => array(
								'description' => __( 'Remove wp_lazy_loading_enabled filter', 'wpshadow' ),
								'time'        => __( '5 minutes', 'wpshadow' ),
								'cost'        => __( 'Free', 'wpshadow' ),
								'difficulty'  => __( 'Easy', 'wpshadow' ),
								'steps'       => array(
									__( 'Find code adding filter: add_filter(\'wp_lazy_loading_enabled\', \'__return_false\')', 'wpshadow' ),
									__( 'Remove or comment out the filter', 'wpshadow' ),
									__( 'Clear cache', 'wpshadow' ),
									__( 'Test a page - images should have loading="lazy" attribute', 'wpshadow' ),
								),
							),
						),
					),
				);
			}

			return null; // Native lazy loading is active - good
		}

		// WordPress < 5.5 - check for lazy loading plugins
		$lazy_plugins = self::detect_lazy_loading_plugins();

		if ( ! empty( $lazy_plugins ) ) {
			return null; // Has lazy loading plugin
		}

		// No lazy loading detected
		$image_count = self::count_media_images();

		if ( $image_count < 20 ) {
			return null; // Not enough images to warrant lazy loading
		}

		return array(
			'id'            => self::$slug,
			'title'         => self::$title,
			'description'   => sprintf(
				/* translators: 1: WordPress version, 2: image count */
				__( 'Your site (WordPress %1$s) does not have lazy loading enabled for %2$d images. Upgrading to WordPress 5.5+ enables native lazy loading automatically.', 'wpshadow' ),
				$wp_version,
				$image_count
			),
			'severity'      => 'low',
			'threat_level'  => 35,
			'auto_fixable'  => false,
			'kb_link'       => 'https://wpshadow.com/kb/lazy-loading',
			'family'        => self::$family,
			'meta'          => array(
				'wp_version'        => $wp_version,
				'image_count'       => $image_count,
				'native_available'  => false,
				'impact_level'      => __( 'Low - Performance improvement opportunity', 'wpshadow' ),
				'immediate_actions' => array(
					__( 'Upgrade to WordPress 5.5+ for native lazy loading', 'wpshadow' ),
					__( 'Or install a lazy loading plugin', 'wpshadow' ),
					__( 'Test with PageSpeed Insights after implementation', 'wpshadow' ),
				),
			),
			'details'       => array(
				'why_important'    => __( 'Lazy loading significantly reduces initial page weight and improves Core Web Vitals. Pages with many images benefit most - typical savings are 30-50% on initial load, with images loading as users scroll.', 'wpshadow' ),
				'user_impact'      => array(
					__( 'Mobile Users: Faster page loads on cellular connections', 'wpshadow' ),
					__( 'Bandwidth: 30-50% reduction in initial data transfer', 'wpshadow' ),
					__( 'Core Web Vitals: Improved LCP scores', 'wpshadow' ),
					__( 'SEO: Page speed is a ranking factor', 'wpshadow' ),
				),
				'solution_options' => array(
					'Upgrade WordPress (Best)' => array(
						'description' => __( 'Upgrade to WordPress 5.5+ for native support', 'wpshadow' ),
						'time'        => __( '15-30 minutes', 'wpshadow' ),
						'cost'        => __( 'Free', 'wpshadow' ),
						'difficulty'  => __( 'Easy', 'wpshadow' ),
					),
					'Lazy Load Plugin' => array(
						'description' => __( 'Install a3 Lazy Load or similar plugin', 'wpshadow' ),
						'time'        => __( '10 minutes', 'wpshadow' ),
						'cost'        => __( 'Free', 'wpshadow' ),
						'difficulty'  => __( 'Easy', 'wpshadow' ),
						'plugin'      => 'a3-lazy-load',
					),
				),
				'best_practices'   => array(
					__( 'Don\'t lazy load above-fold images (hurts LCP)', 'wpshadow' ),
					__( 'Native lazy loading (WordPress 5.5+) is best option', 'wpshadow' ),
					__( 'Test on real devices to verify performance gain', 'wpshadow' ),
					__( 'Combine with image optimization for best results', 'wpshadow' ),
				),
			),
		);
	}

	/**
	 * Detect lazy loading plugins.
	 *
	 * @since  1.6028.1535
	 * @return array Active lazy loading plugins.
	 */
	private static function detect_lazy_loading_plugins() {
		$plugins = array();

		$lazy_plugins = array(
			'a3-lazy-load/a3-lazy-load.php'                     => 'a3 Lazy Load',
			'rocket-lazy-load/rocket-lazy-load.php'             => 'Lazy Load by WP Rocket',
			'wp-smushit/wp-smush.php'                           => 'Smush (has lazy load)',
			'lazy-load/lazy-load.php'                           => 'Lazy Load',
			'wp-rocket/wp-rocket.php'                           => 'WP Rocket',
		);

		foreach ( $lazy_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$plugins[] = $plugin_name;
			}
		}

		return $plugins;
	}

	/**
	 * Count images in media library.
	 *
	 * @since  1.6028.1535
	 * @return int Image count.
	 */
	private static function count_media_images() {
		global $wpdb;

		$count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} 
			WHERE post_type = 'attachment' 
			AND post_mime_type LIKE 'image/%'"
		);

		return intval( $count );
	}
}
