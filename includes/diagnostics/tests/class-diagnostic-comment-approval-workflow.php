<?php
/**
 * Comment Approval Workflow Diagnostic
 *
 * Verifies comment approval workflow is properly configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26032.1900
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Comment Approval Workflow Diagnostic Class
 *
 * Checks comment moderation workflow configuration.
 *
 * @since 1.26032.1900
 */
class Diagnostic_Comment_Approval_Workflow extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-approval-workflow';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Approval Workflow';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies comment moderation workflow';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'comments';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26032.1900
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if moderation is enabled.
		$comment_moderation = get_option( 'comment_moderation', 0 );
		$comment_registration = get_option( 'comment_registration', 0 );

		if ( ! $comment_moderation && ! $comment_registration ) {
			$issues[] = __( 'No comment moderation or registration required - open to spam', 'wpshadow' );
		}

		// Check moderation hold settings.
		$moderation_notify = get_option( 'moderation_notify', 1 );
		if ( ! $moderation_notify ) {
			$issues[] = __( 'Admin notifications disabled for pending comments', 'wpshadow' );
		}

		// Check pending comments count.
		$pending_count = wp_count_comments();
		if ( isset( $pending_count->moderated ) && $pending_count->moderated > 100 ) {
			$issues[] = sprintf(
				/* translators: %d: pending comments */
				__( 'Large moderation queue (%d pending comments) - workflow may be overwhelmed', 'wpshadow' ),
				$pending_count->moderated
			);
		}

		// Check if previous commenter approval is set.
		$comment_previously_approved = get_option( 'comment_previously_approved', 1 );
		if ( ! $comment_previously_approved && $comment_moderation ) {
			$issues[] = __( 'All comments require approval even from previously approved users', 'wpshadow' );
		}

		// Check author email verification.
		if ( $comment_moderation && ! $comment_registration ) {
			$issues[] = __( 'Moderation enabled but registration not required - may allow anonymous abuse', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/comment-approval-workflow',
			);
		}

		return null;
	}
}
