<?php
/**
 * Wordpress Heartbeat Api Frequency Diagnostic
 *
 * Wordpress Heartbeat Api Frequency issue detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1275.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wordpress Heartbeat Api Frequency Diagnostic Class
 *
 * @since 1.1275.0000
 */
class Diagnostic_WordpressHeartbeatApiFrequency extends Diagnostic_Base {

	protected static $slug = 'wordpress-heartbeat-api-frequency';
	protected static $title = 'Wordpress Heartbeat Api Frequency';
	protected static $description = 'Wordpress Heartbeat Api Frequency issue detected';
	protected static $family = 'functionality';

	public static function check() {
		// WordPress core Heartbeat API always available
		$issues = array();
		
		// Check 1: Heartbeat completely disabled
		$disabled = apply_filters( 'heartbeat_settings', array() );
		if ( isset( $disabled['disable'] ) && $disabled['disable'] === true ) {
			return null; // Heartbeat disabled, no performance concern
		}
		
		// Check 2: Frontend Heartbeat enabled
		$frontend_disabled = get_option( 'heartbeat_disable_frontend', false );
		if ( ! $frontend_disabled ) {
			$issues[] = __( 'Heartbeat API running on frontend (unnecessary load)', 'wpshadow' );
		}
		
		// Check 3: Post editor interval
		$editor_interval = get_option( 'heartbeat_post_interval', 15 );
		if ( $editor_interval < 30 ) {
			$issues[] = sprintf( __( 'Post editor Heartbeat interval: %d seconds (recommend 30-60)', 'wpshadow' ), $editor_interval );
		}
		
		// Check 4: Dashboard interval
		$dashboard_interval = get_option( 'heartbeat_dashboard_interval', 15 );
		if ( $dashboard_interval < 60 ) {
			$issues[] = sprintf( __( 'Dashboard Heartbeat interval: %d seconds (recommend 60-120)', 'wpshadow' ), $dashboard_interval );
		}
		
		// Check 5: Check server load
		$load = sys_getloadavg();
		if ( ! empty( $load ) && $load[0] > 2.0 && $editor_interval < 30 ) {
			$issues[] = sprintf( __( 'High server load (%.2f) with frequent Heartbeat (reduce interval)', 'wpshadow' ), $load[0] );
		}
		
		// Check 6: AJAX polling conflicts
		global $wpdb;
		$ajax_actions = $wpdb->get_var(
			"SELECT COUNT(DISTINCT option_name) FROM {$wpdb->options} WHERE option_name LIKE 'wp_ajax_%'"
		);
		
		if ( $ajax_actions > 20 && $editor_interval < 30 ) {
			$issues[] = sprintf( __( '%d AJAX actions registered with frequent Heartbeat (potential conflicts)', 'wpshadow' ), $ajax_actions );
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
				/* translators: %s: list of performance issues */
				__( 'WordPress Heartbeat API has %d performance issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => true,
			'kb_link'     => 'https://wpshadow.com/kb/wordpress-heartbeat-api-frequency',
		);
	}
}
