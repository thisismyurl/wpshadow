<?php
/**
 * Comment Spam and Moderation Configuration
 *
 * Validates spam prevention and comment moderation setup.
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
 * Diagnostic_Comment_Spam_Moderation Class
 *
 * Checks for proper spam prevention and comment moderation configuration.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Comment_Spam_Moderation extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-spam-moderation';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Spam and Moderation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates spam prevention and comment moderation setup';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'comments';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Get spam comment count
		$spam_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_approved = 'spam'" );
		$trash_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_approved = 'trash'" );

		// Pattern 1: No spam protection plugin installed
		$has_spam_plugin = self::has_spam_plugin();

		if ( ! $has_spam_plugin ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'No spam protection or anti-spam plugin detected', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/comment-spam-moderation',
				'details'      => array(
					'issue' => 'no_spam_protection',
					'message' => __( 'No anti-spam or spam protection plugin is installed', 'wpshadow' ),
					'spam_statistics' => array(
						'80%+ of all comments are spam',
						'Average site receives 1,000+ spam comments monthly',
						'Unprotected sites can receive 10,000+ daily',
					),
					'problems_without_spam_filter' => array(
						'Manual moderation becomes overwhelming',
						'Spam clogs database (performance impact)',
						'Links in spam comments harm SEO (bad backlinks)',
						'Spam attracts more spam (SEO spam signals)',
						'Admin constantly fighting spam',
					),
					'recommended_solutions' => array(
						'Akismet' => 'Most popular, cloud-based ML detection, 99.99% accuracy',
						'Boxora AntiSpam' => 'AI-powered, highly accurate, no subscription',
						'CleanTalk' => 'Real-time protection, multi-platform',
						'WP-SpamShield' => 'Comprehensive spam blocking',
						'Google reCAPTCHA' => 'Free, integrated challenge system',
					),
					'business_impact' => __( 'Spam filter reduces moderation burden by 90%+', 'wpshadow' ),
					'recommendation' => __( 'Install Akismet or alternative anti-spam plugin immediately', 'wpshadow' ),
				),
			);
		}

		// Pattern 2: Akismet (or similar) plugin installed but not configured
		$spam_plugin = self::get_active_spam_plugin();
		if ( $spam_plugin && ! self::is_spam_plugin_configured( $spam_plugin ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Spam plugin installed but not configured', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/comment-spam-moderation',
				'details'      => array(
					'issue' => 'spam_plugin_not_configured',
					'plugin_name' => $spam_plugin,
					'message' => sprintf(
						/* translators: %s: plugin name */
						__( '%s is installed but not activated/configured (missing API key or settings)', 'wpshadow' ),
						$spam_plugin
					),
					'why_urgent' => __( 'Plugin without configuration provides ZERO spam protection', 'wpshadow' ),
					'setup_steps' => array(
						'1. Go to plugin settings (usually under Settings)',
						'2. Click "Activate" or "Connect"',
						'3. Follow authentication steps',
						'4. For Akismet: Sign up for free/paid key at akismet.com',
						'5. Paste API key in plugin settings',
						'6. Enable spam filtering',
						'7. Test with sample spam comment',
					),
					'activation_priority' => 'URGENT - Spam floods in without this',
					'testing_after_setup' => __( 'Submit test comment with spam keywords to verify working', 'wpshadow' ),
				),
			);
		}

		// Pattern 3: Comment moderation queue too large (unreviewed comments)
		$pending_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_approved = '0'" );

		if ( $pending_count > 50 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Large backlog of unmoderated comments', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/comment-spam-moderation',
				'details'      => array(
					'issue' => 'large_pending_queue',
					'pending_count' => intval( $pending_count ),
					'message' => sprintf(
						/* translators: %d: number of pending comments */
						__( '%d comments awaiting moderation - queue building up', 'wpshadow' ),
						intval( $pending_count )
					),
					'user_experience_impact' => __( 'Legitimate commenters see no feedback (confusing experience)', 'wpshadow' ),
					'common_causes' => array(
						'Comment moderation enabled but not reviewed regularly',
						'New comments require approval (frustrates users)',
						'Moderation backlog grows faster than review speed',
						'No notifications set up for pending comments',
					),
					'solutions' => array(
						'Enable comment auto-approve for first-time commenters',
						'Set email notifications for pending comments',
						'Moderate comments weekly (batch review)',
						'Use spam filter to auto-trash obvious spam',
						'Disable comments on old posts (reduce volume)',
					),
					'user_retention' => __( 'Unapproved comments lead to 40% fewer future comments', 'wpshadow' ),
					'moderation_settings' => 'Consider auto-approving known commenters, manually moderate first-timers',
					'recommendation' => __( 'Review and approve pending comments, then streamline moderation workflow', 'wpshadow' ),
				),
			);
		}

		// Pattern 4: High spam count but spam folder not cleaned regularly
		if ( $spam_count > 100 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Large accumulation of spam comments not cleaned', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/comment-spam-moderation',
				'details'      => array(
					'issue' => 'spam_accumulation',
					'spam_count' => intval( $spam_count ),
					'message' => sprintf(
						/* translators: %d: number of spam comments */
						__( '%d spam comments accumulated in database', 'wpshadow' ),
						intval( $spam_count )
					),
					'database_impact' => __( 'Each spam comment adds database bloat (slows queries)', 'wpshadow' ),
					'space_estimate' => sprintf(
						/* translators: %d: spam count */
						__( '%d spam comments = approximately 50-100KB wasted space', 'wpshadow' ),
						intval( $spam_count )
					),
					'cleanup_recommendation' => array(
						'Permanently delete spam comments older than 30 days',
						'Keep recent spam for verification',
						'Use plugin to batch delete old spam',
						'Schedule automatic spam deletion (monthly)',
					),
					'cleanup_tools' => array(
						'Admin > Comments > Spam (bulk actions)',
						'Advanced plugins: Optimize Database after Deleting Revisions',
						'WP-Optimize: Auto-delete old spam',
					),
					'maintenance_task' => __( 'Regular spam cleanup prevents database bloat', 'wpshadow' ),
					'recommendation' => __( 'Delete accumulated spam to keep database lean', 'wpshadow' ),
				),
			);
		}

		// Pattern 5: No comment notification emails configured
		$moderation_notify = get_option( 'moderation_notify', 1 );

		if ( ! $moderation_notify ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Admin notification emails disabled for pending comments', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/comment-spam-moderation',
				'details'      => array(
					'issue' => 'no_comment_notifications',
					'message' => __( 'Notifications disabled: Admin won\'t know about pending comments', 'wpshadow' ),
					'consequence' => __( 'Pending comments never get reviewed (pile up indefinitely)', 'wpshadow' ),
					'user_impact' => __( 'Commenters never see their comments published', 'wpshadow' ),
					'recommendation' => __( 'Enable moderation notification emails in Settings > Discussion', 'wpshadow' ),
					'notification_settings' => array(
						'Email admin when comment awaiting approval',
						'Email admin when comment marked as spam',
						'Email admin when comment removed',
					),
					'best_practice' => __( 'Moderation happens faster when you get email notifications', 'wpshadow' ),
				),
			);
		}

		// Pattern 6: Comment form not protected with CAPTCHA
		$comment_form_protected = self::is_comment_form_protected();

		if ( ! $comment_form_protected ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Comment form lacks CAPTCHA or bot protection', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/comment-spam-moderation',
				'details'      => array(
					'issue' => 'no_captcha_protection',
					'message' => __( 'Comment form has no CAPTCHA or bot prevention', 'wpshadow' ),
					'bot_attack_potential' => __( 'Automated bots can spam at 100+ comments per minute', 'wpshadow' ),
					'benefits_of_captcha' => array(
						'Stops automated bot spam attacks',
						'Requires human interaction (bots fail)',
						'Complements anti-spam filters',
						'Works even with spam filter disabled',
					),
					'captcha_options' => array(
						'Google reCAPTCHA v3' => 'Invisible, best user experience, free',
						'Google reCAPTCHA v2' => 'Checkbox, widely recognized, free',
						'hCaptcha' => 'Privacy-focused alternative',
						'Math CAPTCHA' => 'Simple text-based challenge',
					),
					'implementation' => 'Use reCAPTCHA v3 (invisible, most user-friendly)',
					'user_experience' => __( 'reCAPTCHA v3 invisible to users (no extra clicks)', 'wpshadow' ),
					'recommendation' => __( 'Add Google reCAPTCHA v3 to comment form for bot protection', 'wpshadow' ),
				),
			);
		}

		return null; // No issues found
	}

	/**
	 * Check if spam protection plugin installed.
	 *
	 * @since  1.2601.2148
	 * @return bool True if installed.
	 */
	private static function has_spam_plugin() {
		$spam_plugins = array(
			'akismet/akismet.php',
			'boxora-antispam/boxora-antispam.php',
			'cleantalk-spam-protect/cleantalk.php',
			'wp-spamshield/wp-spamshield.php',
			'antispam-bee/antispam-bee.php',
		);

		foreach ( $spam_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) || file_exists( WP_PLUGIN_DIR . '/' . dirname( $plugin ) ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get active spam plugin name.
	 *
	 * @since  1.2601.2148
	 * @return string Plugin name or empty.
	 */
	private static function get_active_spam_plugin() {
		$spam_plugins = array(
			'akismet/akismet.php' => 'Akismet',
			'boxora-antispam/boxora-antispam.php' => 'Boxora AntiSpam',
			'cleantalk-spam-protect/cleantalk.php' => 'CleanTalk',
			'wp-spamshield/wp-spamshield.php' => 'WP-SpamShield',
		);

		foreach ( $spam_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				return $name;
			}
		}

		return '';
	}

	/**
	 * Check if spam plugin is configured.
	 *
	 * @since  1.2601.2148
	 * @param  string $plugin Plugin name.
	 * @return bool True if configured.
	 */
	private static function is_spam_plugin_configured( $plugin ) {
		if ( 'Akismet' === $plugin ) {
			return (bool) get_option( 'wordpress_api_key', false );
		}

		return true; // Assume configured if we can't determine
	}

	/**
	 * Check if comment form is protected.
	 *
	 * @since  1.2601.2148
	 * @return bool True if protected.
	 */
	private static function is_comment_form_protected() {
		// Check for reCAPTCHA plugin
		if ( is_plugin_active( 'google-captcha/google-captcha.php' ) ) {
			return true;
		}

		if ( is_plugin_active( 'wordfence/wordfence.php' ) ) {
			return true;
		}

		// Check for other protection
		return (bool) get_option( 'recaptcha_site_key', false );
	}
}
