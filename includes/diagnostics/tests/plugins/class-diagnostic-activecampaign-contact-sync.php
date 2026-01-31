<?php
/**
 * Activecampaign Contact Sync Diagnostic
 *
 * Activecampaign Contact Sync configuration issues.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.728.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Activecampaign Contact Sync Diagnostic Class
 *
 * @since 1.728.0000
 */
class Diagnostic_ActivecampaignContactSync extends Diagnostic_Base {

	protected static $slug = 'activecampaign-contact-sync';
	protected static $title = 'Activecampaign Contact Sync';
	protected static $description = 'Activecampaign Contact Sync configuration issues';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'ActiveCampaign' ) && ! defined( 'ACTIVECAMPAIGN_VERSION' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Contact sync enabled.
		$sync_enabled = get_option( 'activecampaign_contact_sync', '1' );
		if ( '0' === $sync_enabled ) {
			$issues[] = 'contact sync disabled';
		}

		// Check 2: Pending contact syncs.
		global $wpdb;
		$pending_syncs = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->usermeta} WHERE meta_key = %s AND meta_value = %s",
				'activecampaign_sync_status',
				'pending'
			)
		);
		if ( $pending_syncs > 50 ) {
			$issues[] = "{$pending_syncs} contacts pending sync (queue backing up)";
		}

		// Check 3: Failed sync attempts.
		$failed_syncs = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->usermeta} WHERE meta_key = %s AND meta_value = %s",
				'activecampaign_sync_status',
				'failed'
			)
		);
		if ( $failed_syncs > 0 ) {
			$issues[] = "{$failed_syncs} contacts failed to sync";
		}

		// Check 4: Orphaned contacts (in WP but not in AC).
		$total_users = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->users}" );
		$synced_users = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->usermeta} WHERE meta_key = %s AND meta_value != %s",
				'activecampaign_contact_id',
				''
			)
		);
		if ( $synced_users < ( $total_users * 0.8 ) ) {
			$percentage = round( ( $synced_users / $total_users ) * 100 );
			$issues[] = "only {$percentage}% of users synced to ActiveCampaign";
		}

		// Check 5: Sync frequency setting.
		$sync_frequency = get_option( 'activecampaign_sync_frequency', 'realtime' );
		if ( 'realtime' === $sync_frequency && $total_users > 1000 ) {
			$issues[] = 'real-time sync on large user base (consider scheduled batches)';
		}

		// Check 6: Field mapping configuration.
		$field_mapping = get_option( 'activecampaign_field_mapping', array() );
		if ( empty( $field_mapping ) ) {
			$issues[] = 'no field mapping configured (default fields only)';
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 70, 40 + ( count( $issues ) * 6 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'ActiveCampaign contact sync issues: ' . implode( ', ', $issues ),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/activecampaign-contact-sync',
			);
		}

		return null;
	}
}
