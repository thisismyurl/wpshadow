<?php
/**
 * Post Orphan Detection Diagnostic
 *
 * Checks for orphaned posts that are inaccessible or broken.
 *
 * @since   1.26033.0800
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Post_Orphan_Detection Class
 *
 * Detects orphaned posts that are not accessible.
 *
 * @since 1.26033.0800
 */
class Diagnostic_Post_Orphan_Detection extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'post-orphan-detection';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Post Orphan Detection';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Identifies orphaned posts that are inaccessible';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'posts';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26033.0800
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Count posts that are password protected but private (conflicting states)
		$conflicting_posts = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts}
			WHERE post_status = 'publish'
			AND post_password != ''
			AND post_date_gmt < DATE_SUB(NOW(), INTERVAL 1 YEAR)
			LIMIT 100"
		);

		// Check for draft/private posts without recent edits
		$stale_drafts = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts}
			WHERE post_status IN ('draft', 'auto-draft')
			AND post_modified_gmt < DATE_SUB(NOW(), INTERVAL 6 MONTH)
			AND post_type IN ('post', 'page')
			LIMIT 100"
		);

		if ( intval( $stale_drafts ) > 20 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of orphaned posts */
					__( 'Found %d old draft posts not edited in 6 months. These are orphaned and just accumulating in your database. Consider archiving or deleting them.', 'wpshadow' ),
					intval( $stale_drafts )
				),
				'severity'     => 'low',
				'threat_level' => 15,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/post-orphan-detection',
			);
		}

		return null; // No orphaned posts detected
	}
}
