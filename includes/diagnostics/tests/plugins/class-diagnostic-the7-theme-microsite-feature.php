<?php
/**
 * The7 Theme Microsite Feature Diagnostic
 *
 * The7 Theme Microsite Feature needs optimization.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1313.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The7 Theme Microsite Feature Diagnostic Class
 *
 * @since 1.1313.0000
 */
class Diagnostic_The7ThemeMicrositeFeature extends Diagnostic_Base {

	protected static $slug = 'the7-theme-microsite-feature';
	protected static $title = 'The7 Theme Microsite Feature';
	protected static $description = 'The7 Theme Microsite Feature needs optimization';
	protected static $family = 'functionality';

	public static function check() {
		// Check for The7 theme
		$theme = wp_get_theme();
		if ( 'The7' !== $theme->name && 'The7' !== $theme->parent_theme ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: Microsite feature enabled
		$microsites_enabled = get_option( 'the7_microsites_enabled', false );
		if ( ! $microsites_enabled ) {
			return null;
		}
		
		// Check 2: Microsite count
		$microsite_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s",
				'dt_microsite'
			)
		);
		
		if ( $microsite_count === 0 ) {
			return null;
		}
		
		// Check 3: Asset loading optimization
		$optimize_assets = get_option( 'the7_microsite_optimize_assets', false );
		if ( ! $optimize_assets ) {
			$issues[] = __( 'Microsite asset optimization not enabled (loads all theme assets)', 'wpshadow' );
		}
		
		// Check 4: Microsite caching
		$cache_enabled = get_option( 'the7_microsite_cache', false );
		if ( ! $cache_enabled && $microsite_count > 3 ) {
			$issues[] = __( 'Microsite caching not enabled (performance impact)', 'wpshadow' );
		}
		
		// Check 5: Subdomain configuration
		$use_subdomains = get_option( 'the7_microsite_use_subdomains', false );
		if ( $use_subdomains ) {
			$wildcard_configured = get_option( 'the7_wildcard_dns_configured', false );
			if ( ! $wildcard_configured ) {
				$issues[] = __( 'Subdomains enabled without wildcard DNS verification', 'wpshadow' );
			}
		}
		
		// Check 6: Microsite database tables
		$has_microsite_tables = get_option( 'the7_microsite_separate_tables', false );
		if ( $has_microsite_tables && $microsite_count > 10 ) {
			$issues[] = sprintf( __( '%d microsites with separate tables (database complexity)', 'wpshadow' ), $microsite_count );
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
				/* translators: %s: list of microsite issues */
				__( 'The7 theme microsite feature has %d configuration issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/the7-theme-microsite-feature',
		);
	}
}
