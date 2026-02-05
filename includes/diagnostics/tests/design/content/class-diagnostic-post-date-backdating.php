<?php
/**
 * Post Date Backdating Diagnostic
 *
 * Verifies backdating posts works correctly. Tests post_date vs post_date_gmt handling
 * to ensure dates are properly stored and displayed across timezones.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6030.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post Date Backdating Diagnostic Class
 *
 * Checks for issues with backdating posts and GMT offset handling.
 *
 * @since 1.6030.2148
 */
class Diagnostic_Post_Date_Backdating extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'post-date-backdating';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Post Date Backdating';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies backdating posts works correctly and GMT offset is properly handled';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'posts';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues = array();

		// Check for mismatched post_date vs post_date_gmt (should differ by timezone offset).
		$gmt_offset = get_option( 'gmt_offset', 0 );
		$timezone_string = get_option( 'timezone_string', '' );

		// Check for posts where the difference doesn't match timezone settings.
		$mismatched = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*)
				FROM {$wpdb->posts}
				WHERE post_status = 'publish'
				AND post_date != '0000-00-00 00:00:00'
				AND post_date_gmt != '0000-00-00 00:00:00'
				AND ABS(TIMESTAMPDIFF(HOUR, post_date, post_date_gmt)) > %d
				LIMIT 1",
				abs( (int) $gmt_offset ) + 2
			)
		);

		if ( $mismatched > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts */
				__( '%d posts have mismatched date/GMT offset', 'wpshadow' ),
				$mismatched
			);
		}

		// Check for future dates that are too far ahead (possible backdating errors).
		$future_posts = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*)
				FROM {$wpdb->posts}
				WHERE post_date > DATE_ADD(NOW(), INTERVAL %d DAY)
				AND post_status IN ('publish', 'future')",
				365
			)
		);

		if ( $future_posts > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts */
				__( '%d posts scheduled more than 1 year in the future', 'wpshadow' ),
				$future_posts
			);
		}

		// Check for posts with dates before WordPress epoch (2003-01-01).
		$ancient_posts = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->posts}
			WHERE post_date < '2003-01-01 00:00:00'
			AND post_status = 'publish'"
		);

		if ( $ancient_posts > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts */
				__( '%d posts dated before 2003 (WordPress epoch)', 'wpshadow' ),
				$ancient_posts
			);
		}

		// Check if timezone setting is empty (could cause issues).
		if ( empty( $timezone_string ) && 0 === (float) $gmt_offset ) {
			$issues[] = __( 'No timezone configured (defaults to UTC)', 'wpshadow' );
		}

		// Check for posts where post_date and post_date_gmt are identical (should differ by offset).
		if ( ! empty( $timezone_string ) || 0 !== (float) $gmt_offset ) {
			$identical_dates = $wpdb->get_var(
				"SELECT COUNT(*)
				FROM {$wpdb->posts}
				WHERE post_status = 'publish'
				AND post_date = post_date_gmt
				AND post_date != '0000-00-00 00:00:00'"
			);

			if ( $identical_dates > 0 ) {
				$issues[] = sprintf(
					/* translators: %d: number of posts */
					__( '%d posts have identical local/GMT dates (timezone not applied)', 'wpshadow' ),
					$identical_dates
				);
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( '. ', $issues ),
				'severity'    => 'medium',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/post-date-backdating',
			);
		}

		return null;
	}
}
