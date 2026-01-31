<?php
/**
 * Amelia Calendar Sync Diagnostic
 *
 * Amelia calendar sync not configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.466.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Amelia Calendar Sync Diagnostic Class
 *
 * @since 1.466.0000
 */
class Diagnostic_AmeliaCalendarSync extends Diagnostic_Base {

	protected static $slug = 'amelia-calendar-sync';
	protected static $title = 'Amelia Calendar Sync';
	protected static $description = 'Amelia calendar sync not configured';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'AMELIA_VERSION' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Google Calendar integration enabled.
		$google_enabled = get_option( 'amelia_settings_googleCalendar_enabled', false );
		if ( ! $google_enabled ) {
			$issues[] = 'Google Calendar integration not enabled';
		}

		// Check 2: Google API credentials configured.
		if ( $google_enabled ) {
			$client_id = get_option( 'amelia_settings_googleCalendar_clientId', '' );
			$client_secret = get_option( 'amelia_settings_googleCalendar_clientSecret', '' );
			if ( empty( $client_id ) || empty( $client_secret ) ) {
				$issues[] = 'Google Calendar credentials not configured';
			}
		}

		// Check 3: Sync frequency configured.
		$sync_interval = get_option( 'amelia_settings_calendar_syncInterval', '' );
		if ( empty( $sync_interval ) ) {
			$issues[] = 'no calendar sync interval set';
		}

		// Check 4: Two-way sync enabled.
		$two_way = get_option( 'amelia_settings_googleCalendar_twoWaySync', false );
		if ( ! $two_way && $google_enabled ) {
			$issues[] = 'two-way calendar sync not enabled';
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 65, 40 + ( count( $issues ) * 6 ) );
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Calendar sync issues: ', 'wpshadow' ) . implode( ', ', $issues ),
				'severity'     => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/amelia-calendar-sync',
			);
		}

		return null;
	}
}
