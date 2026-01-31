<?php
/**
 * Oxygen Builder Css Caching Diagnostic
 *
 * Oxygen Builder Css Caching issues found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.816.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Oxygen Builder Css Caching Diagnostic Class
 *
 * @since 1.816.0000
 */
class Diagnostic_OxygenBuilderCssCaching extends Diagnostic_Base {

	protected static $slug = 'oxygen-builder-css-caching';
	protected static $title = 'Oxygen Builder Css Caching';
	protected static $description = 'Oxygen Builder Css Caching issues found';
	protected static $family = 'performance';

	public static function check() {
		$issues = array();
		
		// Check 1: CSS cache enabled
		$css_cache = get_option( 'oxygen_css_cache_enabled', false );
		if ( ! $css_cache ) {
			$issues[] = 'CSS cache disabled';
		}
		
		// Check 2: Automatic cache regeneration
		$auto_regen = get_option( 'oxygen_auto_regenerate_cache', false );
		if ( ! $auto_regen ) {
			$issues[] = 'Auto cache regeneration disabled';
		}
		
		// Check 3: CSS minification enabled
		$minification = get_option( 'oxygen_css_minification', false );
		if ( ! $minification ) {
			$issues[] = 'CSS minification disabled';
		}
		
		// Check 4: Critical CSS generation
		$critical_css = get_option( 'oxygen_critical_css_enabled', false );
		if ( ! $critical_css ) {
			$issues[] = 'Critical CSS not generated';
		}
		
		// Check 5: CDN integration for CSS
		$cdn_integration = get_option( 'oxygen_cdn_css_enabled', false );
		if ( ! $cdn_integration ) {
			$issues[] = 'CDN integration disabled';
		}
		
		// Check 6: Cache busting enabled
		$cache_busting = get_option( 'oxygen_cache_busting_enabled', false );
		if ( ! $cache_busting ) {
			$issues[] = 'Cache busting disabled';
		}
		
		if ( ! empty( $issues ) ) {
			$threat_level = min( 70, 40 + ( count( $issues ) * 5 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Oxygen Builder CSS caching issues: ' . implode( ', ', $issues ),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/oxygen-builder-css-caching',
			);
		}
		
		return null;
	}
}
