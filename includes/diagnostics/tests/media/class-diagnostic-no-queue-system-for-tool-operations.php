<?php
/**
 * No Queue System for Tool Operations Diagnostic
 *
 * Tests for background processing queue availability.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26033.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * No Queue System for Tool Operations Diagnostic Class
 *
 * Tests for background processing queue availability.
 *
 * @since 1.26033.0000
 */
class Diagnostic_No_Queue_System_For_Tool_Operations extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-queue-system-for-tool-operations';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'No Queue System for Tool Operations';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests for background processing queue availability';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26033.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check for queue system availability.
		$has_queue = class_exists( 'WP_Background_Process' ) || class_exists( 'AsyncRequest' );

		if ( ! $has_queue ) {
			$issues[] = __( 'No background queue system available', 'wpshadow' );
		}

		// Check for scheduled event support.
		if ( ! function_exists( 'wp_schedule_event' ) ) {
			$issues[] = __( 'wp_schedule_event not available - cannot schedule tool operations', 'wpshadow' );
		}

		// Check cron job connectivity.
		$last_cron = get_transient( '_site_transient_doing_cron' );
		if ( empty( $last_cron ) ) {
			$issues[] = __( 'No recent cron activity detected - queue may not be processing', 'wpshadow' );
		}

		// Check for stuck queue jobs.
		$queued_jobs = get_transient( '_wpshadow_queue_jobs' );
		if ( ! empty( $queued_jobs ) && is_array( $queued_jobs ) && count( $queued_jobs ) > 100 ) {
			$issues[] = sprintf(
				/* translators: %d: number of queued jobs */
				__( '%d queued jobs detected - queue may be stuck', 'wpshadow' ),
				count( $queued_jobs )
			);
		}

		// Check for database transient storage.
		global $wpdb;
		$option_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE '%_queue_%'" );

		if ( $option_count > 1000 ) {
			$issues[] = sprintf(
				/* translators: %d: number of queue transients */
				__( '%d queue transients stored in DB - may impact performance', 'wpshadow' ),
				$option_count
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/no-queue-system-for-tool-operations',
			);
		}

		return null;
	}
}
