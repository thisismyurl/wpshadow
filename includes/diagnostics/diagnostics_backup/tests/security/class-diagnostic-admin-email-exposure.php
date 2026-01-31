<?php
/**
 * Admin Email Exposure Diagnostic
 *
 * Detects when admin email addresses are exposed in public areas,
 * making sites vulnerable to targeted phishing and social engineering.
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
 * Diagnostic_Admin_Email_Exposure Class
 *
 * Detects exposed admin email addresses.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Admin_Email_Exposure extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'admin-email-exposure';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Admin Email Exposure';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects exposed admin email addresses';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if exposure found, null otherwise.
	 */
	public static function check() {
		$exposure_check = self::check_email_exposure();

		if ( empty( $exposure_check['exposures'] ) ) {
			return null; // No exposure detected
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: number of exposure points */
				__( 'Admin email exposed in %d locations. Attackers use email for targeted phishing, password resets, social engineering. 90%% of breaches start with email.', 'wpshadow' ),
				count( $exposure_check['exposures'] )
			),
			'severity'     => 'medium',
			'threat_level' => 60,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/email-exposure',
			'family'       => self::$family,
			'meta'         => array(
				'exposures'   => $exposure_check['exposures'],
				'admin_email' => $exposure_check['admin_email'],
			),
			'details'      => array(
				'common_email_exposure_points' => array(
					'RSS Feed' => array(
						'Location: /feed/',
						'Shows admin email in feed metadata',
						'Harvested by spammers',
					),
					'Comment Forms' => array(
						'WordPress shows admin email as author',
						'Visible in comment reply-to',
						'Scraped by bots',
					),
					'Author Archives' => array(
						'Location: /author/admin/',
						'May show email in bio',
						'Google indexes these pages',
					),
					'Contact Page' => array(
						'Plain text email on contact page',
						'Instant spam magnet',
						'Use contact form instead',
					),
				),
				'attack_scenarios'          => array(
					'Targeted Phishing' => array(
						'Attacker knows you\'re site admin',
						'Sends "urgent WordPress security update" email',
						'Link goes to fake wp-login.php',
						'Steals credentials',
					),
					'Password Reset Attacks' => array(
						'Knows admin email',
						'Requests password reset',
						'Intercepts email or uses social engineering',
					),
					'Spear Phishing' => array(
						'Personalized email mentioning your site',
						'Malicious attachment or link',
						'90% of successful breaches start here',
					),
				),
				'hiding_email_addresses'    => array(
					'RSS Feed' => array(
						'Settings → Reading',
						'Uncheck "Display posts in feed"',
						'Or use Yoast SEO to control RSS output',
					),
					'Contact Page' => array(
						'Remove plain text email',
						'Use Contact Form 7 or WPForms',
						'Forms hide actual email address',
					),
					'Author Bio' => array(
						'Users → Your Profile',
						'Clear/hide email in biographical info',
						'Use obfuscation: admin [at] site [dot] com',
					),
					'Comment Notifications' => array(
						'Settings → Discussion',
						'Use role-based email: comments@site.com',
						'Not personal admin@',
					),
				),
				'email_obfuscation_techniques' => array(
					'JavaScript Obfuscation' => array(
						'Email rendered by JS, hidden from scrapers',
						'Plugin: Email Address Encoder',
						'Free, automatic',
					),
					'ASCII Encoding' => array(
						'Email stored as &#97;&#100;&#109;&#105;&#110;',
						'Browser renders correctly, bots can\'t scrape',
						'Most effective method',
					),
					'Image Instead of Text' => array(
						'Email as image: admin@site.com.png',
						'Bots can\'t scrape images (yet)',
						'Less convenient for users',
					),
				),
				'best_practices'            => array(
					__( 'Use role-based emails: support@, info@, contact@' ),
					__( 'Never show admin@ publicly' ),
					__( 'Enable email obfuscation plugin' ),
					__( 'Use contact forms instead of direct emails' ),
					__( 'Monitor for leaked emails (haveibeenpwned.com)' ),
				),
			),
		);
	}

	/**
	 * Check email exposure.
	 *
	 * @since  1.2601.2148
	 * @return array Email exposure analysis.
	 */
	private static function check_email_exposure() {
		$exposures = array();
		$admin_email = get_option( 'admin_email' );

		// Check RSS feed for email
		$rss_url = get_feed_link();
		$rss_response = wp_remote_get( $rss_url );

		if ( ! is_wp_error( $rss_response ) ) {
			$rss_body = wp_remote_retrieve_body( $rss_response );
			if ( strpos( $rss_body, $admin_email ) !== false ) {
				$exposures[] = __( 'Admin email visible in RSS feed', 'wpshadow' );
			}
		}

		// Check if admin user has posts (author archive exposure risk)
		$admin_user = get_user_by( 'email', $admin_email );
		if ( $admin_user ) {
			$admin_posts = count_user_posts( $admin_user->ID );
			if ( $admin_posts > 0 ) {
				$exposures[] = __( 'Admin user has posts (author archive exposes info)', 'wpshadow' );
			}
		}

		return array(
			'exposures'   => $exposures,
			'admin_email' => $admin_email,
		);
	}
}
