<?php
/**
 * WP All Import Cron Configuration Diagnostic
 *
 * Scheduled imports not configured properly.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.274.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WP All Import Cron Configuration Diagnostic Class
 *
 * @since 1.274.0000
 */
class Diagnostic_WpAllImportCronConfiguration extends Diagnostic_Base {

	protected static $slug = 'wp-all-import-cron-configuration';
	protected static $title = 'WP All Import Cron Configuration';
	protected static $description = 'Scheduled imports not configured properly';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'PMXI_Plugin' ) ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: Get scheduled imports
		$scheduled_imports = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}pmxi_imports WHERE scheduled = %d",
				1
			)
		);
		
		if ( empty( $scheduled_imports ) ) {
			return null;
		}
		
		// Check 2: Cron events registered
		$cron_events = _get_cron_array();
		$has_import_cron = false;
		foreach ( $cron_events as $timestamp => $cron ) {
			foreach ( $cron as $hook => $dings ) {
				if ( strpos( $hook, 'pmxi_' ) !== false || strpos( $hook, 'wp_all_import' ) !== false ) {
					$has_import_cron = true;
					break 2;
				}
			}
		}
		
		if ( ! $has_import_cron && count( $scheduled_imports ) > 0 ) {
			$issues[] = sprintf( __( '%d scheduled imports but no cron events registered', 'wpshadow' ), count( $scheduled_imports ) );
		}
		
		// Check 3: WP-Cron disabled
		if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) {
			$system_cron = get_option( 'pmxi_cron_job_key', '' );
			if ( empty( $system_cron ) ) {
				$issues[] = __( 'WP-Cron disabled but no system cron configured', 'wpshadow' );
			}
		}
		
		// Check 4: Import schedule interval validation
		foreach ( $scheduled_imports as $import ) {
			if ( ! empty( $import->scheduling_run_on ) ) {
				$schedule = maybe_unserialize( $import->scheduling_run_on );
				if ( empty( $schedule ) ) {
					$issues[] = sprintf( __( 'Import ID %d has invalid schedule data', 'wpshadow' ), $import->id );
				}
			}
		}
		
		// Check 5: Check for stuck imports
		$stuck_imports = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}pmxi_imports WHERE processing = %d AND triggered = %d AND created_at < DATE_SUB(NOW(), INTERVAL 1 HOUR)",
				1,
				1
			)
		);
		
		if ( $stuck_imports > 0 ) {
			$issues[] = sprintf( __( '%d imports stuck in processing state', 'wpshadow' ), $stuck_imports );
		}
		
		// Check 6: Memory limit for large imports
		$memory_limit = ini_get( 'memory_limit' );
		$memory_bytes = wp_convert_hr_to_bytes( $memory_limit );
		$large_imports = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}pmxi_imports WHERE count > %d",
				1000
			)
		);
		
		if ( $large_imports > 0 && $memory_bytes < 268435456 ) {
			$issues[] = sprintf( __( '%d large imports with memory limit below 256M', 'wpshadow' ), $large_imports );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 40;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 55;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 48;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of configuration issues */
				__( 'WP All Import cron has %d configuration issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/wp-all-import-cron-configuration',
		);
	}
}
