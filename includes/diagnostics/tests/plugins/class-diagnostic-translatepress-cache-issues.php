<?php
/**
 * TranslatePress Cache Issues Diagnostic
 *
 * TranslatePress caching conflicts.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.317.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * TranslatePress Cache Issues Diagnostic Class
 *
 * @since 1.317.0000
 */
class Diagnostic_TranslatepressCacheIssues extends Diagnostic_Base {

	protected static $slug = 'translatepress-cache-issues';
	protected static $title = 'TranslatePress Cache Issues';
	protected static $description = 'TranslatePress caching conflicts';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'TRP_PLUGIN_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Page cache compatibility
		$cache_plugins = array(
			'W3_Total_Cache' => defined( 'W3TC' ),
			'WP_Super_Cache' => function_exists( 'wp_cache_clear_cache' ),
			'WP_Rocket'      => defined( 'WP_ROCKET_VERSION' ),
		);
		
		$has_cache = false;
		foreach ( $cache_plugins as $plugin => $active ) {
			if ( $active ) {
				$has_cache = true;
				break;
			}
		}
		
		if ( $has_cache ) {
			$cache_compat = get_option( 'trp_page_cache_compat', 'no' );
			if ( 'no' === $cache_compat ) {
				$issues[] = __( 'Page cache not configured (wrong language served)', 'wpshadow' );
			}
		}
		
		// Check 2: Translation cache size
		global $wpdb;
		$cache_size = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE 'trp_cache_%'"
		);
		
		if ( $cache_size > 1000 ) {
			$issues[] = sprintf( __( 'Large cache (%d entries, slow queries)', 'wpshadow' ), $cache_size );
		}
		
		// Check 3: Cache invalidation
		$auto_invalidate = get_option( 'trp_auto_invalidate_cache', 'yes' );
		if ( 'no' === $auto_invalidate ) {
			$issues[] = __( 'Manual invalidation only (stale translations)', 'wpshadow' );
		}
		
		// Check 4: Language cookie conflicts
		$cookie_name = get_option( 'trp_language_cookie', 'trp_language' );
		if ( 'trp_language' === $cookie_name ) {
			// Check for other plugins using similar cookie names
			if ( defined( 'WPML_PLUGIN_BASENAME' ) || class_exists( 'Polylang' ) ) {
				$issues[] = __( 'Cookie name may conflict with other i18n plugins', 'wpshadow' );
			}
		}
		
		// Check 5: Object cache integration
		$use_object_cache = get_option( 'trp_use_object_cache', 'no' );
		if ( 'no' === $use_object_cache && wp_using_ext_object_cache() ) {
			$issues[] = __( 'Not using object cache (slow translation lookups)', 'wpshadow' );
		}
		
		// Check 6: Transient cleanup
		$cleanup_interval = get_option( 'trp_cleanup_interval', 'never' );
		if ( 'never' === $cleanup_interval ) {
			$issues[] = __( 'No transient cleanup (database bloat)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 50;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 62;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 56;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of TranslatePress cache issues */
				__( 'TranslatePress has %d cache issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/translatepress-cache-issues',
		);
	}
}
