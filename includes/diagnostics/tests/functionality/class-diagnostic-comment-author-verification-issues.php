<?php
/**
 * Comment Author Verification Issues Diagnostic
 *
 * Checks for comment author verification and validation issues.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2308
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Comment Author Verification Issues Diagnostic Class
 *
 * Detects comment author verification problems.
 *
 * @since 1.2601.2308
 */
class Diagnostic_Comment_Author_Verification_Issues extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-author-verification-issues';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Author Verification Issues';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for issues with comment author verification and validation';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2308
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues = array();

		// Check if comment author verification is enabled
		$comment_author_email_required = get_option( 'require_name_email', 0 );

		if ( ! $comment_author_email_required ) {
			$issues[] = __( 'Comment author name/email not required', 'wpshadow' );
		}

		// Check for comments with invalid email addresses
		$invalid_emails = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->comments} 
			WHERE comment_author_email NOT LIKE '%@%.%' 
			AND comment_author_email != '' 
			AND 1=1"
		);

		if ( $invalid_emails > 10 ) {
			$issues[] = sprintf(
				/* translators: %d: number of comments with invalid emails */
				__( '%d comments have invalid email addresses', 'wpshadow' ),
				$invalid_emails
			);
		}

		// Check for comments with empty author names
		$empty_names = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->comments} 
			WHERE (comment_author = '' OR comment_author IS NULL) 
			AND comment_type = 'comment'"
		);

		if ( $empty_names > 20 ) {
			$issues[] = sprintf(
				/* translators: %d: number of comments with empty authors */
				__( '%d comments have empty author names', 'wpshadow' ),
				$empty_names
			);
		}

		// Check for comments from non-registered users marked as posts by author
		$author_comments_unverified = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->comments} 
			WHERE user_id = 0 
			AND comment_author IN (
				SELECT user_login FROM {$wpdb->users}
			) 
			AND comment_type = 'comment'"
		);

		if ( $author_comments_unverified > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of unverified comments */
				__( '%d comments claim to be from site authors but are unverified', 'wpshadow' ),
				$author_comments_unverified
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					/* translators: %d: number of issues */
					__( 'Found %d comment author verification issues', 'wpshadow' ),
					count( $issues )
				),
				'severity'      => 'medium',
				'threat_level'  => 45,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/comment-author-verification-issues',
			);
		}

		return null;
	}
}
