<?php
/**
 * Asset Cleanup Plugin Rules Diagnostic
 *
 * Asset Cleanup Plugin Rules not optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.926.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Asset Cleanup Plugin Rules Diagnostic Class
 *
 * @since 1.926.0000
 */
class Diagnostic_AssetCleanupPluginRules extends Diagnostic_Base {

	protected static $slug = 'asset-cleanup-plugin-rules';
	protected static $title = 'Asset Cleanup Plugin Rules';
	protected static $description = 'Asset Cleanup Plugin Rules not optimized';
	protected static $family = 'performance';

	public static function check() {
		if ( ! class_exists( 'WpAssetCleanUp' ) && ! function_exists( 'wpacu_init' ) ) {
			return null;
		}

		$issues = array();

		// Check if plugin is actually unloading assets
		global $wpdb;
		$unload_rules = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE %s",
				'%wpacu_%unload%'
			)
		);

		if ( $unload_rules < 1 ) {
			$issues[] = 'no asset unload rules configured (plugin not being utilized)';
		}

		// Check for test mode enabled in production
		$test_mode = get_option( 'wpacu_test_mode', '0' );
		if ( '1' === $test_mode && ! WP_DEBUG ) {
			$issues[] = 'test mode enabled in production environment';
		}

		// Check for CSS combine/minify settings
		$combine_css = get_option( 'wpacu_combine_loaded_css', '0' );
		$minify_css = get_option( 'wpacu_minify_css', '0' );
		if ( '0' === $combine_css && '0' === $minify_css ) {
			$issues[] = 'CSS optimization features disabled (missing performance benefits)';
		}

		// Check for JavaScript optimization
		$combine_js = get_option( 'wpacu_combine_loaded_js', '0' );
		$minify_js = get_option( 'wpacu_minify_js', '0' );
		if ( '0' === $combine_js && '0' === $minify_js ) {
			$issues[] = 'JavaScript optimization features disabled';
		}

		// Check for cache directory permissions
		$cache_dir = WP_CONTENT_DIR . '/cache/asset-cleanup/';
		if ( is_dir( $cache_dir ) ) {
			if ( ! is_writable( $cache_dir ) ) {
				$issues[] = 'cache directory not writable (optimization files cannot be saved)';
			}
		}

		// Check for conflicting plugins
		$conflicting_plugins = array(
			'autoptimize/autoptimize.php',
			'wp-rocket/wp-rocket.php',
			'fast-velocity-minify/fvm.php',
		);

		$active_plugins = get_option( 'active_plugins', array() );
		$conflicts = array_intersect( $conflicting_plugins, $active_plugins );

		if ( ! empty( $conflicts ) && ( '1' === $combine_css || '1' === $combine_js ) ) {
			$issues[] = 'conflicting optimization plugins active (may cause issues)';
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 70, 40 + ( count( $issues ) * 6 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Asset Cleanup plugin configuration issues: ' . implode( ', ', $issues ),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/asset-cleanup-plugin-rules',
			);
		}

		return null;
	}
}
