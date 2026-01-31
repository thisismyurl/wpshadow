<?php
/**
 * Seo Framework Performance Diagnostic
 *
 * Seo Framework Performance configuration issues.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.708.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Seo Framework Performance Diagnostic Class
 *
 * @since 1.708.0000
 */
class Diagnostic_SeoFrameworkPerformance extends Diagnostic_Base {

	protected static $slug = 'seo-framework-performance';
	protected static $title = 'Seo Framework Performance';
	protected static $description = 'Seo Framework Performance configuration issues';
	protected static $family = 'performance';

	public static function check() {
		// Check for The SEO Framework plugin
		$has_tsf = defined( 'THE_SEO_FRAMEWORK_VERSION' ) ||
		           function_exists( 'the_seo_framework' ) ||
		           class_exists( 'The_SEO_Framework\Load' );

		if ( ! $has_tsf ) {
			return null;
		}

		$issues = array();

		// Check 1: Cache support
		$cache_enabled = get_option( 'the_seo_framework_cache', 0 );
		if ( ! $cache_enabled ) {
			$issues[] = __( 'Object cache disabled (slower page loads)', 'wpshadow' );
		}

		// Check 2: Sitemap generation
		$sitemap_enabled = get_option( 'autodescription-site-settings', array() );
		if ( isset( $sitemap_enabled['sitemaps_output'] ) && ! $sitemap_enabled['sitemaps_output'] ) {
			$issues[] = __( 'Sitemap disabled (SEO impact)', 'wpshadow' );
		}

		// Check 3: Sitemap transient cache
		$sitemap_cache = get_option( 'the_seo_framework_sitemap_cache', 0 );
		if ( ! $sitemap_cache ) {
			$issues[] = __( 'Sitemap cache disabled (regenerated each request)', 'wpshadow' );
		}

		// Check 4: Low memory mode
		$low_memory = get_option( 'the_seo_framework_low_memory_mode', 0 );
		if ( $low_memory ) {
			$issues[] = __( 'Low memory mode active (limited features)', 'wpshadow' );
		}

		// Check 5: Transient cleanup
		global $wpdb;
		$transient_count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE '_transient_tsf_%'"
		);

		if ( $transient_count > 100 ) {
			$issues[] = sprintf( __( '%d TSF transients (database bloat)', 'wpshadow' ), $transient_count );
		}

		// Check 6: Schema output
		$schema_output = get_option( 'the_seo_framework_schema_output', 1 );
		if ( ! $schema_output ) {
			$issues[] = __( 'Schema.org disabled (lost rich snippets)', 'wpshadow' );
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
				__( 'SEO Framework has %d performance issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/seo-framework-performance',
		);
	}
}
