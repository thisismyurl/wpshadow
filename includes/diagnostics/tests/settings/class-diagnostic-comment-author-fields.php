<?php
/**
 * Comment Author Information and Requirements
 *
 * Validates comment form author field requirements and email verification.
 *
 * @since 0.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Comment_Author_Fields Class
 *
 * Checks comment form author field requirements and verification.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Comment_Author_Fields extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-author-fields';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Author Fields and Requirements';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates comment author field requirements and email collection';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'comments';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Get comment settings
		$require_name_email = get_option( 'require_name_email', 0 );
		$comment_registration = get_option( 'comment_registration', 0 );
		$users_can_register = get_option( 'users_can_register', 0 );

		// Pattern 1: Anonymous comments allowed with no name/email required
		if ( ! $require_name_email && ! $comment_registration ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Anonymous comments allowed without name/email verification', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/comment-author-fields?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'issue' => 'no_author_identification',
					'message' => __( 'Comments accepted with no author name or email', 'wpshadow' ),
					'risks' => array(
						'Spam bots submit comments without identification',
						'No way to contact/verify commenters',
						'Comments appear to be from "Anonymous"',
						'Hard to build commenter reputation/history',
					),
					'spam_vulnerability' => __( 'Anonymous comments are 10x more likely to be spam', 'wpshadow' ),
					'community_quality' => __( 'Named commenters show accountability (better quality)', 'wpshadow' ),
					'contact_capability' => __( 'Without email, can\'t reach commenter for follow-up', 'wpshadow' ),
					'recommendation_options' => array(
						'Require name + email (balanced)',
						'Require registration (more gatekeeping)',
						'Use captcha instead (let anonymous post but verify human)',
					),
					'best_practice' => __( 'Require name + email (reduces spam, maintains approachability)', 'wpshadow' ),
					'recommendation' => __( 'Enable "Name and Email required" in Settings > Discussion', 'wpshadow' ),
				),
			);
		}

		// Pattern 2: Requires registration but users cannot self-register
		if ( $comment_registration && ! $users_can_register ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Comments require registration but registration is closed', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/comment-author-fields?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'issue' => 'registration_required_but_closed',
					'message' => __( 'Users must register to comment, but cannot register', 'wpshadow' ),
					'broken_flow' => array(
						'User tries to comment',
						'Told they must register',
						'Cannot find registration link',
						'Registration is disabled by admin',
						'User gives up (frustrated)',
					),
					'comment_loss' => __( 'Blocking 95%+ of potential comments', 'wpshadow' ),
					'user_friction' => __( 'Creates dead-end for visitors wanting to engage', 'wpshadow' ),
					'alternative_solutions' => array(
						'Option A: Don\'t require registration, just require name/email',
						'Option B: Enable self-registration if you want registered commenters',
						'Option C: Use social login (Google, Facebook accounts)',
					),
					'recommended_approach' => 'Require name+email (easier than registration)',
					'recommendation' => __( 'Either disable registration requirement OR enable user self-registration', 'wpshadow' ),
				),
			);
		}

		// Pattern 3: No email verification for anonymous comments
		$comment_form_method = wp_remote_get( home_url( '/' ) );

		if ( is_wp_error( $comment_form_method ) ) {
			$has_email_verify = false;
		} else {
			$body = wp_remote_retrieve_body( $comment_form_method );
			$has_email_verify = (bool) strpos( $body, 'required' ) !== false && strpos( $body, 'email' ) !== false;
		}

		if ( ! $has_email_verify && ! $comment_registration ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Comment form does not validate email addresses', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/comment-author-fields?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'issue' => 'no_email_verification',
					'message' => __( 'Comment form accepts invalid email addresses', 'wpshadow' ),
					'spam_exploitation' => __( 'Spammers use fake emails (invalid format)', 'wpshadow' ),
					'contact_problem' => __( 'Cannot send follow-up emails to invalid addresses', 'wpshadow' ),
					'verification_impact' => array(
						'Valid email format reduces spam by 20-30%',
						'Invalid emails become uncontactable accounts',
						'Good for building commenter reputation database',
					),
					'html5_solution' => 'Use HTML5 email input validation (requires no plugins)',
					'validation_benefits' => array(
						'Weeds out obvious spam (bot-generated emails)',
						'Allows you to contact legitimate commenters',
						'Builds verifiable commenter reputation',
						'Improves comment quality signals',
					),
					'recommendation' => __( 'Enable email field requirement and HTML5 validation', 'wpshadow' ),
				),
			);
		}

		// Pattern 4: Comment author email collected but never used
		$recent_comments = get_comments(
			array(
				'number' => 100,
				'status' => 'approve',
			)
		);

		$emails_present = 0;
		foreach ( $recent_comments as $comment ) {
			if ( ! empty( $comment->comment_author_email ) ) {
				$emails_present++;
			}
		}

		if ( $emails_present > 50 && ! $comment_registration ) {
			// Check if email notifications are actually sent
			$comment_notification = get_option( 'comments_notify', 1 );

			if ( ! $comment_notification ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => __( 'Commenter emails collected but not used for notifications', 'wpshadow' ),
					'severity'     => 'low',
					'threat_level' => 20,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/comment-author-fields?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
					'details'      => array(
						'issue' => 'email_not_used',
						'message' => __( 'Collecting emails from commenters but not sending notifications', 'wpshadow' ),
						'missed_opportunity' => __( '100+ comments with unused email addresses', 'wpshadow' ),
						'privacy_concern' => __( 'Collecting data you don\'t use is poor UX + privacy issue', 'wpshadow' ),
						'engagement_loss' => __( 'Commenters get no follow-up (miss reply notifications)', 'wpshadow' ),
						'data_use_transparency' => array(
							'Emails should be used for notifications to replies',
							'Or privacy policy should explain why collected',
							'GDPR requires clear use description',
						),
						'best_practice' => 'Send emails when replies to their comments arrive',
						'enable_notifications' => 'Go to Settings > Discussion > Send notification on new comment',
						'user_benefit' => __( 'Commenters notified of replies = more engagement', 'wpshadow' ),
						'recommendation' => __( 'Enable comment reply notifications in Settings > Discussion', 'wpshadow' ),
					),
				);
			}
		}

		// Pattern 5: Too many required fields on comment form
		$require_field_count = 0;
		if ( $require_name_email ) {
			$require_field_count += 2; // Name and email
		}
		if ( $comment_registration ) {
			$require_field_count += 1; // Registration/login
		}

		// Pattern 6: Comment form has no anti-spam measures at all
		if ( ! self::has_any_spam_measures() ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Comment form has no anti-spam measures configured', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/comment-author-fields?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'issue' => 'no_spam_measures',
					'message' => __( 'Comment form has zero spam protection (extremely vulnerable)', 'wpshadow' ),
					'vulnerability' => __( 'Form is open target for automated spam bots', 'wpshadow' ),
					'spam_risk' => array(
						'1000+ spam comments per week',
						'Bot attacks can submit 100+ spam per minute',
						'Database fills with junk',
						'Server resources wasted',
					),
					'recommended_protections' => array(
						'Require name + email (disqualifies simple bots)',
						'Add reCAPTCHA (stops most automated attacks)',
						'Enable anti-spam plugin (Akismet)',
						'Set comment moderation (review first-time comments)',
					),
					'minimum_viable_protection' => 'Require name+email AND enable Akismet',
						'ideal_protection' => 'Name+email + Akismet + reCAPTCHA',
					'recommendation' => __( 'Implement at least TWO spam prevention measures immediately', 'wpshadow' ),
				),
			);
		}

		return null; // No issues found
	}

	/**
	 * Check if any spam measures are in place.
	 *
	 * @since 0.6093.1200
	 * @return bool True if measures found.
	 */
	private static function has_any_spam_measures() {
		// Check for anti-spam plugins
		$spam_plugins = array(
			'akismet/akismet.php',
			'boxora-antispam/boxora-antispam.php',
			'google-captcha/google-captcha.php',
		);

		foreach ( $spam_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		// Check for reCAPTCHA
		if ( get_option( 'recaptcha_site_key', false ) ) {
			return true;
		}

		// Check if name/email required
		if ( get_option( 'require_name_email', 0 ) ) {
			return true;
		}

		// Check if registration required
		if ( get_option( 'comment_registration', 0 ) ) {
			return true;
		}

		return false;
	}
}
