<?php
/**
 * Comment Author Information and Requirements
 *
 * Validates comment form author field requirements and email verification.
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
 * Treatment_Comment_Author_Fields Class
 *
 * Checks comment form author field requirements and verification.
 *
 * @since 1.6030.2148
 */
class Treatment_Comment_Author_Fields extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-author-fields';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Author Fields and Requirements';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates comment author field requirements and email collection';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Comment_Author_Fields' );
	}

	/**
	 * Check if any spam measures are in place.
	 *
	 * @since  1.6030.2148
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
