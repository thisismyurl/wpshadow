<?php
/**
 * Comment Sort Order Diagnostic
 *
 * Validates comment sort order configuration.
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
 * Comment Sort Order Diagnostic Class
 *
 * Checks if comment sort order is properly configured.
 *
 * @since 1.2601.1912
 */
class Diagnostic_Comment_Sort_Order extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-sort-order';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Sort Order';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates comment sort order configuration';

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

		// Check comment_order option.
		$comment_order = get_option( 'comment_order', 'asc' );

		// Check if comments are enabled.
		$default_comment_status = get_option( 'default_comment_status', 'open' );
		if ( 'closed' === $default_comment_status || 'close' === $default_comment_status ) {
			// Comments are disabled, sort order is not relevant.
			return null;
		}

		// Validate that comment_order is a valid value.
		if ( ! in_array( $comment_order, array( 'asc', 'desc' ), true ) ) {
			$issues[] = sprintf(
				/* translators: %s: invalid value */
				__( 'Comment order has invalid value: %s (should be "asc" or "desc")', 'wpshadow' ),
				esc_html( $comment_order )
			);
		}

		// Best practice check: 'asc' (chronological) is generally preferred.
		// 'desc' (reverse chronological) can work but is less common.
		if ( 'desc' === $comment_order ) {
			$issues[] = __( 'Comments are sorted in reverse chronological order (newest first) - chronological (oldest first) is more common', 'wpshadow' );
		}

		// Check threading compatibility.
		$thread_comments       = get_option( 'thread_comments', '0' );
		$thread_comments_depth = (int) get_option( 'thread_comments_depth', 5 );

		if ( ( '1' === $thread_comments || 1 === $thread_comments ) && 'desc' === $comment_order ) {
			$issues[] = __( 'Threading is enabled with reverse chronological order - reply chains may be confusing', 'wpshadow' );
		}

		// Check pagination compatibility.
		$page_comments         = get_option( 'page_comments', '0' );
		$default_comments_page = get_option( 'default_comments_page', 'newest' );

		if ( '1' === $page_comments || 1 === $page_comments ) {
			// Check if default page and order are mismatched.
			// 'newest' + 'desc' and 'oldest' + 'asc' are consistent combinations.
			$is_consistent = ( 'newest' === $default_comments_page && 'desc' === $comment_order ) ||
							( 'oldest' === $default_comments_page && 'asc' === $comment_order );

			if ( ! $is_consistent ) {
				$issues[] = sprintf(
					/* translators: 1: default page, 2: order */
					__( 'Default comments page (%1$s) may not match sort order (%2$s) - pagination may be confusing', 'wpshadow' ),
					$default_comments_page,
					$comment_order
				);
			}
		}

		// Check if there are actual comments to sort.
		global $wpdb;
		$comment_count = (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_approved = '1'"
		);

		if ( 0 === $comment_count ) {
			// No comments, so sort order doesn't matter.
			return null;
		}

		// Check for comments with unusual dates (may indicate sorting issues).
		$future_comments = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->comments} 
				WHERE comment_approved = '1' 
				AND comment_date > %s",
				current_time( 'mysql' )
			)
		);

		if ( $future_comments > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of comments */
				__( '%d comments have future dates - sort order may be affected', 'wpshadow' ),
				$future_comments
			);
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
				__( 'Found %d comment sort order configuration issues', 'wpshadow' ),
				count( $issues )
			),
			'severity'           => 'low',
			'threat_level'       => 20,
			'site_health_status' => 'recommended',
			'auto_fixable'       => false,
			'kb_link'            => 'https://wpshadow.com/kb/comment-sort-order',
			'family'             => self::$family,
			'details'            => array(
				'issues'                => $issues,
				'comment_order'         => $comment_order,
				'thread_comments'       => $thread_comments,
				'thread_comments_depth' => $thread_comments_depth,
				'page_comments'         => $page_comments,
				'default_comments_page' => $default_comments_page,
				'comment_count'         => $comment_count,
				'future_comments'       => isset( $future_comments ) ? $future_comments : 0,
			),
		);
	}
}
