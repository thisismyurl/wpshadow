<?php
/**
 * Comments Auto Close Old Posts Diagnostic (Stub)
 *
 * TODO stub mapped to the settings gauge.
 *
 * @package WPShadow
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
 * Diagnostic_Comments_Auto_Close_Old_Posts Class
 *
 * TODO: Implement full test logic and remediation guidance.
 */
class Diagnostic_Comments_Auto_Close_Old_Posts extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'comments-auto-close-old-posts';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Comments Auto Close Old Posts';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether WordPress is configured to automatically close comments on posts older than a set number of days. Leaving comments permanently open on all posts is an ever-growing spam surface.';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'settings';

	/**
	 * Severity of the finding.
	 *
	 * @var string
	 */
	protected static $severity = 'low';

	/**
	 * Estimated minutes to resolve.
	 *
	 * @var int
	 */
	protected static $time_to_fix_minutes = 5;

	/**
	 * Business impact statement.
	 *
	 * @var string
	 */
	protected static $impact = 'Permanently open comments on old posts continuously expand the spam attack surface and increase moderation workload without a commensurate engagement benefit.';

	/**
	 * Run the diagnostic check.
	 *
	 * TODO Test Plan:
	 * - Read get_option('close_comments_for_old_posts').
	 * - Read get_option('close_comments_days_old').
	 * - Flag if close_comments_for_old_posts is '0' (disabled) and the site
	 *   has comments enabled at all (get_option('default_comment_status')
	 *   === 'open').
	 * - Return null (healthy) when auto-close is enabled with a reasonable
	 *   threshold (<= 180 days) or when comments are globally disabled.
	 *
	 * TODO Fix Plan:
	 * - Guide the user to Settings > Discussion > Automatically close comments.
	 * - Use update_option('close_comments_for_old_posts', '1') and set a
	 *   threshold via update_option('close_comments_days_old', 60).
	 * - Do not modify WordPress core files.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array if issue exists, null if healthy.
	 */
	public static function check() {
		// TODO: Implement testable logic.
		return null;
	}
}
