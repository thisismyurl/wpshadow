<?php
/**
 * Post Revisions Cleanup Not Scheduled Diagnostic
 *
 * Checks if post revisions cleanup is scheduled.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2347
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post Revisions Cleanup Not Scheduled Diagnostic Class
 *
 * Detects unscheduled post revisions cleanup.
 *
 * @since 1.2601.2347
 */
class Diagnostic_Post_Revisions_Cleanup_Not_Scheduled extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'post-revisions-cleanup-not-scheduled';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Post Revisions Cleanup Not Scheduled';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if post revisions cleanup is scheduled';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2347
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if post revisions limit is set
		if ( ! defined( 'WP_POST_REVISIONS' ) || WP_POST_REVISIONS === true ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Post revisions cleanup is not scheduled. Limit post revisions to reduce database bloat and improve performance.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 20,
				'auto_fixable'  => true,
				'kb_link'       => 'https://wpshadow.com/kb/post-revisions-cleanup-not-scheduled',
			);
		}

		return null;
	}
}
