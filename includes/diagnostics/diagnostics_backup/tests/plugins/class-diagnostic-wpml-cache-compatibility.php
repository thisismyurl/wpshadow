<?php
/**
 * WPML Cache Compatibility Diagnostic
 *
 * WPML conflicting with caching plugins.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.303.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPML Cache Compatibility Diagnostic Class
 *
 * @since 1.303.0000
 */
class Diagnostic_WpmlCacheCompatibility extends Diagnostic_Base {

	protected static $slug = 'wpml-cache-compatibility';
	protected static $title = 'WPML Cache Compatibility';
	protected static $description = 'WPML conflicting with caching plugins';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'ICL_SITEPRESS_VERSION' ) ) {
			return null;
		}

		$issues = array();

		$settings = get_option( 'icl_sitepress_settings', array() );
		$language_negotiation = isset( $settings['language_negotiation_type'] ) ? (int) $settings['language_negotiation_type'] : 0;

		// Check 1: Verify cache compatibility mode
		$cache_compat = isset( $settings['language_cache'] ) ? (bool) $settings['language_cache'] : false;
		if ( ! $cache_compat ) {
			$issues[] = 'WPML cache compatibility not enabled';
		}

		// Check 2: Check for directory language negotiation with cache
		if ( $language_negotiation === 1 && ! $cache_compat ) {
			$issues[] = 'Directory-based language URLs without cache compatibility';
		}

		// Check 3: Verify language cookie is set
		$language_cookie = isset( $settings['browser_redirect'] ) ? (bool) $settings['browser_redirect'] : false;
		if ( defined( 'WP_CACHE' ) && WP_CACHE && ! $language_cookie ) {
			$issues[] = 'Browser language redirect disabled with cache active';
		}

		// Check 4: Check for persistent object cache
		if ( defined( 'WP_CACHE' ) && WP_CACHE && ! wp_using_ext_object_cache() ) {
			$issues[] = 'Cache enabled but no persistent object cache configured';
		}

		// Check 5: Verify language switcher caching
		$switcher_cache = isset( $settings['language_switcher_caching'] ) ? (bool) $settings['language_switcher_caching'] : false;
		if ( ! $switcher_cache ) {
			$issues[] = 'Language switcher cache not enabled';
		}

		// Check 6: Check for cache plugin compatibility list
		$cache_plugins = isset( $settings['cache_plugin_compatibility'] ) ? (array) $settings['cache_plugin_compatibility'] : array();
		if ( defined( 'WP_CACHE' ) && WP_CACHE && empty( $cache_plugins ) ) {
			$issues[] = 'Cache plugin compatibility list not configured';
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 45;
			$threat_multiplier = 6;
			$max_threat = 75;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d WPML cache compatibility issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/wpml-cache-compatibility',
			);
		}

		return null;
	}
}
