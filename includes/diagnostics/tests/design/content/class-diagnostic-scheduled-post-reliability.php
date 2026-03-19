<?php
/**
 * Scheduled Post Reliability Diagnostic
 *
 * Verifies scheduled posts publish at correct time.
 * Detects missed schedules and cron issues.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Tests
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Scheduled Post Reliability Diagnostic Class
 *
 * Checks for issues preventing scheduled posts from publishing
 * correctly at their designated time.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Scheduled_Post_Reliability extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'scheduled-post-reliability';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Scheduled Post Reliability';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies scheduled posts publish at correct time and detects cron issues';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'posts';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues = array();

		// Check for posts that missed their scheduled time.
		$missed_schedule = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->posts}
			WHERE post_status = 'future'
			AND post_date <= NOW()
			AND post_date > DATE_SUB(NOW(), INTERVAL 7 DAY)"
		);

		if ( $missed_schedule > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts */
				__( '%d scheduled posts missed their publish time', 'wpshadow' ),
				$missed_schedule
			);
		}

		// Check if WP-Cron is disabled.
		if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) {
			$issues[] = __( 'WP-Cron is disabled (scheduled posts require manual cron setup)', 'wpshadow' );
		}

		// Check if alternate cron is configured when WP-Cron is disabled.
		if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON && 
		     ! defined( 'ALTERNATE_WP_CRON' ) ) {
			$issues[] = __( 'No alternate cron configured while WP-Cron disabled', 'wpshadow' );
		}

		// Check if publish_future_post hook is registered.
		$scheduled_posts = wp_get_scheduled_event( 'publish_future_post' );
		if ( false === $scheduled_posts ) {
			// Check if there are any future posts.
			$has_future_posts = $wpdb->get_var(
				"SELECT COUNT(*)
				FROM {$wpdb->posts}
				WHERE post_status = 'future'"
			);

			if ( $has_future_posts > 0 ) {
				$issues[] = __( 'Future posts exist but no cron events scheduled', 'wpshadow' );
			}
		}

		// Check if wp-cron.php is accessible.
		$cron_url  = site_url( 'wp-cron.php' );
		$cron_test = wp_remote_get(
			$cron_url,
			array(
				'timeout' => 5,
				'blocking' => true,
			)
		);

		if ( is_wp_error( $cron_test ) ) {
			$issues[] = __( 'WP-Cron endpoint not accessible (scheduled posts may fail)', 'wpshadow' );
		}

		// Check for excessive cron events that might delay post publishing.
		$cron_array = _get_cron_array();
		$total_cron_events = 0;
		if ( is_array( $cron_array ) ) {
			foreach ( $cron_array as $timestamp => $cron ) {
				$total_cron_events += count( $cron );
			}
		}

		if ( $total_cron_events > 100 ) {
			$issues[] = sprintf(
				/* translators: %d: number of events */
				__( '%d cron events queued (high load may delay scheduled posts)', 'wpshadow' ),
				$total_cron_events
			);
		}

		// Check for very old future posts (likely stuck).
		$old_future_posts = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->posts}
			WHERE post_status = 'future'
			AND post_date < DATE_SUB(NOW(), INTERVAL 7 DAY)"
		);

		if ( $old_future_posts > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts */
				__( '%d posts stuck in "future" status for over 7 days', 'wpshadow' ),
				$old_future_posts
			);
		}

		// Check timezone configuration.
		$timezone_string = get_option( 'timezone_string' );
		$gmt_offset      = get_option( 'gmt_offset' );

		if ( empty( $timezone_string ) && $gmt_offset == 0 ) {
			$issues[] = __( 'Timezone not configured (scheduled posts may publish at wrong time)', 'wpshadow' );
		}

		// Check for future posts with dates far in the future.
		$far_future_posts = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->posts}
			WHERE post_status = 'future'
			AND post_date > DATE_ADD(NOW(), INTERVAL 1 YEAR)"
		);

		if ( $far_future_posts > 5 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts */
				__( '%d posts scheduled over 1 year in future', 'wpshadow' ),
				$far_future_posts
			);
		}

		// Check server time vs WordPress time.
		$server_time = time();
		$wp_time     = current_time( 'timestamp' );
		$time_diff   = abs( $server_time - $wp_time );

		if ( $time_diff > 300 ) { // 5 minutes difference.
			$issues[] = sprintf(
				/* translators: %d: time difference in minutes */
				__( 'Server time and WordPress time differ by %d minutes', 'wpshadow' ),
				round( $time_diff / 60 )
			);
		}

		// Check for cron spawning issues (low traffic sites).
		$last_cron_run = get_option( '_transient_doing_cron' );
		if ( $last_cron_run && ( time() - $last_cron_run ) > 3600 ) {
			$issues[] = __( 'WP-Cron may not be running regularly (low site traffic?)', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'high',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/scheduled-post-reliability',
			);
		}

		return null;
	}
}
