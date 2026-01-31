<?php
/**
 * Eventbrite Ticket Sync Diagnostic
 *
 * Eventbrite sync causing conflicts.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.581.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Eventbrite Ticket Sync Diagnostic Class
 *
 * @since 1.581.0000
 */
class Diagnostic_EventbriteTicketSync extends Diagnostic_Base {

	protected static $slug = 'eventbrite-ticket-sync';
	protected static $title = 'Eventbrite Ticket Sync';
	protected static $description = 'Eventbrite sync causing conflicts';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'Eventbrite_API' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: API token configured
		$api_token = get_option( 'eventbrite_api_token', '' );
		if ( empty( $api_token ) ) {
			$issues[] = __( 'No API token (sync disabled)', 'wpshadow' );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Eventbrite API token not configured', 'wpshadow' ),
				'severity'    => 65,
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/eventbrite-ticket-sync',
			);
		}
		
		// Check 2: Sync frequency
		$sync_frequency = get_option( 'eventbrite_sync_frequency', 'hourly' );
		if ( 'hourly' === $sync_frequency ) {
			$issues[] = __( 'Hourly sync (outdated ticket counts)', 'wpshadow' );
		}
		
		// Check 3: Last sync time
		$last_sync = get_option( 'eventbrite_last_sync', 0 );
		if ( $last_sync > 0 && ( time() - $last_sync ) > 3600 ) {
			$hours_ago = round( ( time() - $last_sync ) / 3600 );
			$issues[] = sprintf( __( 'Last sync %d hours ago (stale data)', 'wpshadow' ), $hours_ago );
		}
		
		// Check 4: Webhook configuration
		$webhook_enabled = get_option( 'eventbrite_webhook_enabled', 'no' );
		if ( 'no' === $webhook_enabled ) {
			$issues[] = __( 'Webhooks disabled (delayed updates)', 'wpshadow' );
		}
		
		// Check 5: Inventory conflicts
		global $wpdb;
		$conflicts = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = '_eventbrite_sync_conflict'"
		);
		
		if ( $conflicts > 0 ) {
			$issues[] = sprintf( __( '%d inventory conflicts', 'wpshadow' ), $conflicts );
		}
		
		// Check 6: Order reconciliation
		$auto_reconcile = get_option( 'eventbrite_auto_reconcile', 'yes' );
		if ( 'no' === $auto_reconcile ) {
			$issues[] = __( 'Manual order reconciliation (admin overhead)', 'wpshadow' );
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
				/* translators: %s: list of ticket sync issues */
				__( 'Eventbrite ticket sync has %d issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/eventbrite-ticket-sync',
		);
	}
}
