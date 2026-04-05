<?php
/**
 * Treatment: Enable Comment Moderation
 *
 * Sets comment_moderation to 1 so that comments must be manually approved
 * before appearing publicly. This prevents spam, unsolicited links, and
 * abusive comments from going live without review.
 *
 * Risk level: safe — single option update, fully reversible.
 *
 * @package WPShadow
 * @since   0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enables comment moderation (hold for approval before publishing).
 */
class Treatment_Comment_Moderation_Enabled extends Treatment_Base {

	/**
	 * @var string
	 */
	protected static $slug = 'comment-moderation-enabled';

	/** @return string */
	public static function get_risk_level(): string {
		return 'safe';
	}

	/**
	 * Set comment_moderation to 1.
	 *
	 * @return array
	 */
	public static function apply() {
		$previous = (int) get_option( 'comment_moderation', 0 );
		update_option( 'wpshadow_prev_comment_moderation', $previous, false );
		update_option( 'comment_moderation', 1 );

		return array(
			'success' => true,
			'message' => __( 'Comment moderation enabled. All new comments must be approved before they appear publicly.', 'wpshadow' ),
			'details' => array( 'previous_value' => $previous, 'new_value' => 1 ),
		);
	}

	/**
	 * Restore the previous comment_moderation value.
	 *
	 * @return array
	 */
	public static function undo() {
		$previous = get_option( 'wpshadow_prev_comment_moderation' );

		if ( false === $previous ) {
			return array(
				'success' => false,
				'message' => __( 'No previous value stored — nothing to restore.', 'wpshadow' ),
			);
		}

		update_option( 'comment_moderation', (int) $previous );
		delete_option( 'wpshadow_prev_comment_moderation' );

		return array(
			'success' => true,
			'message' => __( 'Comment moderation setting restored.', 'wpshadow' ),
		);
	}
}
