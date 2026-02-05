<?php
/**
 * Comment Threaded Discussion Not Enabled Treatment
 *
 * Checks if threaded comments are enabled.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6030.2352
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Comment Threaded Discussion Not Enabled Treatment Class
 *
 * Detects disabled threaded comments.
 *
 * @since 1.6030.2352
 */
class Treatment_Comment_Threaded_Discussion_Not_Enabled extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-threaded-discussion-not-enabled';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Threaded Discussion Not Enabled';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if threaded comments are enabled';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6030.2352
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
				'kb_link'       => 'https://wpshadow.com/kb/comment-threaded-discussion-not-enabled',
			);
		}

		return null;
	}
}
