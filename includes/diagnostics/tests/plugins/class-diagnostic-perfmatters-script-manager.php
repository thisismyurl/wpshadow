<?php
/**
 * Perfmatters Script Manager Diagnostic
 *
 * Perfmatters Script Manager not optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.918.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Perfmatters Script Manager Diagnostic Class
 *
 * @since 1.918.0000
 */
class Diagnostic_PerfmattersScriptManager extends Diagnostic_Base {

	protected static $slug = 'perfmatters-script-manager';
	protected static $title = 'Perfmatters Script Manager';
	protected static $description = 'Perfmatters Script Manager not optimized';
	protected static $family = 'performance';

	public static function check() {
		// Check for Perfmatters plugin
		if ( ! defined( 'PERFMATTERS_VERSION' ) && ! class_exists( 'Perfmatters' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Script manager enabled
		$script_manager_enabled = get_option( 'perfmatters_script_manager_enabled', false );
		if ( ! $script_manager_enabled ) {
			return null;
		}
		
		// Check 2: Get disabled scripts count
		$disabled_scripts = get_option( 'perfmatters_disabled_scripts', array() );
		if ( empty( $disabled_scripts ) ) {
			$issues[] = __( 'Script manager enabled but no scripts disabled', 'wpshadow' );
		}
		
		// Check 3: Critical scripts disabled
		$critical_scripts = array( 'jquery', 'wp-includes' );
		if ( is_array( $disabled_scripts ) ) {
			foreach ( $disabled_scripts as $script => $locations ) {
				foreach ( $critical_scripts as $critical ) {
					if ( stripos( $script, $critical ) !== false ) {
						$issues[] = sprintf( __( 'Critical script disabled: %s (may break site)', 'wpshadow' ), $script );
						break;
					}
				}
			}
		}
		
		// Check 4: Delay JavaScript enabled
		$delay_js = get_option( 'perfmatters_delay_js', false );
		if ( $delay_js ) {
			$delay_exclusions = get_option( 'perfmatters_delay_js_exclusions', array() );
			if ( empty( $delay_exclusions ) ) {
				$issues[] = __( 'JavaScript delay enabled without exclusions (may break interactivity)', 'wpshadow' );
			}
		}
		
		// Check 5: Scripts disabled globally vs per-page
		global $wpdb;
		$global_disabled = 0;
		if ( is_array( $disabled_scripts ) ) {
			foreach ( $disabled_scripts as $script => $locations ) {
				if ( in_array( 'everywhere', $locations, true ) ) {
					$global_disabled++;
				}
			}
		}
		
		if ( $global_disabled > 5 ) {
			$issues[] = sprintf( __( '%d scripts disabled globally (consider per-page rules)', 'wpshadow' ), $global_disabled );
		}
		
		// Check 6: Unused CSS removal
		$unused_css = get_option( 'perfmatters_remove_unused_css', false );
		if ( $unused_css && $delay_js ) {
			$issues[] = __( 'Both unused CSS removal and JS delay enabled (may cause FOUC)', 'wpshadow' );
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
				/* translators: %s: list of optimization issues */
				__( 'Perfmatters Script Manager has %d optimization issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/perfmatters-script-manager',
		);
	}
}
