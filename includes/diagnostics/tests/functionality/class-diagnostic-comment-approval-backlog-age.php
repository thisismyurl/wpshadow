<?php
/**
 * Comment Approval Backlog Age Diagnostic
 *
 * Checks for aged comments pending approval.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2310
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Comment Approval Backlog Age Diagnostic Class
 *
 * Detects aged comments pending approval.
 *
 * @since 1.2601.2310
 */
class Diagnostic_Comment_Approval_Backlog_Age extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-approval-backlog-age';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Approval Backlog Age';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks age of pending comments';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2310
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Find oldest unapproved comment
		$oldest_unapproved = $wpdb->get_row(
			"SELECT comment_date, comment_author_email FROM {$wpdb->comments} WHERE comment_approved = '0' ORDER BY comment_date ASC LIMIT 1"
		);

		if ( ! $oldest_unapproved ) {
			return null; // No unapproved comments
		}

		$comment_date = strtotime( $oldest_unapproved->comment_date );
		$current_time = current_time( 'timestamp' );
		$age_days     = ( $current_time - $comment_date ) / DAY_IN_SECONDS;

		if ( $age_days > 7 ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					__( 'Oldest pending comment is %d days old. Consider reviewing or deleting aged comments.', 'wpshadow' ),
					absint( $age_days )
				),
				'severity'      => 'medium',
				'threat_level'  => 40,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/comment-approval-backlog-age',
			);
		}

		return null;
	}
}
