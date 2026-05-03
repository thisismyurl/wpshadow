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
 * @package ThisIsMyURL\Shadow
 * @since   0.6095
 */

declare(strict_types=1);

namespace ThisIsMyURL\Shadow\Treatments;

use ThisIsMyURL\Shadow\Core\Treatment_Base;

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
		update_option( 'thisismyurl_shadow_prev_comment_moderation', $previous, false );
		update_option( 'comment_moderation', 1 );

		return array(
			'success' => true,
			'message' => __( 'Comment moderation enabled. All new comments must be approved before they appear publicly.', 'thisismyurl-shadow' ),
			'details' => array( 'previous_value' => $previous, 'new_value' => 1 ),
		);
	}

	/**
	 * Restore the previous comment_moderation value.
	 *
	 * @return array
	 */
	public static function undo() {
		$previous = get_option( 'thisismyurl_shadow_prev_comment_moderation' );

		if ( false === $previous ) {
			return array(
				'success' => false,
				'message' => __( 'No previous value stored — nothing to restore.', 'thisismyurl-shadow' ),
			);
		}

		update_option( 'comment_moderation', (int) $previous );
		delete_option( 'thisismyurl_shadow_prev_comment_moderation' );

		return array(
			'success' => true,
			'message' => __( 'Comment moderation setting restored.', 'thisismyurl-shadow' ),
		);
	}
}
