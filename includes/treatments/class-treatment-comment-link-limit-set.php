<?php
/**
 * Treatment: Set Comment Link Limit
 *
 * Updates comment_max_links to 2, which holds comments with more than 2
 * links in the moderation queue. A limit of 0 means unlimited links per
 * comment, which allows spam bots to inject any number of links into
 * comments. Setting a low threshold is a standard spam defence.
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
 * Sets the comment link moderation threshold to 2.
 */
class Treatment_Comment_Link_Limit_Set extends Treatment_Base {

	/**
	 * @var string
	 */
	protected static $slug = 'comment-link-limit-set';

	/**
	 * Recommended link limit for new comments.
	 */
	private const RECOMMENDED_LIMIT = 2;

	/** @return string */
	public static function get_risk_level(): string {
		return 'safe';
	}

	/**
	 * Set comment_max_links to 2.
	 *
	 * @return array
	 */
	public static function apply() {
		$previous = (int) get_option( 'comment_max_links', 0 );
		update_option( 'thisismyurl_shadow_prev_comment_max_links', $previous, false );
		update_option( 'comment_max_links', self::RECOMMENDED_LIMIT );

		return array(
			'success' => true,
			'message' => sprintf(
						/* translators: %d: link threshold. */
				__( 'Comment link limit set to %d. Comments containing more than this many links will be held for moderation automatically.', 'thisismyurl-shadow' ),
				self::RECOMMENDED_LIMIT
			),
			'details' => array(
				'previous_value' => $previous,
				'new_value'      => self::RECOMMENDED_LIMIT,
			),
		);
	}

	/**
	 * Restore the previous comment_max_links value.
	 *
	 * @return array
	 */
	public static function undo() {
		$previous = get_option( 'thisismyurl_shadow_prev_comment_max_links' );

		if ( false === $previous ) {
			return array(
				'success' => false,
				'message' => __( 'No previous value stored — nothing to restore.', 'thisismyurl-shadow' ),
			);
		}

		update_option( 'comment_max_links', (int) $previous );
		delete_option( 'thisismyurl_shadow_prev_comment_max_links' );

		return array(
			'success' => true,
			'message' => sprintf(
						/* translators: %d: restored limit value. */
				__( 'Comment link limit restored to %d.', 'thisismyurl-shadow' ),
				(int) $previous
			),
		);
	}
}
