<?php
/**
 * Mixpanel User Profiles Sync Diagnostic
 *
 * Mixpanel User Profiles Sync misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1384.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mixpanel User Profiles Sync Diagnostic Class
 *
 * @since 1.1384.0000
 */
class Diagnostic_MixpanelUserProfilesSync extends Diagnostic_Base {

	protected static $slug = 'mixpanel-user-profiles-sync';
	protected static $title = 'Mixpanel User Profiles Sync';
	protected static $description = 'Mixpanel User Profiles Sync misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		// Check for Mixpanel integration (via Segment, custom code, or plugins)
		$has_mixpanel = get_option( 'mixpanel_token', '' ) !== '' ||
		                get_option( 'segment_write_key', '' ) !== '' ||
		                defined( 'MIXPANEL_TOKEN' );
		
		if ( ! $has_mixpanel ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Mixpanel token configured
		$token = get_option( 'mixpanel_token', '' );
		if ( empty( $token ) && ! defined( 'MIXPANEL_TOKEN' ) ) {
			$issues[] = __( 'Mixpanel token not configured', 'wpshadow' );
		}
		
		// Check 2: User profile properties
		$profile_props = get_option( 'mixpanel_profile_properties', array() );
		if ( empty( $profile_props ) ) {
			$issues[] = __( 'No profile properties configured (limited user insights)', 'wpshadow' );
		}
		
		// Check 3: Identity linking
		$identity_linking = get_option( 'mixpanel_identity_linking', 'off' );
		if ( 'off' === $identity_linking ) {
			$issues[] = __( 'Identity linking disabled (duplicate user profiles)', 'wpshadow' );
		}
		
		// Check 4: Sync frequency
		$sync_frequency = get_option( 'mixpanel_sync_frequency', 'realtime' );
		if ( 'realtime' === $sync_frequency ) {
			$issues[] = __( 'Real-time sync (high API usage)', 'wpshadow' );
		}
		
		// Check 5: PII in profiles
		$track_email = get_option( 'mixpanel_track_email', 'yes' );
		if ( 'yes' === $track_email ) {
			$issues[] = __( 'Email addresses in profiles (GDPR concern)', 'wpshadow' );
		}
		
		// Check 6: Queue processing
		$use_queue = get_option( 'mixpanel_use_queue', 'no' );
		if ( 'no' === $use_queue ) {
			$issues[] = __( 'Synchronous API calls (page load delay)', 'wpshadow' );
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
				/* translators: %s: list of user profile sync issues */
				__( 'Mixpanel user profiles have %d sync issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/mixpanel-user-profiles-sync',
		);
	}
}
