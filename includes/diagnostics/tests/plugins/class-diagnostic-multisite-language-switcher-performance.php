<?php
/**
 * Multisite Language Switcher Performance Diagnostic
 *
 * Multisite Language Switcher Performance misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.962.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Multisite Language Switcher Performance Diagnostic Class
 *
 * @since 1.962.0000
 */
class Diagnostic_MultisiteLanguageSwitcherPerformance extends Diagnostic_Base {

	protected static $slug = 'multisite-language-switcher-performance';
	protected static $title = 'Multisite Language Switcher Performance';
	protected static $description = 'Multisite Language Switcher Performance misconfigured';
	protected static $family = 'performance';

	public static function check() {
		if ( ! is_multisite() ) {
			return null;
		}

		$has_msls = class_exists( 'Multisite_Language_Switcher' ) ||
		            function_exists( 'get_the_msls' ) ||
		            defined( 'MSLS_PLUGIN_VERSION' );

		if ( ! $has_msls ) {
			return null;
		}

		$issues = array();

		// Check 1: Site count
		$site_count = get_blog_count();
		if ( $site_count > 20 ) {
			$issues[] = sprintf( __( '%d sites (slow language switching)', 'wpshadow' ), $site_count );
		}

		// Check 2: Caching
		$cache_enabled = get_option( 'msls_cache_enabled', 'no' );
		if ( 'no' === $cache_enabled ) {
			$issues[] = __( 'Language queries not cached (redundant lookups)', 'wpshadow' );
		}

		// Check 3: Auto-detect language
		$auto_detect = get_option( 'msls_auto_detect', 'yes' );
		if ( 'yes' === $auto_detect ) {
			$issues[] = __( 'Auto-detect enabled (extra queries)', 'wpshadow' );
		}

		// Check 4: Flag images
		$flag_type = get_option( 'msls_flag_type', 'image' );
		if ( 'image' === $flag_type ) {
			$issues[] = __( 'Using flag images (extra HTTP requests)', 'wpshadow' );
		}

		// Check 5: Link optimization
		$optimize_links = get_option( 'msls_optimize_links', 'no' );
		if ( 'no' === $optimize_links ) {
			$issues[] = __( 'Links not optimized (slower page generation)', 'wpshadow' );
		}

		// Check 6: Database queries
		global $wpdb;
		$query_count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->sitemeta} WHERE meta_key LIKE 'msls_%'"
		);

		if ( $query_count > 1000 ) {
			$issues[] = sprintf( __( '%d MSLS meta entries (database bloat)', 'wpshadow' ), $query_count );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		$threat_level = 55;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 67;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 61;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				__( 'Multisite Language Switcher has %d performance issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/multisite-language-switcher-performance',
		);
	}
}
