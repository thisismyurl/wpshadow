<?php
/**
 * Comment Sort Order Diagnostic
 *
 * Verifies comment display order is configured for best user experience.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26032.1755
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
 * Checks comment sorting configuration.
 *
 * @since 1.26032.1755
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
	protected static $description = 'Verifies comment sort order';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'comments';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26032.1755
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check comment order setting.
		$comment_order = get_option( 'comment_order', 'asc' );

		// Check pagination and default page.
		$page_comments = get_option( 'page_comments', 0 );
		$default_comments_page = get_option( 'default_comments_page', 'newest' );

		// Check for conflicts.
		if ( $page_comments ) {
			if ( $default_comments_page === 'newest' && $comment_order === 'asc' ) {
				$issues[] = __( 'Showing newest page first but comments sorted oldest first - may be confusing', 'wpshadow' );
			} elseif ( $default_comments_page === 'oldest' && $comment_order === 'desc' ) {
				$issues[] = __( 'Showing oldest page first but comments sorted newest first - may be confusing', 'wpshadow' );
			}
		}

		// Check threading compatibility.
		$thread_comments = get_option( 'thread_comments', 0 );
		if ( $thread_comments && $comment_order === 'desc' ) {
			$issues[] = __( 'Threaded comments with descending order may make conversations hard to follow', 'wpshadow' );
		}

		// Get posts with many comments to check impact.
		global $wpdb;
		$max_comments = $wpdb->get_var(
			"SELECT MAX(comment_count) FROM {$wpdb->posts} WHERE comment_count > 0"
		);

		if ( $max_comments > 100 && $comment_order === 'desc' && ! $page_comments ) {
			$issues[] = __( 'Descending order on posts with many comments without pagination may bury old discussions', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/comment-sort-order',
			);
		}

		return null;
	}
}
