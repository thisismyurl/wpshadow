<?php
/**
 * BuddyPress Activity Stream Diagnostic
 *
 * BuddyPress activity stream slow.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.513.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BuddyPress Activity Stream Diagnostic Class
 *
 * @since 1.513.0000
 */
class Diagnostic_BuddypressActivityStream extends Diagnostic_Base {

	protected static $slug = 'buddypress-activity-stream';
	protected static $title = 'BuddyPress Activity Stream';
	protected static $description = 'BuddyPress activity stream slow';
	protected static $family = 'performance';

	public static function check() {
		if ( ! class_exists( 'BuddyPress' ) ) {
			return null;
		}

		global $wpdb;
		$issues = array();

		// Check 1: Activity count
		$activity_count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->prefix}bp_activity"
		);

		if ( $activity_count > 50000 ) {
			$issues[] = sprintf( __( '%s activities (slow queries)', 'wpshadow' ), number_format( $activity_count ) );
		}

		// Check 2: Activity caching
		$cache_enabled = get_option( 'bp_activity_cache', 0 );
		if ( ! $cache_enabled ) {
			$issues[] = __( 'Activity not cached (redundant queries)', 'wpshadow' );
		}

		// Check 3: Per page limit
		$per_page = get_option( 'bp_activity_per_page', 20 );
		if ( $per_page > 50 ) {
			$issues[] = sprintf( __( '%d items per page (slow loading)', 'wpshadow' ), $per_page );
		}

		// Check 4: Auto-load more
		$auto_load = get_option( 'bp_activity_auto_load', 'yes' );
		if ( 'yes' === $auto_load ) {
			$issues[] = __( 'Auto-load enabled (continuous DB queries)', 'wpshadow' );
		}

		// Check 5: Activity cleanup
		$cleanup = get_option( 'bp_activity_cleanup', 'no' );
		if ( 'no' === $cleanup ) {
			$issues[] = __( 'Old activities never deleted (database bloat)', 'wpshadow' );
		}

		// Check 6: Akismet integration
		$akismet = get_option( 'bp_activity_akismet', 'no' );
		if ( 'no' === $akismet && function_exists( 'akismet_http_post' ) ) {
			$issues[] = __( 'Akismet available but not used (spam)', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		$threat_level = 55;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 67;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 61;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				__( 'BuddyPress activity stream has %d performance issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/buddypress-activity-stream',
		);
	}
}
