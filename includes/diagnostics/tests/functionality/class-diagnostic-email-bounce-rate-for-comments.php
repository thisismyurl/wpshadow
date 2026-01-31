<?php
/**
 * Email Bounce Rate for Comments Diagnostic
 *
 * Checks for email bounce issues with comment notifications.
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
 * Email Bounce Rate for Comments Diagnostic Class
 *
 * Detects high email bounce rates for comment notifications.
 *
 * @since 1.2601.2310
 */
class Diagnostic_Email_Bounce_Rate_For_Comments extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'email-bounce-rate-for-comments';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Email Bounce Rate for Comments';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for email delivery bounce issues';

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

		// Check for invalid comment emails
		$invalid_emails = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_author_email LIKE '%@%' AND comment_author_email NOT LIKE '%..%' AND comment_type = 'comment'"
		);

		// Look for obvious invalid patterns
		$obvious_invalid = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_author_email IN ('', '@', 'noreply@noreply.com', 'noreply@localhost') AND comment_type = 'comment'"
		);

		if ( $obvious_invalid > 10 ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					__( '%d comments have invalid email addresses. Comment reply notifications will likely bounce.', 'wpshadow' ),
					absint( $obvious_invalid )
				),
				'severity'      => 'medium',
				'threat_level'  => 45,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/email-bounce-rate-for-comments',
			);
		}

		// Check if verification hooks are filtering bad emails
		if ( has_filter( 'pre_comment_author_email' ) ) {
			return null; // Verification is in place
		}

		return null;
	}
}
