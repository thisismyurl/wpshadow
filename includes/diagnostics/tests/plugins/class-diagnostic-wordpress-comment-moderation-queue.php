<?php
/**
 * Wordpress Comment Moderation Queue Diagnostic
 *
 * Wordpress Comment Moderation Queue issue detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1265.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wordpress Comment Moderation Queue Diagnostic Class
 *
 * @since 1.1265.0000
 */
class Diagnostic_WordpressCommentModerationQueue extends Diagnostic_Base {

	protected static $slug = 'wordpress-comment-moderation-queue';
	protected static $title = 'Wordpress Comment Moderation Queue';
	protected static $description = 'Wordpress Comment Moderation Queue issue detected';
	protected static $family = 'functionality';

	public static function check() {
		$issues = array();

		// Check 1: Verify comment moderation enabled
		$moderation_enabled = get_option( 'comment_moderation', false );
		if ( ! $moderation_enabled ) {
			$issues[] = __( 'Comment moderation not enabled', 'wpshadow' );
		}

		// Check 2: Check pending comment queue
		global $wpdb;
		$pending_count = wp_count_comments();
		if ( isset( $pending_count->moderated ) && $pending_count->moderated > 100 ) {
			$issues[] = __( 'Large backlog of pending comments', 'wpshadow' );
		}

		// Check 3: Verify spam detection
		$spam_detection = get_option( 'spam_detection_enabled', false );
		if ( ! $spam_detection ) {
			$issues[] = __( 'Comment spam detection not enabled', 'wpshadow' );
		}

		// Check 4: Check moderation email notifications
		$notif_email = get_option( 'moderation_notify', 0 );
		if ( $notif_email === 0 ) {
			$issues[] = __( 'Moderation notification emails not enabled', 'wpshadow' );
		}

		// Check 5: Verify comment caching
		$comment_cache = get_transient( 'comment_moderation_cache' );
		if ( false === $comment_cache ) {
			$issues[] = __( 'Comment moderation caching not active', 'wpshadow' );
		}

		// Check 6: Check auto-approval settings
		$auto_approve = get_option( 'comment_auto_approve', false );
		if ( ! $auto_approve && get_option( 'comment_whitelist' ) ) {
			$issues[] = __( 'Comment auto-approval whitelist not configured', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 75, 45 + ( count( $issues ) * 5 ) );
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: Comma-separated list of issues */
					__( 'WordPress comment moderation queue issues detected: %s', 'wpshadow' ),
					implode( ', ', $issues )
				),
				'severity'     => 'medium',
				'threat_level' => $threat_level,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/wordpress-comment-moderation-queue',
			);
		}

		return null;
	}
}

	}
}
