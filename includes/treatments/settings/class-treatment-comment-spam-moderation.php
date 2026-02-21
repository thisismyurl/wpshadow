<?php
/**
 * Comment Spam and Moderation Configuration
 *
 * Validates spam prevention and comment moderation setup.
 *
 * @since   1.6030.2148
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Comment_Spam_Moderation Class
 *
 * Checks for proper spam prevention and comment moderation configuration.
 *
 * @since 1.6030.2148
 */
class Treatment_Comment_Spam_Moderation extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-spam-moderation';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Spam and Moderation';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates spam prevention and comment moderation setup';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'comments';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6030.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Comment_Spam_Moderation' );
	}

	/**
	 * Check if spam protection plugin installed.
	 *
	 * @since  1.6030.2148
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
	 * @since  1.6030.2148
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
	 * @since  1.6030.2148
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
	 * @since  1.6030.2148
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
