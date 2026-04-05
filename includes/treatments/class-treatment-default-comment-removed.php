<?php
/**
 * Treatment: Remove the default "Hello World" starter comment
 *
 * WordPress installs with a sample comment by "A WordPress Commenter"
 * (email wapuu@wordpress.example) on the Hello World post. Leaving it live
 * signals the site was never properly set up.
 *
 * This treatment permanently deletes that comment. Because the content
 * is synthetic starter data with no real value, undo is not supported —
 * the comment is gone for good.
 *
 * @package WPShadow
 * @since   0.6093.1900
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Permanently removes the WordPress default starter comment.
 */
class Treatment_Default_Comment_Removed extends Treatment_Base {

	/** @var string */
	protected static $slug = 'default-comment-removed';

	/** @return string */
	public static function get_risk_level(): string {
		return 'moderate';
	}

	/**
	 * Delete the default WordPress starter comment.
	 *
	 * @return array
	 */
	public static function apply(): array {
		// Primary search: well-known email address used in all WP installs.
		$comments = get_comments(
			array(
				'author_email' => 'wapuu@wordpress.example',
				'number'       => 1,
				'status'       => 'any',
			)
		);

		// Fallback: match by author name + known comment text.
		if ( empty( $comments ) ) {
			$comments = get_comments(
				array(
					'author'       => 'A WordPress Commenter',
					'search'       => 'Hi, this is a comment',
					'number'       => 1,
					'status'       => 'any',
				)
			);
		}

		if ( empty( $comments ) ) {
			return array(
				'success' => true,
				'message' => __( 'Default starter comment not found — it may have already been removed.', 'wpshadow' ),
			);
		}

		$comment    = reset( $comments );
		$comment_id = (int) $comment->comment_ID;
		$deleted    = wp_delete_comment( $comment_id, true );

		if ( ! $deleted ) {
			return array(
				'success' => false,
				'message' => __( 'Could not delete the default starter comment. Try removing it manually from the Comments screen.', 'wpshadow' ),
			);
		}

		return array(
			'success' => true,
			'message' => __( 'Default starter comment permanently deleted.', 'wpshadow' ),
		);
	}

	/**
	 * Undo is not supported — deleted comments cannot be restored.
	 *
	 * @return array
	 */
	public static function undo(): array {
		return array(
			'success' => false,
			'message' => __( 'The starter comment was permanently deleted and cannot be restored automatically. Re-create it manually if needed.', 'wpshadow' ),
		);
	}
}
