<?php
/**
 * BuddyPress Activity Spam Protection Diagnostic
 *
 * BuddyPress activity stream vulnerable to spam.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.235.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BuddyPress Activity Spam Protection Diagnostic Class
 *
 * @since 1.235.0000
 */
class Diagnostic_BuddypressActivitySpam extends Diagnostic_Base {

	protected static $slug = 'buddypress-activity-spam';
	protected static $title = 'BuddyPress Activity Spam Protection';
	protected static $description = 'BuddyPress activity stream vulnerable to spam';
	protected static $family = 'security';

	public static function check() {
		if ( ! function_exists( 'buddypress' ) ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		$threat_level = 0;

		// Check Akismet integration
		$akismet_enabled = get_option( 'bp_activity_akismet_enabled', false );
		if ( ! $akismet_enabled && class_exists( 'Akismet' ) ) {
			$issues[] = 'akismet_not_integrated';
			$threat_level += 15;
		}

		// Check activity moderation settings
		$moderate_activity = get_option( 'bp_moderate_activity_updates', false );
		if ( ! $moderate_activity ) {
			$issues[] = 'activity_not_moderated';
			$threat_level += 10;
		}

		// Check for spam activity items
		$spam_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}bp_activity 
				 WHERE type = %s AND hide_sitewide = %d",
				'activity_update',
				1
			)
		);
		if ( $spam_count > 50 ) {
			$issues[] = 'high_spam_activity_count';
			$threat_level += 15;
		}

		// Check rate limiting
		$rate_limit = get_option( 'bp_activity_rate_limit', 0 );
		if ( $rate_limit < 3 ) {
			$issues[] = 'no_rate_limiting';
			$threat_level += 10;
		}

		// Check for rapid activity posting
		$recent_activity = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}bp_activity 
				 WHERE date_recorded > %s",
				date( 'Y-m-d H:i:s', strtotime( '-1 hour' ) )
			)
		);
		if ( $recent_activity > 200 ) {
			$issues[] = 'excessive_activity_rate';
			$threat_level += 15;
		}

		// Check link posting restrictions
		$restrict_links = get_option( 'bp_activity_restrict_links', false );
		if ( ! $restrict_links ) {
			$issues[] = 'links_unrestricted';
			$threat_level += 10;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of activity spam issues */
				__( 'BuddyPress activity stream has spam protection gaps: %s. This can allow spam content, promotional links, and abuse that damages community engagement.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) )
			);

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => $description,
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/buddypress-activity-spam',
			);
		}
		
		return null;
	}
}
