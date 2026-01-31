<?php
/**
 * BuddyPress Member Privacy Diagnostic
 *
 * BuddyPress member profiles exposed.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.514.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BuddyPress Member Privacy Diagnostic Class
 *
 * @since 1.514.0000
 */
class Diagnostic_BuddypressMemberPrivacy extends Diagnostic_Base {

	protected static $slug = 'buddypress-member-privacy';
	protected static $title = 'BuddyPress Member Privacy';
	protected static $description = 'BuddyPress member profiles exposed';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'BuddyPress' ) ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		$threat_level = 0;

		// Check default profile visibility
		$default_visibility = get_option( 'bp-xprofile-default-visibility', 'public' );
		if ( $default_visibility === 'public' ) {
			$issues[] = 'profiles_public_by_default';
			$threat_level += 20;
		}

		// Check if members can hide their profiles
		$allow_custom_visibility = get_option( 'bp-xprofile-allow-custom-visibility', 'disabled' );
		if ( $allow_custom_visibility === 'disabled' ) {
			$issues[] = 'no_custom_visibility_control';
			$threat_level += 15;
		}

		// Check activity stream privacy
		$activity_privacy = get_option( 'bp_activity_privacy_enabled', false );
		if ( ! $activity_privacy ) {
			$issues[] = 'activity_always_public';
			$threat_level += 15;
		}

		// Check friend list visibility
		$hide_friendships = get_option( 'bp_hide_friendship_requests', false );
		if ( ! $hide_friendships ) {
			$issues[] = 'friend_lists_exposed';
			$threat_level += 10;
		}

		// Check member directory restrictions
		$directory_restricted = get_option( 'bp_restrict_member_directory', false );
		if ( ! $directory_restricted ) {
			$issues[] = 'member_directory_public';
			$threat_level += 10;
		}

		// Check for public email addresses
		$public_emails = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}bp_xprofile_data xd
				 INNER JOIN {$wpdb->prefix}bp_xprofile_fields xf ON xd.field_id = xf.id
				 WHERE xf.type = %s AND xd.value != ''",
				'email'
			)
		);
		if ( $public_emails > 0 ) {
			$issues[] = 'email_addresses_visible';
			$threat_level += 20;
		}

		// Check private messaging settings
		$restrict_messages = get_option( 'bp_restrict_private_messaging', 'all' );
		if ( $restrict_messages === 'all' ) {
			$issues[] = 'anyone_can_message';
			$threat_level += 10;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of member privacy issues */
				__( 'BuddyPress member privacy has concerns: %s. This can expose personal information, enable harassment, and violate user trust and GDPR requirements.', 'wpshadow' ),
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
				'kb_link'     => 'https://wpshadow.com/kb/buddypress-member-privacy',
			);
		}
		
		return null;
	}
}
