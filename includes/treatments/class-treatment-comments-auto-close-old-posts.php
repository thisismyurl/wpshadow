<?php
/**
 * Treatment: Auto-close comments on old posts
 *
 * Enables the WordPress setting that automatically closes the comment thread
 * on posts older than a configurable number of days. When the close window is
 * set unreasonably high (> 180 days), it is reduced to 90 days so the
 * protection is actually meaningful.
 *
 * Undo: restores the previous values for close_comments_for_old_posts and
 * close_comments_days_old.
 *
 * @package WPShadow
 * @since   0.6095
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enables automatic comment closing on old posts.
 */
class Treatment_Comments_Auto_Close_Old_Posts extends Treatment_Base {

	/** @var string */
	protected static $slug = 'comments-auto-close-old-posts';

	/** @return string */
	public static function get_risk_level(): string {
		return 'safe';
	}

	/**
	 * Enable auto-close and ensure the close window is ≤ 180 days.
	 *
	 * @return array
	 */
	public static function apply(): array {
		$prev_enabled = get_option( 'close_comments_for_old_posts', '0' );
		$prev_days    = (int) get_option( 'close_comments_days_old', 14 );

		// Store old values for undo().
		static::save_backup_value( 'wpshadow_comments_auto_close_prev_enabled', $prev_enabled );
		static::save_backup_value( 'wpshadow_comments_auto_close_prev_days', $prev_days );

		update_option( 'close_comments_for_old_posts', '1' );

		$messages = array( __( 'Auto-close comments on old posts has been enabled.', 'wpshadow' ) );

		// If the window was disabled or set above 180 days, reset to 90.
		if ( '0' === (string) $prev_enabled || $prev_days > 180 || 0 === $prev_days ) {
			update_option( 'close_comments_days_old', 90 );
			$messages[] = __( 'Comment close window set to 90 days.', 'wpshadow' );
		}

		return array(
			'success' => true,
			'message' => implode( ' ', $messages ),
		);
	}

	/**
	 * Restore previous comment-close settings.
	 *
	 * @return array
	 */
	public static function undo(): array {
		$prev_enabled = static::load_backup_value( 'wpshadow_comments_auto_close_prev_enabled', true );
		$prev_days    = static::load_backup_value( 'wpshadow_comments_auto_close_prev_days', true );

		if ( $prev_enabled['found'] ) {
			update_option( 'close_comments_for_old_posts', $prev_enabled['value'] );
		}

		if ( $prev_days['found'] ) {
			update_option( 'close_comments_days_old', $prev_days['value'] );
		}

		return array(
			'success' => true,
			'message' => __( 'Comment auto-close settings restored to their previous values.', 'wpshadow' ),
		);
	}
}
