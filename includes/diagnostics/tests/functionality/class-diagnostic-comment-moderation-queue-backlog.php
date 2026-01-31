<?php
/**
 * Comment Moderation Queue Backlog Diagnostic
 *
 * Checks if there are pending comments awaiting moderation.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2309
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Comment Moderation Queue Backlog Diagnostic Class
 *
 * Detects comment moderation backlog.
 *
 * @since 1.2601.2309
 */
class Diagnostic_Comment_Moderation_Queue_Backlog extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-moderation-queue-backlog';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Moderation Queue Backlog';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for pending comments awaiting moderation';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2309
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Count pending comments
		$pending_comments = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_approved = 0"
		);

		if ( $pending_comments > 50 ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					/* translators: %d: number of pending comments */
					__( '%d comments pending moderation', 'wpshadow' ),
					$pending_comments
				),
				'severity'      => 'medium',
				'threat_level'  => 35,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/comment-moderation-queue-backlog',
			);
		}

		if ( $pending_comments > 100 ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					/* translators: %d: number of pending comments */
					__( 'Large backlog: %d comments pending moderation', 'wpshadow' ),
					$pending_comments
				),
				'severity'      => 'high',
				'threat_level'  => 55,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/comment-moderation-queue-backlog',
			);
		}

		return null;
	}
}
