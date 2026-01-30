<?php
/**
 * Advanced Ads Cache Compatibility Diagnostic
 *
 * Advanced Ads breaking caching plugins.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.290.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Advanced Ads Cache Compatibility Diagnostic Class
 *
 * @since 1.290.0000
 */
class Diagnostic_AdvancedAdsCacheCompatibility extends Diagnostic_Base {

	protected static $slug = 'advanced-ads-cache-compatibility';
	protected static $title = 'Advanced Ads Cache Compatibility';
	protected static $description = 'Advanced Ads breaking caching plugins';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'ADVADS_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Cache-busting enabled.
		$cache_busting = get_option( 'advads_cache_busting', '0' );
		if ( '0' === $cache_busting ) {
			$issues[] = 'cache-busting disabled (ads may not rotate properly with caching)';
		}
		
		// Check 2: Conflicting cache plugins.
		$active_plugins = get_option( 'active_plugins', array() );
		$cache_plugins = array(
			'wp-rocket/wp-rocket.php',
			'w3-total-cache/w3-total-cache.php',
			'wp-super-cache/wp-cache.php',
		);
		$active_cache = array_intersect( $cache_plugins, $active_plugins );
		if ( ! empty( $active_cache ) && '0' === $cache_busting ) {
			$issues[] = 'cache plugin detected but Advanced Ads cache-busting disabled';
		}
		
		// Check 3: AJAX loading method.
		$ajax_loading = get_option( 'advads_ajax_loading', '0' );
		if ( '0' === $ajax_loading && ! empty( $active_cache ) ) {
			$issues[] = 'AJAX loading disabled with cache plugin active (may cause ad serving issues)';
		}
		
		// Check 4: Cache exclusion patterns.
		$cache_exclusions = get_option( 'advads_cache_exclusions', array() );
		if ( empty( $cache_exclusions ) && ! empty( $active_cache ) ) {
			$issues[] = 'no cache exclusion patterns configured for ad content';
		}
		
		// Check 5: Dynamic ad content.
		global $wpdb;
		$dynamic_ads = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_content LIKE %s",
				'advanced_ads',
				'%visitor_conditions%'
			)
		);
		if ( $dynamic_ads > 0 && '0' === $cache_busting ) {
			$issues[] = "{$dynamic_ads} ads with visitor conditions but cache-busting disabled";
		}
		
		// Check 6: Output buffering conflicts.
		$output_buffering = get_option( 'advads_output_buffering', 'auto' );
		if ( 'disabled' === $output_buffering && ! empty( $active_cache ) ) {
			$issues[] = 'output buffering disabled (may conflict with cache plugins)';
		}
		
		if ( ! empty( $issues ) ) {
			$threat_level = min( 75, 45 + ( count( $issues ) * 6 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Advanced Ads cache compatibility issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/advanced-ads-cache-compatibility',
			);
		}
		
		return null;
	}
}
