<?php
/**
 * Comment Form CAPTCHA Not Configured Diagnostic
 *
 * Checks if CAPTCHA protection on comments is enabled.
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
 * Comment Form CAPTCHA Not Configured Diagnostic Class
 *
 * Detects missing CAPTCHA on comment forms.
 *
 * @since 1.2601.2310
 */
class Diagnostic_Comment_Form_CAPTCHA_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-form-captcha-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Form CAPTCHA Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if CAPTCHA is enabled on comment forms';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2310
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if comments are enabled
		if ( ! get_option( 'default_comments_page' ) ) {
			return null; // Comments disabled
		}

		// Check for CAPTCHA plugins
		$captcha_plugins = array(
			'google-captcha/google-captcha.php',
			'really-simple-captcha/really-simple-captcha.php',
			'wp-recaptcha/wp-recaptcha.php',
		);

		$captcha_active = false;
		foreach ( $captcha_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$captcha_active = true;
				break;
			}
		}

		if ( ! $captcha_active ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Comment forms have no CAPTCHA protection. Protect comment forms with CAPTCHA to prevent automated spam.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 50,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/comment-form-captcha-not-configured',
			);
		}

		return null;
	}
}
