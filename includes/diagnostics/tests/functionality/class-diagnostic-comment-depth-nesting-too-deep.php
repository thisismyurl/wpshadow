<?php
/**
 * Comment Depth Nesting Too Deep Diagnostic
 *
 * Checks if comment nesting depth is configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2310
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Comment Depth Nesting Too Deep Diagnostic Class
 *
 * Detects excessive comment nesting.
 *
 * @since 1.2601.2310
 */
class Diagnostic_Comment_Depth_Nesting_Too_Deep extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-depth-nesting-too-deep';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Depth Nesting Too Deep';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if comment nesting depth is limited';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2310
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$thread_comments_depth = get_option( 'thread_comments_depth', 5 );

		if ( $thread_comments_depth > 10 ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					__( 'Comment nesting depth is %d levels. Limit nesting to 3-5 levels to improve readability and performance.', 'wpshadow' ),
					absint( $thread_comments_depth )
				),
				'severity'      => 'low',
				'threat_level'  => 15,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/comment-depth-nesting-too-deep',
			);
		}

		return null;
	}
}
