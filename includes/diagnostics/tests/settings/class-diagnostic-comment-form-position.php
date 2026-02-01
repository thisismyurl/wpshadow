<?php
/**
 * Comment Form Position Diagnostic
 *
 * Tests comment form placement for optimal UX.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.1912
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Comment Form Position Diagnostic Class
 *
 * Validates that comment form position is optimized for UX.
 *
 * @since 1.2601.1912
 */
class Diagnostic_Comment_Form_Position extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-form-position';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Form Position';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests comment form placement for optimal UX';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'settings';

	/**
	 * The family label
	 *
	 * @var string
	 */
	protected static $family_label = 'Settings';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.1912
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check comment_order option (determines form position indirectly).
		$comment_order = get_option( 'comment_order', 'asc' );

		// Check if comments are enabled.
		$default_comment_status = get_option( 'default_comment_status', 'open' );
		if ( 'closed' === $default_comment_status || 'close' === $default_comment_status ) {
			// Comments are disabled, form position is not relevant.
			return null;
		}

		// WordPress displays form position based on comment_order:
		// - 'asc' = older comments first, form typically at bottom.
		// - 'desc' = newer comments first, form typically at top.
		// Best practice: Form below comments (asc order) for better UX.
		if ( 'desc' === $comment_order ) {
			$issues[] = __( 'Comment form may appear above comments (desc order) - users expect form below comments', 'wpshadow' );
		}

		// Check comment threading depth.
		$thread_comments       = get_option( 'thread_comments', '0' );
		$thread_comments_depth = (int) get_option( 'thread_comments_depth', 5 );
		if ( '1' === $thread_comments || 1 === $thread_comments ) {
			if ( $thread_comments_depth < 2 ) {
				$issues[] = __( 'Comment threading is enabled but depth is too shallow (less than 2 levels)', 'wpshadow' );
			} elseif ( $thread_comments_depth > 10 ) {
				$issues[] = sprintf(
					/* translators: %d: threading depth */
					__( 'Comment threading depth is very deep (%d levels) - may cause UX issues', 'wpshadow' ),
					$thread_comments_depth
				);
			}
		}

		// Check pagination settings (affects form position perception).
		$page_comments         = get_option( 'page_comments', '0' );
		$comments_per_page     = (int) get_option( 'comments_per_page', 50 );
		$default_comments_page = get_option( 'default_comments_page', 'newest' );

		if ( '1' === $page_comments || 1 === $page_comments ) {
			// Pagination is enabled.
			if ( 'newest' === $default_comments_page && 'asc' === $comment_order ) {
				$issues[] = __( 'Default comments page is "newest" but order is "asc" - form may be on different page than default view', 'wpshadow' );
			} elseif ( 'oldest' === $default_comments_page && 'desc' === $comment_order ) {
				$issues[] = __( 'Default comments page is "oldest" but order is "desc" - may confuse users', 'wpshadow' );
			}

			if ( $comments_per_page < 10 ) {
				$issues[] = sprintf(
					/* translators: %d: comments per page */
					__( 'Comments per page is too low (%d) - excessive pagination may hurt UX', 'wpshadow' ),
					$comments_per_page
				);
			}
		}

		// If no issues found, return null.
		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'                 => self::$slug,
			'title'              => self::$title,
			'description'        => sprintf(
				/* translators: %d: number of issues */
				__( 'Found %d comment form position issues', 'wpshadow' ),
				count( $issues )
			),
			'severity'           => 'low',
			'threat_level'       => 25,
			'site_health_status' => 'recommended',
			'auto_fixable'       => false,
			'kb_link'            => 'https://wpshadow.com/kb/comment-form-position',
			'family'             => self::$family,
			'details'            => array(
				'issues'                => $issues,
				'comment_order'         => $comment_order,
				'thread_comments'       => $thread_comments,
				'thread_comments_depth' => $thread_comments_depth,
				'page_comments'         => $page_comments,
				'comments_per_page'     => $comments_per_page,
				'default_comments_page' => $default_comments_page,
			),
		);
	}
}
