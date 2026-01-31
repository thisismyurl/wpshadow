<?php
/**
 * Comment Spam Accumulation Diagnostic
 *
 * Monitors spam comment volume indicating inadequate
 * filtering or outdated moderation settings.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Comment_Spam_Accumulation Class
 *
 * Monitors spam comment accumulation.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Comment_Spam_Accumulation extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-spam-accumulation';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Spam Accumulation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Monitors spam comment volume';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'quality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if high spam volume, null otherwise.
	 */
	public static function check() {
		$spam_status = self::check_comment_spam();

		if ( $spam_status['spam_count'] < 100 ) {
			return null; // Acceptable spam level
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: number of spam comments */
				__( '%d spam comments in queue. Spam filter failing. Legitimate comments may be caught too. Enable Akismet ($5/mo) or improve filter settings.', 'wpshadow' ),
				$spam_status['spam_count']
			),
			'severity'     => 'low',
			'threat_level' => 35,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/comment-spam',
			'family'       => self::$family,
			'meta'         => array(
				'spam_comments' => $spam_status['spam_count'],
			),
			'details'      => array(
				'impact_of_comment_spam'          => array(
					'Time Wasted' => array(
						'Moderate: 500 spam = hours deleting',
						'Repetitive: Tedious work',
					),
					'Site Health' => array(
						'Links in spam: May be indexed',
						'Trust issue: Visible spam = poorly maintained',
					),
					'Legitimate Comments' => array(
						'Over-aggressive filter: Reject good comments',
						'False positives: Legitimate users frustrated',
					),
				),
				'common_spam_sources'             => array(
					'Automated Bots' => array(
						'Tools: Scripts mass-posting links',
						'Goal: Backlinks for SEO',
						'Volume: 100+ per day',
					),
					'Manual Spam' => array(
						'Humans: Paid to spam',
						'Sophistication: May seem legitimate',
						'Harder to filter automatically',
					),
					'Form Spam' => array(
						'Contact forms: Spam submissions',
						'Goal: Test form, advertise',
						'Prevention: CAPTCHA required',
					),
				),
				'spam_prevention_tools'           => array(
					'Akismet' => array(
						'Cost: $5+/month',
						'Effectiveness: 99.9% spam caught',
						'False positive: < 0.1%',
						'Setup: WordPress plugin',
					),
					'Honeypot' => array(
						'Method: Hidden field, bots fill it',
						'Free: Built into some plugins',
						'Effective: Against unsophisticated bots',
					),
					'CAPTCHA' => array(
						'reCAPTCHA v3: Invisible to users',
						'reCAPTCHA v2: "I\'m not a robot"',
						'Blockbot v2: Comment form protection',
					),
					'Manual Moderation' => array(
						'Setting: All comments require approval',
						'Con: Delays legitimate comments',
						'Use: High-spam situations',
					),
				),
				'spam_filter_settings'            => array(
					'Hold Moderation' => array(
						'Setting: wp-admin → Settings → Discussion',
						'Option: Hold comments with # links',
						'Recommended: Hold comments with 2+ links',
					),
					'Blacklist' => array(
						'Words: Auto-trash comments with words',
						'Example: "viagra", "casino", etc.',
						'Maintenance: Update as spam changes',
					),
					'Require Registration' => array(
						'Setting: Only registered users comment',
						'Pro: Reduces anonymous spam',
						'Con: Reduces engagement',
					),
				),
				'managing_existing_spam'          => array(
					'Bulk Delete' => array(
						'wp-admin → Comments',
						'Filter: Spam, select all, delete',
						'Script: Use wp-cli for large volumes',
					),
					'Automated Cleanup' => array(
						'Plugin: Delete Old Comments',
						'Deletes: Spam/trash comments > 30 days',
					),
				),
			),
		);
	}

	/**
	 * Check comment spam.
	 *
	 * @since  1.2601.2148
	 * @return array Spam status.
	 */
	private static function check_comment_spam() {
		global $wpdb;

		$spam_count = (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_approved = 'spam'"
		);

		return array(
			'spam_count' => $spam_count,
		);
	}
}
