<?php
/**
 * Wordpress Cron Execution Diagnostic
 *
 * Wordpress Cron Execution issue detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1276.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wordpress Cron Execution Diagnostic Class
 *
 * @since 1.1276.0000
 */
class Diagnostic_WordpressCronExecution extends Diagnostic_Base {

	protected static $slug = 'wordpress-cron-execution';
	protected static $title = 'Wordpress Cron Execution';
	protected static $description = 'Wordpress Cron Execution issue detected';
	protected static $family = 'functionality';

	public static function check() {
		// WordPress core cron system
		$issues = array();
		
		// Check 1: WP-Cron disabled status
		$wp_cron_disabled = defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON;
		if ( $wp_cron_disabled ) {
			// Check if system cron is configured
			$system_cron_verified = get_transient( 'wpshadow_system_cron_verified' );
			if ( ! $system_cron_verified ) {
				$issues[] = __( 'WP-Cron disabled without system cron verification', 'wpshadow' );
			}
		}
		
		// Check 2: Cron spawn timeout
		$doing_cron = get_transient( 'doing_cron' );
		if ( $doing_cron ) {
			$age = time() - (int) $doing_cron;
			if ( $age > 600 ) { // 10 minutes
				$issues[] = sprintf( __( 'Cron locked for %d minutes (possible timeout)', 'wpshadow' ), floor( $age / 60 ) );
			}
		}
		
		// Check 3: Missed schedules
		$cron_array = _get_cron_array();
		$now = time();
		$missed = 0;
		
		if ( is_array( $cron_array ) ) {
			foreach ( $cron_array as $timestamp => $cron ) {
				if ( $timestamp < ( $now - 3600 ) ) {
					$missed += count( $cron );
				}
			}
		}
		
		if ( $missed > 5 ) {
			$issues[] = sprintf( __( '%d missed cron schedules (over 1 hour late)', 'wpshadow' ), $missed );
		}
		
		// Check 4: Cron execution frequency
		$last_cron_run = get_option( 'wpshadow_last_cron_run', 0 );
		if ( $last_cron_run > 0 ) {
			$time_since = time() - $last_cron_run;
			if ( $time_since > 900 ) { // 15 minutes
				$issues[] = sprintf( __( 'No cron execution for %d minutes', 'wpshadow' ), floor( $time_since / 60 ) );
			}
		}
		
		// Check 5: Alternative cron running
		if ( defined( 'ALTERNATE_WP_CRON' ) && ALTERNATE_WP_CRON && ! $wp_cron_disabled ) {
			$issues[] = __( 'Alternate cron enabled (may cause reliability issues)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			// Update last successful verification
			update_option( 'wpshadow_last_cron_run', time(), false );
			return null;
		}
		
		$threat_level = 50;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 65;
		} elseif ( count( $issues ) >= 2 ) {
			$threat_level = 58;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of cron issues */
				__( 'WordPress cron system has %d execution issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/wordpress-cron-execution',
		);
	}
}
