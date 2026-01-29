<?php
/**
 * Comment Author Verification Issues Diagnostic
 *
 * Checks if comment author information is valid and verified,
 * detecting fake or suspicious author data.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5028.1630
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Comment Author Verification Class
 *
 * Validates comment author emails and names for authenticity.
 * Detects fake emails, disposable domains, and suspicious patterns.
 *
 * @since 1.5028.1630
 */
class Diagnostic_Comment_Author_Verification extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-author-verification';

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
	protected static $description = 'Validates comment author information authenticity';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'comments';

	/**
	 * Run the diagnostic check.
	 *
	 * Analyzes recent comments for invalid/fake author data using get_comments().
	 * Checks email validity, disposable domains, and suspicious patterns.
	 *
	 * @since  1.5028.1630
	 * @return array|null Finding array if verification issues found, null otherwise.
	 */
	public static function check() {
		$cache_key = 'wpshadow_comment_author_verification_check';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		// Use get_comments() API (NO $wpdb).
		$recent_comments = get_comments(
			array(
				'status'  => 'approve',
				'number'  => 100,
				'orderby' => 'comment_date',
				'order'   => 'DESC',
			)
		);

		if ( empty( $recent_comments ) ) {
			set_transient( $cache_key, null, 12 * HOUR_IN_SECONDS );
			return null;
		}

		$suspicious_authors = array();
		$disposable_domains = self::get_disposable_domains();

		foreach ( $recent_comments as $comment ) {
			$issues = array();
			$author_email = $comment->comment_author_email;
			$author_name  = $comment->comment_author;

			// Check email validity.
			if ( ! empty( $author_email ) && ! is_email( $author_email ) ) {
				$issues[] = 'invalid-email';
			}

			// Check for disposable email domains.
			if ( ! empty( $author_email ) ) {
				$email_domain = substr( strrchr( $author_email, '@' ), 1 );
				if ( in_array( strtolower( $email_domain ), $disposable_domains, true ) ) {
					$issues[] = 'disposable-email';
				}
			}

			// Check for suspicious author names.
			if ( ! empty( $author_name ) ) {
				// Generic spam names.
				if ( preg_match( '/\b(admin|test|user|guest|anon|spam)\d+\b/i', $author_name ) ) {
					$issues[] = 'suspicious-name';
				}

				// Names with only special characters.
				if ( preg_match( '/^[^a-zA-Z]+$/', $author_name ) ) {
					$issues[] = 'invalid-name';
				}

				// Extremely long names (likely spam).
				if ( strlen( $author_name ) > 50 ) {
					$issues[] = 'excessive-length';
				}
			}

			// If 2+ issues, flag as suspicious.
			if ( count( $issues ) >= 2 ) {
				$suspicious_authors[] = array(
					'comment_id' => $comment->comment_ID,
					'author'     => $author_name,
					'email'      => $author_email,
					'date'       => $comment->comment_date,
					'issues'     => $issues,
				);
			}
		}

		// If 10+ suspicious authors in last 100 comments, flag it.
		if ( count( $suspicious_authors ) >= 10 ) {
			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of suspicious authors */
					__( 'Detected %d comments with suspicious author information (invalid emails, disposable domains, fake names). Consider strengthening author verification.', 'wpshadow' ),
					count( $suspicious_authors )
				),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/comments-author-verification',
				'data'         => array(
					'suspicious_count'   => count( $suspicious_authors ),
					'suspicious_authors' => array_slice( $suspicious_authors, 0, 10 ),
					'total_analyzed'     => count( $recent_comments ),
				),
			);

			set_transient( $cache_key, $result, 12 * HOUR_IN_SECONDS );
			return $result;
		}

		set_transient( $cache_key, null, 12 * HOUR_IN_SECONDS );
		return null;
	}

	/**
	 * Get list of disposable email domains.
	 *
	 * @since  1.5028.1630
	 * @return array List of disposable email domains.
	 */
	private static function get_disposable_domains() {
		return array(
			'10minutemail.com',
			'guerrillamail.com',
			'mailinator.com',
			'tempmail.com',
			'throwaway.email',
			'maildrop.cc',
			'yopmail.com',
			'temp-mail.org',
			'fakeinbox.com',
			'trashmail.com',
			'getnada.com',
			'emailondeck.com',
		);
	}
}
