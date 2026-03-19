<?php
/**
 * No Email Opt-in Forms Diagnostic
 *
 * Detects missing email capture forms, losing opportunities to
 * build audience and drive conversions.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Engagement
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * No Email Opt-in Forms Diagnostic Class
 *
 * Checks for email list building mechanisms to ensure visitor
 * capture and ongoing engagement opportunities.
 *
 * **Why This Matters:**
 * - Email has 40x higher conversion than social
 * - You own your email list (platform independent)
 * - Email ROI: $42 for every $1 spent
 * - Visitors leave and never return without capture
 * - Email enables ongoing relationships
 *
 * **Email Capture Best Practices:**
 * - Popup after 30 seconds or scroll 50%
 * - Inline forms mid-content
 * - Exit-intent popups
 * - Content upgrades (lead magnets)
 * - Welcome bar at top
 *
 * @since 1.6093.1200
 */
class Diagnostic_No_Email_Opt_In_Forms extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-email-opt-in-forms';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'No Email Opt-in Forms';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Missing email capture forms, losing opportunities to build your audience';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'engagement';

	/**
	 * Run the diagnostic check
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if no email forms detected, null otherwise.
	 */
	public static function check() {
		// Check for popular email marketing plugins
		if ( self::has_email_marketing_plugin() ) {
			return null;
		}

		// Check for email forms in widgets
		if ( self::has_email_form_widget() ) {
			return null;
		}

		// Check content for email signup shortcodes/blocks
		$recent_posts = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => 10,
			)
		);

		$email_form_patterns = array(
			'[mc4wp_form', // MailChimp
			'[mailpoet',   // MailPoet
			'[convertkit', // ConvertKit
			'wp:mailchimp',
			'wp:newsletter',
		);

		foreach ( $recent_posts as $post ) {
			foreach ( $email_form_patterns as $pattern ) {
				if ( strpos( $post->post_content, $pattern ) !== false ) {
					return null; // Found email form in content
				}
			}
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'No email capture forms detected. You\'re losing every visitor who doesn\'t subscribe. Install an email marketing plugin to build your audience.', 'wpshadow' ),
			'severity'     => 'high',
			'threat_level' => 60,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/email-list-building',
			'details'      => array(
				'message'          => 'Install email marketing plugin and add opt-in forms',
				'recommended_tools' => array(
					'MailerLite (free up to 1000 subscribers)',
					'MailPoet (WordPress native)',
					'ConvertKit (creator focused)',
					'Mailchimp for WordPress',
				),
				'placement_tips'   => array(
					'Popup after 30 seconds',
					'Inline form after introduction',
					'Exit-intent popup',
					'Sidebar widget',
					'Footer signup',
				),
			),
		);
	}

	/**
	 * Check if email marketing plugin is installed
	 *
	 * @since 1.6093.1200
	 * @return bool True if plugin detected, false otherwise.
	 */
	private static function has_email_marketing_plugin() {
		$email_plugins = array(
			'mailchimp-for-wp/mailchimp-for-wp.php',
			'mailpoet/mailpoet.php',
			'convertkit/wp-convertkit.php',
			'newsletter/plugin.php',
			'email-subscribers/email-subscribers.php',
		);

		foreach ( $email_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if email form exists in widgets
	 *
	 * @since 1.6093.1200
	 * @return bool True if email widget found, false otherwise.
	 */
	private static function has_email_form_widget() {
		$sidebars = wp_get_sidebars_widgets();

		foreach ( $sidebars as $sidebar => $widgets ) {
			if ( empty( $widgets ) || 'wp_inactive_widgets' === $sidebar ) {
				continue;
			}

			foreach ( $widgets as $widget ) {
				// Check for common email widget prefixes
				if ( strpos( $widget, 'mc4wp' ) === 0 ||
				     strpos( $widget, 'mailpoet' ) === 0 ||
				     strpos( $widget, 'newsletter' ) === 0 ) {
					return true;
				}
			}
		}

		return false;
	}
}
