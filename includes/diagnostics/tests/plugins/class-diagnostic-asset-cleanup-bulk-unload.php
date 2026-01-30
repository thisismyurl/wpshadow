<?php
/**
 * Asset Cleanup Bulk Unload Diagnostic
 *
 * Asset Cleanup Bulk Unload not optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.927.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Asset Cleanup Bulk Unload Diagnostic Class
 *
 * @since 1.927.0000
 */
class Diagnostic_AssetCleanupBulkUnload extends Diagnostic_Base {

	protected static $slug = 'asset-cleanup-bulk-unload';
	protected static $title = 'Asset Cleanup Bulk Unload';
	protected static $description = 'Asset Cleanup Bulk Unload not optimized';
	protected static $family = 'performance';

	public static function check() {
		// Check for Asset CleanUp
		if ( ! function_exists( 'wpacu_object' ) && ! defined( 'WPACU_PLUGIN_VERSION' ) ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: Count bulk unload rules
		$bulk_unloads = get_option( 'wpacu_global_unload', array() );
		if ( ! empty( $bulk_unloads ) && count( $bulk_unloads ) > 50 ) {
			$issues[] = sprintf( __( '%d bulk unload rules (performance overhead)', 'wpshadow' ), count( $bulk_unloads ) );
		}
		
		// Check 2: Unload everywhere rules
		$unload_everywhere = get_option( 'wpacu_global_data', array() );
		if ( isset( $unload_everywhere['styles'] ) && count( $unload_everywhere['styles'] ) > 20 ) {
			$issues[] = sprintf( __( '%d site-wide CSS unloads (maintenance burden)', 'wpshadow' ), count( $unload_everywhere['styles'] ) );
		}
		if ( isset( $unload_everywhere['scripts'] ) && count( $unload_everywhere['scripts'] ) > 20 ) {
			$issues[] = sprintf( __( '%d site-wide JS unloads (maintenance burden)', 'wpshadow' ), count( $unload_everywhere['scripts'] ) );
		}
		
		// Check 3: Regex rules
		$regex_rules = get_option( 'wpacu_load_exceptions', array() );
		if ( ! empty( $regex_rules['regex'] ) && count( $regex_rules['regex'] ) > 10 ) {
			$issues[] = sprintf( __( '%d regex rules (slow pattern matching)', 'wpshadow' ), count( $regex_rules['regex'] ) );
		}
		
		// Check 4: Test mode
		$test_mode = get_option( 'wpacu_test_mode', 0 );
		if ( ! $test_mode ) {
			$issues[] = __( 'Test mode disabled (no safe preview)', 'wpshadow' );
		}
		
		// Check 5: Plugin conflicts
		$settings = get_option( 'wpacu_settings', array() );
		if ( empty( $settings['disable_jquery_migrate'] ) ) {
			// Check if other plugins might conflict
			if ( is_plugin_active( 'autoptimize/autoptimize.php' ) ||
			     is_plugin_active( 'wp-rocket/wp-rocket.php' ) ) {
				$issues[] = __( 'Running with other optimization plugins (conflict risk)', 'wpshadow' );
			}
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
				/* translators: %s: list of bulk unload issues */
				__( 'Asset CleanUp bulk unload has %d issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/asset-cleanup-bulk-unload',
		);
	}
}
