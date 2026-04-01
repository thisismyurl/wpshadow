<?php
/**
 * Post Dates and Timestamps Diagnostic
 *
 * Tests whether post timestamps remain correct after import.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post Dates and Timestamps Diagnostic Class
 *
 * Tests whether post creation, modification, and publish dates remain correct.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Post_Dates_And_Timestamps extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'post-dates-and-timestamps';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Post Dates and Timestamps';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether post timestamps remain correct after import';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues = array();

		// Check for posts with future dates that are published.
		$future_published = $wpdb->get_var( "
			SELECT COUNT(*)
			FROM {$wpdb->posts}
			WHERE post_type IN ('post', 'page')
			AND post_status = 'publish'
			AND post_date > NOW()
		" );

		if ( $future_published > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of published posts with future dates */
				__( '%d published posts have future publish dates', 'wpshadow' ),
				$future_published
			);
		}

		// Check for posts with same timestamps (batch import indicator).
		$same_timestamps = $wpdb->get_results( "
			SELECT post_date, COUNT(*) as count
			FROM {$wpdb->posts}
			WHERE post_type IN ('post', 'page')
			GROUP BY post_date
			HAVING count > 5
			LIMIT 5
		" );

		if ( ! empty( $same_timestamps ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts with identical timestamps */
				__( 'Found %d posts with identical timestamps (batch import pattern)', 'wpshadow' ),
				count( $same_timestamps )
			);
		}

		// Check for timezone-related issues (posts from different timezones).
		$timezone_option = get_option( 'timezone_string' );
		if ( empty( $timezone_option ) ) {
			$timezone_option = get_option( 'gmt_offset' );
			if ( $timezone_option == 0 ) {
				$issues[] = __( 'Timezone not properly configured', 'wpshadow' );
			}
		}

		// Check if post_date and post_date_gmt are synchronized.
		$async_dates = $wpdb->get_var( "
			SELECT COUNT(*)
			FROM {$wpdb->posts}
			WHERE post_type IN ('post', 'page')
			AND post_date != DATE_ADD(post_date_gmt, INTERVAL (
				SELECT CAST(option_value AS SIGNED)
				FROM {$wpdb->options}
				WHERE option_name = 'gmt_offset'
			) HOUR)
			LIMIT 1
		" );

		if ( $async_dates > 0 ) {
			$issues[] = __( 'Post dates and GMT dates are not synchronized', 'wpshadow' );
		}

		// Check for posts with post_modified earlier than post_date.
		$modified_before_created = $wpdb->get_var( "
			SELECT COUNT(*)
			FROM {$wpdb->posts}
			WHERE post_type IN ('post', 'page')
			AND post_modified < post_date
			LIMIT 1
		" );

		if ( $modified_before_created > 0 ) {
			$issues[] = __( 'Some posts have modification date earlier than creation date', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/post-dates-and-timestamps?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
