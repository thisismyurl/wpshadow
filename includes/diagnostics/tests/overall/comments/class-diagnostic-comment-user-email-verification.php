<?php
/**
 * Comment User Email Verification Diagnostic
 *
 * Verifies commenter email addresses when needed and detects invalid
 * or suspicious email patterns.
 *
 * @package    WPShadow\Diagnostics
 * @subpackage Tests
 * @since      1.2601.2207
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Comment User Email Verification Diagnostic Class
 *
 * Checks for:
 * - Invalid email formats in comments
 * - Disposable/temporary email addresses
 * - Suspicious email patterns (spam indicators)
 * - Missing email verification for registered users
 * - Email domains with high spam rates
 *
 * @since 1.2601.2207
 */
class Diagnostic_Comment_User_Email_Verification extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-user-email-verification';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Comment User Email Verification';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies commenter email addresses and detects invalid patterns';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'comments';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2207
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;
		$issues = array();

		// Check for invalid email formats.
		$invalid_emails = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->comments}
			WHERE comment_author_email != ''
			AND comment_author_email NOT REGEXP '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\\.[A-Za-z]{2,}$'"
		);

		if ( $invalid_emails > 0 ) {
			$issues[] = sprintf(
				__( '%s comments have invalid email formats', 'wpshadow' ),
				number_format_i18n( $invalid_emails )
			);
		}

		// Check for common disposable email domains.
		$disposable_domains = array(
			'tempmail.com',
			'10minutemail.com',
			'guerrillamail.com',
			'mailinator.com',
			'throwaway.email',
			'temp-mail.org',
		);

		$pattern = implode( '|', array_map( 'preg_quote', $disposable_domains ) );
		$disposable_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*)
				FROM {$wpdb->comments}
				WHERE comment_author_email REGEXP %s",
				$pattern
			)
		);

		if ( $disposable_count > 0 ) {
			$issues[] = sprintf(
				__( '%s comments use disposable email addresses (likely spam)', 'wpshadow' ),
				number_format_i18n( $disposable_count )
			);
		}

		// Check for suspicious email patterns.
		$suspicious_patterns = array(
			'noreply@',
			'no-reply@',
			'admin@',
			'test@',
			'example.com',
			'test.com',
			'@localhost',
		);

		$suspicious_count = 0;
		foreach ( $suspicious_patterns as $pattern ) {
			$count = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*)
					FROM {$wpdb->comments}
					WHERE comment_author_email LIKE %s",
					'%' . $wpdb->esc_like( $pattern ) . '%'
				)
			);
			$suspicious_count += (int) $count;
		}

		if ( $suspicious_count > 0 ) {
			$issues[] = sprintf(
				__( '%s comments have suspicious email patterns', 'wpshadow' ),
				number_format_i18n( $suspicious_count )
			);
		}

		// Check for duplicate emails with different names.
		$duplicate_emails = $wpdb->get_results(
			"SELECT comment_author_email, COUNT(DISTINCT comment_author) as author_count
			FROM {$wpdb->comments}
			WHERE comment_author_email != ''
			GROUP BY comment_author_email
			HAVING author_count > 5
			LIMIT 10"
		);

		if ( ! empty( $duplicate_emails ) ) {
			$issues[] = sprintf(
				__( '%d email addresses used with multiple names (possible spam or identity issues)', 'wpshadow' ),
				count( $duplicate_emails )
			);
		}

		// Check comment moderation settings.
		$require_email = get_option( 'require_name_email', 1 );
		if ( ! $require_email ) {
			$issues[] = __( 'Email addresses not required for comments (increases spam risk)', 'wpshadow' );
		}

		// Check for email verification plugin.
		$verification_plugins = array(
			'comment-email-verify/comment-email-verify.php',
			'wp-comment-email-verify/wp-comment-email-verify.php',
		);

		$has_verification = false;
		foreach ( $verification_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_verification = true;
				break;
			}
		}

		if ( ! $has_verification && $invalid_emails > 10 ) {
			$issues[] = __( 'No email verification plugin detected (consider adding for spam prevention)', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => implode( "\n", $issues ),
			'severity'     => 'medium',
			'threat_level' => 40,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/comment-email-verification',
		);
	}
}
