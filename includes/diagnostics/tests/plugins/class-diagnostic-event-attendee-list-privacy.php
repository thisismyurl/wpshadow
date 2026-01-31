<?php
/**
 * Event Attendee List Privacy Diagnostic
 *
 * Event attendee lists exposed.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.593.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Event Attendee List Privacy Diagnostic Class
 *
 * @since 1.593.0000
 */
class Diagnostic_EventAttendeeListPrivacy extends Diagnostic_Base {

	protected static $slug = 'event-attendee-list-privacy';
	protected static $title = 'Event Attendee List Privacy';
	protected static $description = 'Event attendee lists exposed';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'Tribe__Events__Main' ) && ! class_exists( 'MEC_Main' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Verify attendee list visibility
		$attendee_list = get_option( 'event_attendee_list_visibility', 'public' );
		if ( 'public' === $attendee_list ) {
			$issues[] = 'Attendee lists are publicly visible';
		}

		// Check 2: Check for attendee export restrictions
		$export_restriction = get_option( 'event_attendee_export_restriction', 0 );
		if ( ! $export_restriction ) {
			$issues[] = 'Attendee export not restricted';
		}

		// Check 3: Verify email masking
		$email_masking = get_option( 'event_attendee_email_masking', 0 );
		if ( ! $email_masking ) {
			$issues[] = 'Attendee emails not masked';
		}

		// Check 4: Check for consent requirement
		$consent_required = get_option( 'event_attendee_consent_required', 0 );
		if ( ! $consent_required ) {
			$issues[] = 'Attendee consent for list display not required';
		}

		// Check 5: Verify access control
		$access_control = get_option( 'event_attendee_access_control', 0 );
		if ( ! $access_control ) {
			$issues[] = 'Attendee list access control not configured';
		}

		// Check 6: Check for list caching
		$list_cache = get_option( 'event_attendee_list_cache', 0 );
		if ( $list_cache ) {
			$issues[] = 'Attendee list caching enabled without privacy checks';
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 70;
			$threat_multiplier = 5;
			$max_threat = 95;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d event attendee privacy issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/event-attendee-list-privacy',
			);
		}

		return null;
	}
}
