<?php
/**
 * Wp Crontrol Hook Performance Diagnostic
 *
 * Wp Crontrol Hook Performance issue detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1046.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wp Crontrol Hook Performance Diagnostic Class
 *
 * @since 1.1046.0000
 */
class Diagnostic_WpCrontrolHookPerformance extends Diagnostic_Base {

	protected static $slug = 'wp-crontrol-hook-performance';
	protected static $title = 'Wp Crontrol Hook Performance';
	protected static $description = 'Wp Crontrol Hook Performance issue detected';
	protected static $family = 'performance';

	public static function check() {
		// Check for WP Crontrol plugin
		if ( ! function_exists( 'crontrol_get_cron_hooks' ) && ! defined( 'CRONTROL_VERSION' ) ) {
			// Check WordPress cron directly
			$cron_array = _get_cron_array();
			if ( empty( $cron_array ) ) {
				return null;
			}
		}
		
		$issues = array();
		
		// Check 1: Total cron events
		$cron_events = _get_cron_array();
		$total_events = 0;
		if ( is_array( $cron_events ) ) {
			foreach ( $cron_events as $timestamp => $cron ) {
				$total_events += count( $cron );
			}
		}
		
		if ( $total_events > 50 ) {
			$issues[] = sprintf( __( '%d cron events registered (high overhead)', 'wpshadow' ), $total_events );
		}
		
		// Check 2: Stuck/missed events
		$missed_events = 0;
		$now = time();
		if ( is_array( $cron_events ) ) {
			foreach ( $cron_events as $timestamp => $cron ) {
				if ( $timestamp < ( $now - 3600 ) ) {
					$missed_events += count( $cron );
				}
			}
		}
		
		if ( $missed_events > 5 ) {
			$issues[] = sprintf( __( '%d missed/stuck cron events (WP-Cron may be disabled)', 'wpshadow' ), $missed_events );
		}
		
		// Check 3: Duplicate schedules
		$hooks = array();
		if ( is_array( $cron_events ) ) {
			foreach ( $cron_events as $timestamp => $cron ) {
				foreach ( $cron as $hook => $data ) {
					$hooks[] = $hook;
				}
			}
		}
		$duplicates = array_diff_assoc( $hooks, array_unique( $hooks ) );
		
		if ( count( $duplicates ) > 10 ) {
			$issues[] = sprintf( __( '%d duplicate cron hooks (check for plugin conflicts)', 'wpshadow' ), count( $duplicates ) );
		}
		
		// Check 4: WP-Cron disabled check
		if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) {
			$system_cron = get_option( 'crontrol_system_cron_configured', false );
			if ( ! $system_cron ) {
				$issues[] = __( 'WP-Cron disabled without system cron confirmation', 'wpshadow' );
			}
		}
		
		// Check 5: Spawn frequency
		$spawn_frequency = get_transient( 'doing_cron' );
		if ( $spawn_frequency ) {
			$issues[] = __( 'Cron currently running (may indicate frequent spawning)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 55;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 68;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 62;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of performance issues */
				__( 'WordPress cron hooks have %d performance issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/wp-crontrol-hook-performance',
		);
	}
}
