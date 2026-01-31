<?php
/**
 * Average Session Duration Below 1 Minute Diagnostic
 *
 * Measures average session duration from analytics to detect engagement issues.
 * Low session duration indicates poor content quality or user experience problems.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\UX
 * @since      1.6028.2149
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Average_Session_Duration_Low Class
 *
 * Checks if average session duration is below 1 minute, indicating poor engagement.
 * Queries analytics platforms to measure time users spend on site.
 *
 * @since 1.6028.2149
 */
class Diagnostic_Average_Session_Duration_Low extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'average-session-duration-low';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Average Session Duration Below 1 Minute';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Measures average session duration from analytics';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'ux';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6028.2149
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$cached = get_transient( 'wpshadow_diagnostic_session_duration' );
		if ( false !== $cached ) {
			return $cached;
		}

		$analytics_data = self::get_analytics_session_data();

		if ( ! $analytics_data ) {
			set_transient( 'wpshadow_diagnostic_session_duration', null, 6 * HOUR_IN_SECONDS );
			return null;
		}

		$avg_duration_seconds = (float) $analytics_data['average_duration'];
		$avg_duration_minutes = $avg_duration_seconds / 60;

		// <1 minute is critical, 1-2 minutes is warning
		if ( $avg_duration_minutes >= 2 ) {
			set_transient( 'wpshadow_diagnostic_session_duration', null, 6 * HOUR_IN_SECONDS );
			return null;
		}

		$severity     = $avg_duration_minutes < 1 ? 'high' : 'medium';
		$threat_level = $avg_duration_minutes < 1 ? 70 : 50;

		$finding = array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: average session duration in minutes */
				__( 'Average session duration is only %s minutes, indicating poor engagement or content quality issues', 'wpshadow' ),
				number_format( $avg_duration_minutes, 1 )
			),
			'severity'       => $severity,
			'threat_level'   => $threat_level,
			'auto_fixable'   => false,
			'kb_link'        => 'https://wpshadow.com/kb/session-duration',
			'meta'           => array(
				'average_duration_seconds' => $avg_duration_seconds,
				'average_duration_minutes' => round( $avg_duration_minutes, 2 ),
				'data_source'              => $analytics_data['source'],
			),
			'details'        => array(
				sprintf(
					/* translators: %s: duration */
					__( 'Current average session duration: %s minutes', 'wpshadow' ),
					number_format( $avg_duration_minutes, 1 )
				),
				__( 'Industry benchmark: 2-3 minutes for content sites', 'wpshadow' ),
				__( 'Low session duration can indicate poor content relevance, slow page speed, or UX issues', 'wpshadow' ),
			),
			'recommendations' => array(
				__( 'Improve content quality and relevance to target audience', 'wpshadow' ),
				__( 'Optimize page load speed to reduce bounce rate', 'wpshadow' ),
				__( 'Enhance internal linking to encourage exploration', 'wpshadow' ),
				__( 'Review traffic sources for quality (avoid click-bait)', 'wpshadow' ),
				__( 'Analyze top exit pages for UX improvements', 'wpshadow' ),
			),
		);

		set_transient( 'wpshadow_diagnostic_session_duration', $finding, 6 * HOUR_IN_SECONDS );
		return $finding;
	}

	/**
	 * Get analytics session duration data.
	 *
	 * Queries multiple analytics platforms to get average session duration.
	 *
	 * @since  1.6028.2149
	 * @return array|false Analytics data or false if unavailable.
	 */
	private static function get_analytics_session_data() {
		// Try MonsterInsights.
		if ( class_exists( 'MonsterInsights_Reports' ) && function_exists( 'monsterinsights_get_ua_reports' ) ) {
			$reports = monsterinsights_get_ua_reports();
			if ( ! empty( $reports['overview']['data']['averageSessionDuration'] ) ) {
				return array(
					'average_duration' => (float) $reports['overview']['data']['averageSessionDuration'],
					'source'           => 'MonsterInsights',
				);
			}
		}

		// Try Google Site Kit.
		if ( class_exists( '\Google\Site_Kit\Core\Modules\Analytics' ) ) {
			$analytics_data = get_option( 'googlesitekit_analytics_data', array() );
			if ( ! empty( $analytics_data['avgSessionDuration'] ) ) {
				return array(
					'average_duration' => (float) $analytics_data['avgSessionDuration'],
					'source'           => 'Google Site Kit',
				);
			}
		}

		// Try Jetpack Stats.
		if ( class_exists( 'Jetpack_Stats' ) && function_exists( 'stats_get_csv' ) ) {
			$stats = stats_get_csv( 'stats', array( 'days' => 30 ) );
			if ( ! empty( $stats[0]['avg_time'] ) ) {
				return array(
					'average_duration' => (float) $stats[0]['avg_time'],
					'source'           => 'Jetpack',
				);
			}
		}

		// Try custom WPShadow stored data.
		$custom_data = get_option( 'wpshadow_analytics_session_duration' );
		if ( ! empty( $custom_data['average_duration'] ) ) {
			return array(
				'average_duration' => (float) $custom_data['average_duration'],
				'source'           => 'WPShadow Custom',
			);
		}

		return false;
	}
}
