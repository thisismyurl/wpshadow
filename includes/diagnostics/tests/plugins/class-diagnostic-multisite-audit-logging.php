<?php
/**
 * Multisite Audit Logging Diagnostic
 *
 * Multisite Audit Logging misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.978.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Multisite Audit Logging Diagnostic Class
 *
 * @since 1.978.0000
 */
class Diagnostic_MultisiteAuditLogging extends Diagnostic_Base {

	protected static $slug = 'multisite-audit-logging';
	protected static $title = 'Multisite Audit Logging';
	protected static $description = 'Multisite Audit Logging misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! is_multisite() ) {
			return null;
		}

		global $wpdb;
		$issues = array();

		// Check 1: Audit logging enabled
		$logging_enabled = get_site_option( 'multisite_audit_logging', 'no' );
		if ( 'no' === $logging_enabled ) {
			$issues[] = __( 'Audit logging disabled (no activity tracking)', 'wpshadow' );
		}

		// Check 2: Log retention
		$retention = get_site_option( 'audit_log_retention', 0 );
		if ( $retention === 0 ) {
			$issues[] = __( 'No log retention policy (unlimited growth)', 'wpshadow' );
		}

		// Check 3: Logged events
		$logged_events = get_site_option( 'audit_logged_events', array() );
		if ( empty( $logged_events ) ) {
			$issues[] = __( 'No events configured for logging (incomplete audit)', 'wpshadow' );
		}

		// Check 4: User tracking
		$user_tracking = get_site_option( 'audit_track_users', 'no' );
		if ( 'no' === $user_tracking ) {
			$issues[] = __( 'User actions not tracked (security gap)', 'wpshadow' );
		}

		// Check 5: Site-level logs
		$site_count = get_blog_count();
		if ( $site_count > 10 ) {
			$per_site_logs = get_site_option( 'audit_per_site_logs', 'no' );
			if ( 'no' === $per_site_logs ) {
				$issues[] = sprintf( __( '%d sites without individual logs (hard to track)', 'wpshadow' ), $site_count );
			}
		}

		// Check 6: Log export
		$export_capability = get_site_option( 'audit_export_capability', 'no' );
		if ( 'no' === $export_capability ) {
			$issues[] = __( 'Log export not available (compliance issues)', 'wpshadow' );
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
				__( 'Multisite has %d audit logging issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/multisite-audit-logging',
		);
	}
}
