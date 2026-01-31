<?php
/**
 * Wordpress Site Health Cron Diagnostic
 *
 * Wordpress Site Health Cron issue detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1252.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wordpress Site Health Cron Diagnostic Class
 *
 * @since 1.1252.0000
 */
class Diagnostic_WordpressSiteHealthCron extends Diagnostic_Base {

	protected static $slug = 'wordpress-site-health-cron';
	protected static $title = 'Wordpress Site Health Cron';
	protected static $description = 'Wordpress Site Health Cron issue detected';
	protected static $family = 'functionality';

	public static function check() {
		// WordPress Site Health cron (introduced in WP 5.2)
		global $wp_version;
		if ( version_compare( $wp_version, '5.2', '<' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Site Health cron scheduled
		$cron_array = _get_cron_array();
		$health_check_scheduled = false;
		
		if ( is_array( $cron_array ) ) {
			foreach ( $cron_array as $timestamp => $hooks ) {
				if ( isset( $hooks['wp_site_health_scheduled_check'] ) ) {
					$health_check_scheduled = true;
					break;
				}
			}
		}
		
		if ( ! $health_check_scheduled ) {
			$issues[] = __( 'Site Health scheduled check not registered', 'wpshadow' );
		}
		
		// Check 2: Last Site Health check time
		$last_check = get_option( 'wp_site_health_last_check', 0 );
		if ( $last_check > 0 ) {
			$time_since = time() - $last_check;
			if ( $time_since > 172800 ) { // 2 days
				$issues[] = sprintf( __( 'Site Health not checked in %d days', 'wpshadow' ), floor( $time_since / 86400 ) );
			}
		}
		
		// Check 3: Site Health test results
		$test_results = get_transient( 'health-check-site-status-result' );
		if ( false === $test_results ) {
			$issues[] = __( 'Site Health test results not cached (tests may be slow)', 'wpshadow' );
		}
		
		// Check 4: Critical issues in Site Health
		if ( is_array( $test_results ) && isset( $test_results['critical'] ) && $test_results['critical'] > 0 ) {
			$issues[] = sprintf( __( '%d critical issues in Site Health', 'wpshadow' ), $test_results['critical'] );
		}
		
		// Check 5: Loopback request status
		$loopback_test = get_transient( 'health-check-site-status-loopback' );
		if ( is_array( $loopback_test ) && isset( $loopback_test['status'] ) && 'good' !== $loopback_test['status'] ) {
			$issues[] = __( 'Loopback requests failing (cron and scheduled events affected)', 'wpshadow' );
		}
		
		
		// Check 6: Feature initialization
		if ( ! (get_option( "features_init" ) !== false) ) {
			$issues[] = __( 'Feature initialization', 'wpshadow' );
		}

		// Check 7: Database tables
		if ( ! (! empty( $GLOBALS["wpdb"] )) ) {
			$issues[] = __( 'Database tables', 'wpshadow' );
		}

		// Check 8: Hook registration
		if ( ! (has_action( "init" )) ) {
			$issues[] = __( 'Hook registration', 'wpshadow' );
		}
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = (40 + min(35, count($issues) * 8));
		if ( count( $issues ) >= 4 ) {
			$threat_level = (40 + min(35, count($issues) * 8));
		} elseif ( count( $issues ) >= 2 ) {
			$threat_level = (40 + min(35, count($issues) * 8));
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of site health issues */
				__( 'WordPress Site Health cron has %d issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => true,
			'kb_link'     => 'https://wpshadow.com/kb/wordpress-site-health-cron',
		);
	}
}
