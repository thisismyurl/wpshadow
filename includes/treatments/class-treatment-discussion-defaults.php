<?php
/**
 * Treatment: Harden default discussion settings
 *
 * Fresh WordPress installs often allow comments and pings too freely. This
 * treatment applies a basic anti-spam baseline for new content by closing
 * comments and pings by default and enabling comment moderation.
 *
 * Undo: restores the previous discussion defaults.
 *
 * @package ThisIsMyURL\Shadow
 * @since   0.7056
 */

declare(strict_types=1);

namespace ThisIsMyURL\Shadow\Treatments;

use ThisIsMyURL\Shadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Applies a conservative discussion baseline for new posts.
 */
class Treatment_Discussion_Defaults extends Treatment_Base {

	/** @var string */
	protected static $slug = 'discussion-defaults';

	/** @return string */
	public static function get_risk_level(): string {
		return 'safe';
	}

	/**
	 * Close comments and pings by default, and enable moderation.
	 *
	 * @return array
	 */
	public static function apply(): array {
		$previous = array(
			'default_comment_status' => (string) get_option( 'default_comment_status', 'open' ),
			'default_ping_status'    => (string) get_option( 'default_ping_status', 'open' ),
			'default_pingback_flag'  => (int) get_option( 'default_pingback_flag', 1 ),
			'comment_moderation'     => (int) get_option( 'comment_moderation', 0 ),
		);

		if (
			'closed' === $previous['default_comment_status']
			&& 'closed' === $previous['default_ping_status']
			&& 0 === $previous['default_pingback_flag']
			&& 1 === $previous['comment_moderation']
		) {
			return array(
				'success' => true,
				'message' => __( 'Discussion defaults are already using a hardened baseline. No changes made.', 'thisismyurl-shadow' ),
			);
		}

		static::save_backup_value( 'thisismyurl_shadow_discussion_defaults_prev', $previous );

		update_option( 'default_comment_status', 'closed' );
		update_option( 'default_ping_status', 'closed' );
		update_option( 'default_pingback_flag', 0 );
		update_option( 'comment_moderation', 1 );

		return array(
			'success' => true,
			'message' => __( 'Discussion defaults hardened for new content: comments closed by default, pingbacks disabled, and moderation enabled.', 'thisismyurl-shadow' ),
		);
	}

	/**
	 * Restore the previous discussion defaults.
	 *
	 * @return array
	 */
	public static function undo(): array {
		$loaded = static::load_backup_array(
			'thisismyurl_shadow_discussion_defaults_prev',
			array( 'default_comment_status', 'default_ping_status', 'default_pingback_flag', 'comment_moderation' ),
			true
		);

		if ( ! $loaded['found'] || ! is_array( $loaded['value'] ) ) {
			return array(
				'success' => false,
				'message' => __( 'No stored discussion defaults were found to restore.', 'thisismyurl-shadow' ),
			);
		}

		$previous = $loaded['value'];

		update_option( 'default_comment_status', (string) $previous['default_comment_status'] );
		update_option( 'default_ping_status', (string) $previous['default_ping_status'] );
		update_option( 'default_pingback_flag', (int) $previous['default_pingback_flag'] );
		update_option( 'comment_moderation', (int) $previous['comment_moderation'] );

		return array(
			'success' => true,
			'message' => __( 'Discussion defaults restored to their previous values.', 'thisismyurl-shadow' ),
		);
	}
}