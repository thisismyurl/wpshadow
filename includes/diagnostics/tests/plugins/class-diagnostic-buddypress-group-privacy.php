<?php
/**
 * BuddyPress Group Privacy Diagnostic
 *
 * BuddyPress groups have weak privacy settings.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.236.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BuddyPress Group Privacy Diagnostic Class
 *
 * @since 1.236.0000
 */
class Diagnostic_BuddypressGroupPrivacy extends Diagnostic_Base {

	protected static $slug = 'buddypress-group-privacy';
	protected static $title = 'BuddyPress Group Privacy';
	protected static $description = 'BuddyPress groups have weak privacy settings';
	protected static $family = 'security';

	public static function check() {
		if ( ! function_exists( 'buddypress' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Group privacy levels enforced
		$privacy_levels = get_option( 'bp_group_privacy_levels', false );
		if ( ! $privacy_levels ) {
			$issues[] = 'Group privacy levels not enforced';
		}
		
		// Check 2: Default group privacy set to private
		$default_privacy = get_option( 'bp_group_default_privacy', 'public' );
		if ( 'public' === $default_privacy ) {
			$issues[] = 'Default group privacy set to public';
		}
		
		// Check 3: Invite-only groups supported
		$invite_only = get_option( 'bp_group_invite_only_enabled', false );
		if ( ! $invite_only ) {
			$issues[] = 'Invite-only groups not supported';
		}
		
		// Check 4: Private group directory listing restricted
		$directory_restrict = get_option( 'bp_group_directory_restricted', false );
		if ( ! $directory_restrict ) {
			$issues[] = 'Private groups visible in directory';
		}
		
		// Check 5: Member visibility controls
		$member_visibility = get_option( 'bp_group_member_visibility', false );
		if ( ! $member_visibility ) {
			$issues[] = 'Member visibility controls disabled';
		}
		
		// Check 6: Activity privacy enforcement
		$activity_privacy = get_option( 'bp_group_activity_privacy', false );
		if ( ! $activity_privacy ) {
			$issues[] = 'Activity privacy not enforced';
		}
		
		if ( ! empty( $issues ) ) {
			$threat_level = min( 75, 45 + ( count( $issues ) * 5 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'BuddyPress group privacy issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/buddypress-group-privacy',
			);
		}
		// Additional checks
		if ( ! function_exists( 'wp_verify_nonce' ) ) {
			$issues[] = __( 'Nonce verification unavailable', 'wpshadow' );
		}
		return null;
	}
}
