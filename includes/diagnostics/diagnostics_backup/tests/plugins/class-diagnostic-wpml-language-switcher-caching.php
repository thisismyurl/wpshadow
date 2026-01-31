<?php
/**
 * Wpml Language Switcher Caching Diagnostic
 *
 * Wpml Language Switcher Caching misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1142.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wpml Language Switcher Caching Diagnostic Class
 *
 * @since 1.1142.0000
 */
class Diagnostic_WpmlLanguageSwitcherCaching extends Diagnostic_Base {

	protected static $slug = 'wpml-language-switcher-caching';
	protected static $title = 'Wpml Language Switcher Caching';
	protected static $description = 'Wpml Language Switcher Caching misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'ICL_SITEPRESS_VERSION' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Language switcher caching enabled
		$cache_enabled = get_option( 'icl_lang_switcher_cache', '0' );
		if ( '0' === $cache_enabled ) {
			$issues[] = 'language switcher caching disabled (performance impact)';
		}

		// Check 2: Cache compatibility with page cache
		$page_cache_active = ( defined( 'WP_CACHE' ) && WP_CACHE );
		if ( $page_cache_active && '1' === $cache_enabled ) {
			$cache_mode = get_option( 'icl_cache_mode', 'default' );
			if ( 'default' === $cache_mode ) {
				$issues[] = 'cache mode not optimized for page caching plugin';
			}
		}

		// Check 3: Language cookie caching issues
		$cookie_based = get_option( 'icl_cookie_language', '0' );
		if ( '1' === $cookie_based && $page_cache_active ) {
			$issues[] = 'cookie-based language + page cache (may serve wrong language)';
		}

		// Check 4: Language negotiation method
		$negotiation = get_option( 'icl_language_negotiation_type', '1' );
		if ( '3' === $negotiation ) {
			$issues[] = 'language in domain (separate cache per language required)';
		}

		// Check 5: Switcher position and AJAX
		$switcher_ajax = get_option( 'icl_lang_switcher_ajax', '0' );
		if ( '1' === $switcher_ajax ) {
			$ajax_cache = get_option( 'icl_ajax_cache', '0' );
			if ( '0' === $ajax_cache ) {
				$issues[] = 'AJAX switcher without caching (extra requests)';
			}
		}

		// Check 6: Cache purging on language changes
		$auto_purge = get_option( 'icl_cache_auto_purge', '1' );
		if ( '0' === $auto_purge && '1' === $cache_enabled ) {
			$issues[] = 'cache not purged on language changes (stale content)';
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 70, 40 + ( count( $issues ) * 6 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'WPML language switcher caching issues: ' . implode( ', ', $issues ),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/wpml-language-switcher-caching',
			);
		}

		return null;
	}
}
