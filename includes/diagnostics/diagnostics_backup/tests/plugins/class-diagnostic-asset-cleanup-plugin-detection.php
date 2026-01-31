<?php
/**
 * Asset Cleanup Plugin Detection Diagnostic
 *
 * Asset Cleanup Plugin Detection not optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.929.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Asset Cleanup Plugin Detection Diagnostic Class
 *
 * @since 1.929.0000
 */
class Diagnostic_AssetCleanupPluginDetection extends Diagnostic_Base {

	protected static $slug = 'asset-cleanup-plugin-detection';
	protected static $title = 'Asset Cleanup Plugin Detection';
	protected static $description = 'Asset Cleanup Plugin Detection not optimized';
	protected static $family = 'performance';

	public static function check() {
		// Check for Asset CleanUp plugins
		$has_asset_cleanup = defined( 'WPACU_PLUGIN_VERSION' ) || 
		                     class_exists( 'WpAssetCleanUp' ) ||
		                     get_option( 'wpacu_settings' );
		
		if ( ! $has_asset_cleanup ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: Load detection enabled
		$test_mode = get_option( 'wpacu_test_mode', false );
		if ( $test_mode ) {
			$issues[] = __( 'Test mode active (not optimizing for visitors)', 'wpshadow' );
		}
		
		// Check 2: Unload rules count
		$unload_rules = get_option( 'wpacu_global_unload', array() );
		$rule_count = is_array( $unload_rules ) ? count( $unload_rules ) : 0;
		
		if ( $rule_count === 0 ) {
			$issues[] = __( 'No unload rules configured (plugin not being used)', 'wpshadow' );
		} elseif ( $rule_count > 50 ) {
			$issues[] = sprintf( __( '%d unload rules (maintenance burden)', 'wpshadow' ), $rule_count );
		}
		
		// Check 3: Minify enabled
		$minify_css = get_option( 'wpacu_minify_css', false );
		$minify_js = get_option( 'wpacu_minify_js', false );
		
		if ( ! $minify_css && ! $minify_js ) {
			$issues[] = __( 'Minification disabled (missing optimization)', 'wpshadow' );
		}
		
		// Check 4: Combine files
		$combine_css = get_option( 'wpacu_combine_css', false );
		$combine_js = get_option( 'wpacu_combine_js', false );
		
		if ( $combine_css || $combine_js ) {
			$issues[] = __( 'File combination enabled (HTTP/2 counter-productive)', 'wpshadow' );
		}
		
		// Check 5: Critical CSS
		$critical_css = get_option( 'wpacu_critical_css', '' );
		if ( empty( $critical_css ) ) {
			$issues[] = __( 'Critical CSS not configured (render-blocking)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 45;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 58;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 52;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of asset cleanup issues */
				__( 'Asset CleanUp has %d optimization opportunities: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/asset-cleanup-plugin-detection',
		);
	}
}
