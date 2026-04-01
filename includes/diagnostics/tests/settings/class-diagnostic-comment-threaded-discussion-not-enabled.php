<?php
/**
 * Comment Threaded Discussion Not Enabled Diagnostic
 *
 * Checks if threaded comments are enabled.
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
 * Comment Threaded Discussion Not Enabled Diagnostic Class
 *
 * Detects disabled threaded comments.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Comment_Threaded_Discussion_Not_Enabled extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-threaded-discussion-not-enabled';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Threaded Discussion Not Enabled';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if threaded comments are enabled';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if threaded comments are enabled
		if ( ! get_option( 'thread_comments' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Threaded discussion for comments is not enabled. Enable comment threading to improve user engagement and discussion quality.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 10,
				'auto_fixable'  => true,
				'kb_link'       => 'https://wpshadow.com/kb/comment-threaded-discussion-not-enabled?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
