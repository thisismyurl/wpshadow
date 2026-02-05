<?php
/**
 * Comment Nesting Depth Treatment
 *
 * Verifies comment threading depth is configured for readability.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6032.1755
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Comment Nesting Depth Treatment Class
 *
 * Checks comment threading/nesting configuration.
 *
 * @since 1.6032.1755
 */
class Treatment_Comment_Nesting_Depth extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-nesting-depth';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Nesting Depth';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies comment threading depth';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'comments';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6032.1755
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if threading is enabled.
		$thread_comments = get_option( 'thread_comments', 0 );
		$thread_comments_depth = get_option( 'thread_comments_depth', 5 );

		if ( ! $thread_comments ) {
			$issues[] = __( 'Comment threading is disabled - may reduce conversation flow', 'wpshadow' );
		} else {
			// Check depth configuration.
			if ( $thread_comments_depth < 2 ) {
				$issues[] = __( 'Comment threading depth is too shallow (less than 2 levels)', 'wpshadow' );
			} elseif ( $thread_comments_depth > 10 ) {
				$issues[] = sprintf(
					/* translators: %d: nesting depth */
					__( 'Comment threading depth is very deep (%d levels) which may hurt readability', 'wpshadow' ),
					$thread_comments_depth
				);
			}

			// Optimal range is 3-5 levels.
			if ( $thread_comments_depth < 3 || $thread_comments_depth > 5 ) {
				$issues[] = __( 'Consider 3-5 levels for optimal readability (current setting outside range)', 'wpshadow' );
			}
		}

		// Check theme support for threaded comments.
		if ( $thread_comments && ! current_theme_supports( 'threaded-comments' ) ) {
			$issues[] = __( 'Threading enabled but theme may not support threaded comments properly', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/comment-nesting-depth',
			);
		}

		return null;
	}
}
