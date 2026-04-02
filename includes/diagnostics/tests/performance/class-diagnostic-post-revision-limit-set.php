<?php
/**
 * Post Revision Limit Set Diagnostic
 *
 * Checks whether WP_POST_REVISIONS is set to a finite number, preventing
 * unlimited revision accumulation that bloats the posts table over time.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post Revision Limit Set Diagnostic Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_Post_Revision_Limit_Set extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'post-revision-limit-set';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Post Revision Limit Set';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether WordPress post revisions have been limited or disabled, as unlimited revisions can significantly bloat the database over time.';

	/**
	 * Gauge family/category for dashboard placement.
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * Reads the WP_POST_REVISIONS constant and flags when revisions are unlimited
	 * (true or undefined) or set higher than the recommended maximum.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array when revisions are uncapped or excessive, null when healthy.
	 */
	public static function check() {
		// WP_POST_REVISIONS = true (or not defined) means unlimited revisions.
		// false = disabled. An integer = max revisions per post.
		$revisions_setting = defined( 'WP_POST_REVISIONS' ) ? WP_POST_REVISIONS : true;

		// Already limited or disabled.
		if ( false === $revisions_setting ) {
			return null;
		}

		if ( is_int( $revisions_setting ) && $revisions_setting <= 10 ) {
			return null;
		}

		// Count total revisions in the database.
		global $wpdb;
		$revision_count = (int) $wpdb->get_var(
			"SELECT COUNT(*)
			 FROM {$wpdb->posts}
			 WHERE post_type = 'revision'"
		);

		if ( $revision_count <= 200 ) {
			return null; // Low volume — not yet a problem.
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: revision count 2: current setting */
				__( '%1$d post revisions are stored in the database and WP_POST_REVISIONS is set to "%2$s" (unlimited). Uncapped revisions grow the posts table indefinitely, increasing database size and slowing queries. Define WP_POST_REVISIONS as an integer (e.g., 5) in wp-config.php and clean up old revisions with WP-Optimize or WP-CLI.', 'wpshadow' ),
				$revision_count,
				true === $revisions_setting ? 'true (unlimited)' : $revisions_setting
			),
			'severity'     => 'low',
			'threat_level' => 20,
			'kb_link'      => 'https://wpshadow.com/kb/post-revision-limit?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'      => array(
				'revision_count'    => $revision_count,
				'wp_post_revisions' => $revisions_setting,
			),
		);
	}
}
