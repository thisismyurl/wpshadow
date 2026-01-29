<?php
/**
 * Scheduled Content Queue
 *
 * Verifies that content is scheduled for future publication, indicating
 * active content planning and editorial calendar management.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Content
 * @since      1.6029.1102
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Scheduled Content Queue Diagnostic Class
 *
 * Checks if content is scheduled for future publication.
 *
 * @since 1.6029.1102
 */
class Diagnostic_Scheduled_Content_Queue extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'scheduled-content-queue';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Scheduled Content Queue';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies upcoming content scheduled for publication';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6029.1102
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$cache_key = 'wpshadow_scheduled_content_check';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$scheduled = self::get_scheduled_content();

		if ( $scheduled['count'] > 0 ) {
			set_transient( $cache_key, null, 12 * HOUR_IN_SECONDS );
			return null;
		}

		$finding = array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'No content scheduled for future publication. Content planning may be inactive.', 'wpshadow' ),
			'severity'     => 'low',
			'threat_level' => 20,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/scheduled-content',
			'meta'         => array(
				'scheduled_count' => 0,
				'last_scheduled'  => $scheduled['last_scheduled'],
			),
			'details'      => array(
				__( 'No posts or pages scheduled for future publication', 'wpshadow' ),
				__( 'May indicate lack of content planning', 'wpshadow' ),
				__( 'Regular scheduling improves consistency', 'wpshadow' ),
			),
			'recommendation' => __( 'Schedule content in advance to maintain consistent publishing schedule.', 'wpshadow' ),
		);

		set_transient( $cache_key, $finding, 12 * HOUR_IN_SECONDS );
		return $finding;
	}

	/**
	 * Get scheduled content.
	 *
	 * @since  1.6029.1102
	 * @return array Scheduled content data.
	 */
	private static function get_scheduled_content() {
		global $wpdb;

		// Count future scheduled posts.
		$count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*)
				FROM {$wpdb->posts}
				WHERE post_status = 'future'
				AND post_date > %s",
				current_time( 'mysql' )
			)
		);

		// Get last scheduled post date.
		$last_scheduled = $wpdb->get_var(
			"SELECT MAX(post_date)
			FROM {$wpdb->posts}
			WHERE post_status = 'future'"
		);

		return array(
			'count'          => (int) $count,
			'last_scheduled' => $last_scheduled,
		);
	}
}
