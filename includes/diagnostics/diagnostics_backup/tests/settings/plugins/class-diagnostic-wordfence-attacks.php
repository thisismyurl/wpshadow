<?php
/**
 * Wordfence Blocked Attacks Diagnostic
 *
 * Analyzes recent blocked attack patterns.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5029.1800
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wordfence Blocked Attacks Class
 *
 * Reports on attack patterns and trends.
 *
 * @since 1.5029.1800
 */
class Diagnostic_Wordfence_Attacks extends Diagnostic_Base {

	protected static $slug        = 'wordfence-attacks';
	protected static $title       = 'Wordfence Blocked Attacks';
	protected static $description = 'Analyzes blocked attack patterns';
	protected static $family      = 'plugins';

	public static function check() {
		if ( ! class_exists( 'wordfence' ) ) {
			return null;
		}

		$cache_key = 'wpshadow_wordfence_attacks';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		global $wpdb;
		$wf_hits_table = $wpdb->prefix . 'wfHits';

		// Check if table exists.
		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$wf_hits_table}'" ) !== $wf_hits_table ) {
			return null;
		}

		// Get attack stats from last 7 days.
		$seven_days_ago = time() - ( 7 * DAY_IN_SECONDS );
		
		$attack_count = $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$wf_hits_table} WHERE ctime > %d AND action IN ('blocked:waf', 'blocked:country', 'throttled')",
			$seven_days_ago
		) );

		if ( $attack_count > 1000 ) {
			// High attack volume - analyze patterns.
			$top_ips = $wpdb->get_results( $wpdb->prepare(
				"SELECT IP, COUNT(*) as attempts FROM {$wf_hits_table} 
				WHERE ctime > %d AND action IN ('blocked:waf', 'blocked:country', 'throttled')
				GROUP BY IP ORDER BY attempts DESC LIMIT 10",
				$seven_days_ago
			), ARRAY_A );

			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: count */
					__( '%s attacks blocked in the last 7 days. Monitor for patterns.', 'wpshadow' ),
					number_format_i18n( $attack_count )
				),
				'severity'     => $attack_count > 10000 ? 'high' : 'medium',
				'threat_level' => $attack_count > 10000 ? 60 : 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/plugins-wordfence-attacks',
				'data'         => array(
					'total_attacks_7days' => $attack_count,
					'attacks_per_day' => round( $attack_count / 7 ),
					'top_attacking_ips' => $top_ips,
				),
			);

			set_transient( $cache_key, $result, 6 * HOUR_IN_SECONDS );
			return $result;
		}

		set_transient( $cache_key, null, 12 * HOUR_IN_SECONDS );
		return null;
	}
}
