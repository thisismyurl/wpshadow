<?php
/**
 * AIOSEO Plugin Conflicts Diagnostic
 *
 * Detects conflicts with other SEO plugins.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5029.1805
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AIOSEO Conflicts Class
 *
 * Checks for SEO plugin conflicts.
 *
 * @since 1.5029.1805
 */
class Diagnostic_AIOSEO_Conflicts extends Diagnostic_Base {

	protected static $slug        = 'aioseo-conflicts';
	protected static $title       = 'AIOSEO Plugin Conflicts';
	protected static $description = 'Detects SEO plugin conflicts';
	protected static $family      = 'plugins';

	public static function check() {
		if ( ! function_exists( 'aioseo' ) ) {
			return null;
		}

		$cache_key = 'wpshadow_aioseo_conflicts';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$conflicting_plugins = array();

		// Check for other SEO plugins.
		$seo_plugins = array(
			'wordpress-seo/wp-seo.php' => 'Yoast SEO',
			'wordpress-seo-premium/wp-seo-premium.php' => 'Yoast SEO Premium',
			'seo-by-rank-math/rank-math.php' => 'Rank Math',
			'seo-by-rank-math-pro/rank-math-pro.php' => 'Rank Math Pro',
			'wp-seopress/seopress.php' => 'SEOPress',
			'wp-seopress-pro/seopress-pro.php' => 'SEOPress Pro',
		);

		foreach ( $seo_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$conflicting_plugins[] = $plugin_name;
			}
		}

		if ( ! empty( $conflicting_plugins ) ) {
			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: count */
					__( '%d conflicting SEO plugin(s) detected. Multiple SEO plugins can cause duplicate meta tags!', 'wpshadow' ),
					count( $conflicting_plugins )
				),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/plugins-aioseo-conflicts',
				'data'         => array(
					'conflicting_plugins' => $conflicting_plugins,
					'total_conflicts' => count( $conflicting_plugins ),
					'recommendation' => 'Deactivate all other SEO plugins to avoid conflicts',
				),
			);

			set_transient( $cache_key, $result, 12 * HOUR_IN_SECONDS );
			return $result;
		}

		set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
		return null;
	}
}
