<?php
/**
 * Diagnostic: No Scheduled Future Content
 *
 * Detects absence of scheduled posts. Zero buffer creates stress, reduces
 * consistency, and increases risk of publishing gaps.
 *
 * Issue: https://github.com/thisismyurl/wpshadow/issues/4384
 *
 * @package    WPShadow
 * @subpackage Diagnostics\ContentStrategy
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * No Scheduled Content Diagnostic
 *
 * Checks if site has scheduled posts ready. A buffer of scheduled content
 * improves consistency and reduces publishing stress.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Content_No_Scheduled_Content extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-scheduled-content';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'No Scheduled Future Content';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects absence of content buffer that increases consistency risk';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'content-strategy';

	/**
	 * Check for scheduled content.
	 *
	 * Checks if site has posts scheduled for future publication. Recommended
	 * buffer is 2-4 weeks of scheduled content.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Count scheduled posts.
		$scheduled_count = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*)
				FROM {$wpdb->posts}
				WHERE post_type = 'post'
				AND post_status = 'future'
				AND post_date > %s",
				current_time( 'mysql' )
			)
		);

		if ( $scheduled_count > 0 ) {
			// Has scheduled content.
			return null;
		}

		// Check recent publishing activity to determine if this is a real issue.
		$thirty_days_ago = gmdate( 'Y-m-d H:i:s', strtotime( '-30 days' ) );
		
		$recent_posts = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*)
				FROM {$wpdb->posts}
				WHERE post_type = 'post'
				AND post_status = 'publish'
				AND post_date > %s",
				$thirty_days_ago
			)
		);

		if ( $recent_posts < 2 ) {
			// Site is not actively publishing, so no buffer needed.
			return null;
		}

		// Medium severity - this is a planning/workflow issue.
		$threat_level = 55;

		// Check if site is actively publishing (4+ posts in last 30 days).
		if ( $recent_posts >= 4 ) {
			$threat_level = 60; // More active sites need buffer more.
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %d: recent post count */
				__(
					'No scheduled content buffer. You published %d posts in the last 30 days but have zero scheduled for future publication. A buffer of 2-4 weeks reduces stress and improves consistency.',
					'wpshadow'
				),
				$recent_posts
			),
			'severity'     => 'medium',
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/scheduled-content-buffer',
		);
	}
}
