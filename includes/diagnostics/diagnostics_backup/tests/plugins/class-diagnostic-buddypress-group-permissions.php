<?php
/**
 * BuddyPress Group Permissions Diagnostic
 *
 * BuddyPress group permissions incorrect.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.515.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BuddyPress Group Permissions Diagnostic Class
 *
 * @since 1.515.0000
 */
class Diagnostic_BuddypressGroupPermissions extends Diagnostic_Base {

	protected static $slug = 'buddypress-group-permissions';
	protected static $title = 'BuddyPress Group Permissions';
	protected static $description = 'BuddyPress group permissions incorrect';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'BuddyPress' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Verify default group creation settings
		$restrict_group_creation = get_option( 'bp_restrict_group_creation', 0 );
		if ( ! $restrict_group_creation ) {
			$issues[] = __( 'Group creation not restricted to admins', 'wpshadow' );
		}

		// Check 2: Check group invitation permissions
		$invite_status = get_option( 'bp_group_invite_status', 'members' );
		if ( 'members' === $invite_status ) {
			$issues[] = __( 'Group invitation permissions too permissive', 'wpshadow' );
		}

		// Check 3: Verify group document upload restrictions
		$restrict_uploads = get_option( 'bp_group_restrict_document_uploads', 0 );
		if ( ! $restrict_uploads ) {
			$issues[] = __( 'Group document upload restrictions not configured', 'wpshadow' );
		}

		// Check 4: Check group activity moderation
		$activity_moderation = get_option( 'bp_group_activity_moderation', 0 );
		if ( ! $activity_moderation ) {
			$issues[] = __( 'Group activity moderation not enabled', 'wpshadow' );
		}

		// Check 5: Verify group member role capabilities
		$member_caps_defined = get_option( 'bp_group_member_caps', array() );
		if ( empty( $member_caps_defined ) ) {
			$issues[] = __( 'Group member role capabilities not properly defined', 'wpshadow' );
		}

		// Check 6: Check group visibility default settings
		$default_visibility = get_option( 'bp_group_default_visibility', 'public' );
		if ( 'public' === $default_visibility ) {
			$issues[] = __( 'Default group visibility set to public', 'wpshadow' );
		}
		// Additional checks
		if ( ! function_exists( 'wp_verify_nonce' ) ) {
			$issues[] = __( 'Nonce verification unavailable', 'wpshadow' );
		}
		return null;
	}
}
