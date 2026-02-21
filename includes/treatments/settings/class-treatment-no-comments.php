<?php
/**
 * Treatment: No Comments on Old Posts
 *
 * Detects sites with zero comments on 50+ posts, suggesting low engagement
 * or disabled comments. Comments signal content quality to search engines.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.7030.1447
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * No Comments Treatment Class
 *
 * Checks for posts with zero comments, indicating low engagement.
 *
 * Detection methods:
 * - Comment count across posts
 * - Comment status (enabled/disabled)
 * - Posts older than 3 months with zero comments
 *
 * @since 1.7030.1447
 */
class Treatment_No_Comments extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-comments';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'No Comments on Old Posts';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Zero comments on 50+ posts suggests low engagement or disabled comments';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'user-engagement';

	/**
	 * Run the treatment check.
	 *
	 * Scoring system (4 points):
	 * - 1 point: Comments enabled
	 * - 1 point: <30% posts have zero comments
	 * - 1 point: <50% posts have zero comments
	 * - 1 point: Comment plugin active
	 *
	 * @since  1.7030.1447
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_No_Comments' );
	}
}
