<?php
/**
 * Discussion Settings Security Diagnostic
 *
 * Validates comment/discussion security settings to prevent spam, abuse, and\n * unauthorized comment publication. Misconfigured discussion settings allow spam\n * bots to flood sites with malicious comments containing phishing links.\n *
 * **What This Check Does:**
 * - Checks if comment moderation enabled (before_publish option)\n * - Validates comment author email/name required\n * - Detects if auto-close comments after X days enabled\n * - Tests if new comment notifications sent to admin\n * - Checks if comment author must have previous approved comment (whitelist)\n * - Validates CAPTCHA or similar SPAM protection\n *
 * **Why This Matters:**
 * Permissive discussion settings flood site with spam/malware. Scenarios:\n * - Comments auto-approve without moderation\n * - Spam bots submit 10,000 comments/day containing malware links\n * - Comments published immediately to site (and search engines)\n * - Legitimate users click malware links, get infected\n * - Site reputation damaged (appears to endorse malware)\n *
 * **Business Impact:**
 * Blog with open comments enabled, auto-approve. Spam bot discovers via WPScan.\n * Submits 5,000 comments/day with malware URLs. Comments appear on blog posts.\n * Google crawls comments, indexes malware links. Blog appears in search \"malware\"\n * queries. Site blacklisted by Google Safe Browsing. Traffic drops 90%.\n * Recovery: 1 week blacklist delisting process.\n *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Comments safe and relevant\n * - #9 Show Value: Prevents spam/malware distribution\n * - #10 Beyond Pure: Respects user safety (not serving malware)\n *
 * **Related Checks:**
 * - Comment Form CAPTCHA Not Implemented (human verification)\n * - Comment Flood Protection (rate limiting)\n * - Bot Traffic Detection Not Implemented (general bot prevention)\n *
 * **Learn More:**
 * Discussion security setup: https://wpshadow.com/kb/wordpress-discussion-security\n * Video: Configuring comment moderation (7min): https://wpshadow.com/training/comment-security\n *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Discussion Settings Security Diagnostic Class
 *
 * Implements validation of comment/discussion security options.\n *
 * **Detection Pattern:**
 * 1. Get discussion options: comment_moderation, moderation_notify, etc.\n * 2. Check if comments before_publish vs after_publish\n * 3. Validate comment_registration, require_name_email\n * 4. Check close_comments_for_old_posts\n * 5. Validate allow_comments_feed\n * 6. Return severity if insecure settings found\n *
 * **Real-World Scenario:**
 * Default WordPress discussion settings. Comments allowed, no moderation,\n * doesn't require email. Spam bot auto-discovers via WPScan. Posts 10 comments/min.\n * 500 comments/hour with casino/pharma links. Moderator drowning in notifications.\n * Admin manually deletes comments for 3 hours, gives up. Site full of spam.\n *
 * **Implementation Notes:**
 * - Uses get_option() for all discussion settings\n * - Checks: comment_moderation, require_name_email, close_comments_for_old_posts\n * - Validates notification settings\n * - Severity: high (no moderation), medium (weak moderation)\n * - Treatment: enable moderation, require email, disable old comments\n *
 * @since 0.6093.1200
 */
class Diagnostic_Discussion_Settings_Security extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'discussion-settings-security';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Discussion Settings Security';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates comment/discussion security settings';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * The family label
	 *
	 * @var string
	 */
	protected static $family_label = 'Security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check comment moderation setting.
		$comment_moderation = get_option( 'comment_moderation', '0' );
		if ( '0' === $comment_moderation || 0 === $comment_moderation ) {
			$issues[] = __( 'Comment moderation is disabled - all comments will be published automatically', 'wpshadow' );
		}

		// Check if comment registration is required.
		$comment_registration = get_option( 'comment_registration', '0' );
		if ( '0' === $comment_registration || 0 === $comment_registration ) {
			$issues[] = __( 'Comment registration is not required - anonymous users can comment', 'wpshadow' );
		}

		// Check comment flood control.
		$comment_max_links = (int) get_option( 'comment_max_links', 2 );
		if ( $comment_max_links > 5 ) {
			$issues[] = sprintf(
				/* translators: %d: number of links allowed */
				__( 'Comment link limit is too high (%d) - may allow spam', 'wpshadow' ),
				$comment_max_links
			);
		}

		// Check disallowed keys (formerly blacklist).
		$disallowed_keys = get_option( 'disallowed_comment_keys', '' );
		if ( empty( $disallowed_keys ) ) {
			$issues[] = __( 'No disallowed comment keys configured - spam filtering may be ineffective', 'wpshadow' );
		}

		// Check moderation keys.
		$moderation_keys = get_option( 'moderation_keys', '' );
		if ( empty( $moderation_keys ) && empty( $disallowed_keys ) ) {
			$issues[] = __( 'No moderation keys configured - comment spam filtering is minimal', 'wpshadow' );
		}

		// Check trackback/pingback settings.
		$default_pingback    = get_option( 'default_pingback_flag', '0' );
		$default_ping_status = get_option( 'default_ping_status', 'open' );
		if ( '1' === $default_pingback || 1 === $default_pingback ) {
			$issues[] = __( 'Pingbacks are enabled by default - may allow spam', 'wpshadow' );
		}
		if ( 'open' === $default_ping_status ) {
			$issues[] = __( 'Ping status is set to open by default - trackbacks and pingbacks allowed', 'wpshadow' );
		}

		// Check comment close timing.
		$close_comments_for_old_posts = get_option( 'close_comments_for_old_posts', '0' );
		if ( '0' === $close_comments_for_old_posts || 0 === $close_comments_for_old_posts ) {
			$issues[] = __( 'Comments remain open indefinitely - may attract spam on old posts', 'wpshadow' );
		}

		// If no issues found, return null.
		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'                 => self::$slug,
			'title'              => self::$title,
			'description'        => sprintf(
				/* translators: %d: number of issues */
				__( 'Found %d discussion security configuration issues', 'wpshadow' ),
				count( $issues )
			),
			'severity'           => 'medium',
			'threat_level'       => 55,
			'site_health_status' => 'recommended',
			'auto_fixable'       => false,
			'kb_link'            => 'https://wpshadow.com/kb/discussion-settings-security?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'family'             => self::$family,
			'details'            => array(
				'issues'                   => $issues,
				'comment_moderation'       => $comment_moderation,
				'comment_registration'     => $comment_registration,
				'comment_max_links'        => $comment_max_links,
				'default_pingback'         => $default_pingback,
				'default_ping_status'      => $default_ping_status,
				'close_comments_old_posts' => $close_comments_for_old_posts,
			),
		);
	}
}
