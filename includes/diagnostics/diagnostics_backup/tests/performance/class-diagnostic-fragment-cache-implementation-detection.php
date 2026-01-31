<?php
/**
 * Fragment Cache Implementation Detection Diagnostic
 *
 * Identifies opportunities for fragment caching on dynamic pages.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26028.1905
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Fragment Cache Implementation Detection Class
 *
 * Tests for opportunities to implement fragment caching.
 *
 * @since 1.26028.1905
 */
class Diagnostic_Fragment_Cache_Implementation_Detection extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'fragment-cache-implementation-detection';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Fragment Cache Implementation Detection';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Identifies opportunities for fragment caching on dynamic pages';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26028.1905
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$opportunities = array();

		// Check theme files for transient usage.
		$theme_uses_caching = self::theme_uses_fragment_caching();
		if ( ! $theme_uses_caching ) {
			$issues[] = __( 'Theme does not implement fragment caching', 'wpshadow' );
			$opportunities[] = 'theme_templates';
		}

		// Check if navigation menus are cached.
		if ( ! self::has_menu_caching() ) {
			$issues[] = __( 'Navigation menus rebuilt on every request (not cached)', 'wpshadow' );
			$opportunities[] = 'navigation_menus';
		}

		// Check active widgets for caching.
		$uncached_widgets = self::check_widget_caching();
		if ( $uncached_widgets > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of uncached widgets */
				__( '%d active widgets without caching (executing queries on every page)', 'wpshadow' ),
				$uncached_widgets
			);
			$opportunities[] = 'widgets';
		}

		// Check for fragment caching plugins.
		if ( ! self::has_fragment_caching_plugin() ) {
			$issues[] = __( 'No fragment caching plugin installed', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/fragment-cache-implementation-detection',
				'meta'         => array(
					'opportunities'       => $opportunities,
					'uncached_widgets'    => $uncached_widgets,
					'theme_uses_caching'  => $theme_uses_caching,
					'issues_found'        => count( $issues ),
				),
			);
		}

		return null;
	}

	/**
	 * Check if theme uses fragment caching.
	 *
	 * @since  1.26028.1905
	 * @return bool True if theme uses fragment caching.
	 */
	private static function theme_uses_fragment_caching() {
		$theme = wp_get_theme();
		$template_dir = $theme->get_template_directory();

		// Check common template files for transient usage.
		$templates = array(
			'index.php',
			'front-page.php',
			'home.php',
			'header.php',
			'footer.php',
			'sidebar.php',
		);

		$caching_functions = array(
			'get_transient',
			'set_transient',
			'wp_cache_get',
			'wp_cache_set',
		);

		foreach ( $templates as $template ) {
			$file_path = $template_dir . '/' . $template;
			if ( file_exists( $file_path ) ) {
				$content = file_get_contents( $file_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
				foreach ( $caching_functions as $function ) {
					if ( false !== strpos( $content, $function ) ) {
						return true;
					}
				}
			}
		}

		return false;
	}

	/**
	 * Check if navigation menus are cached.
	 *
	 * @since  1.26028.1905
	 * @return bool True if menu caching detected.
	 */
	private static function has_menu_caching() {
		// Check for menu caching plugins.
		$menu_cache_plugins = array(
			'wp-rocket/wp-rocket.php',
			'w3-total-cache/w3-total-cache.php',
		);

		foreach ( $menu_cache_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		// Check for transients related to menus.
		global $wpdb;
		$transients = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->options}
			WHERE option_name LIKE '_transient_menu_%'
			OR option_name LIKE '_transient_nav_menu_%'"
		);

		return $transients > 0;
	}

	/**
	 * Check widget caching implementation.
	 *
	 * @since  1.26028.1905
	 * @return int Number of uncached widgets.
	 */
	private static function check_widget_caching() {
		$sidebars = wp_get_sidebars_widgets();
		$uncached_count = 0;

		if ( empty( $sidebars ) ) {
			return 0;
		}

		foreach ( $sidebars as $sidebar_id => $widgets ) {
			if ( 'wp_inactive_widgets' === $sidebar_id || empty( $widgets ) ) {
				continue;
			}

			foreach ( $widgets as $widget_id ) {
				// Check if widget output is cached.
				$cached = get_transient( 'widget_output_' . $widget_id );
				if ( false === $cached ) {
					++$uncached_count;
				}
			}
		}

		return $uncached_count;
	}

	/**
	 * Check for fragment caching plugins.
	 *
	 * @since  1.26028.1905
	 * @return bool True if fragment caching plugin found.
	 */
	private static function has_fragment_caching_plugin() {
		$fragment_cache_plugins = array(
			'batcache/batcache.php',
			'fragment-cache/fragment-cache.php',
		);

		foreach ( $fragment_cache_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		// Check for ESI support (Varnish, CDN with ESI).
		if ( function_exists( 'fastly_get_options' ) || defined( 'VARNISH_SUPPORT' ) ) {
			return true;
		}

		return false;
	}
}
