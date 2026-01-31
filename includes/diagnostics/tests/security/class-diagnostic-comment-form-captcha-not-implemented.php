<?php
/**
 * Comment Form CAPTCHA Not Implemented Diagnostic
 *
 * Checks if comment form CAPTCHA is implemented.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Comment Form CAPTCHA Not Implemented Diagnostic Class
 *
 * Detects missing comment CAPTCHA.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Comment_Form_CAPTCHA_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-form-captcha-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Form CAPTCHA Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if comment form CAPTCHA is implemented';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for comment CAPTCHA
		if ( ! has_filter( 'comment_form_after_fields', 'display_comment_captcha' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Comment form CAPTCHA is not implemented. Add reCAPTCHA to comment forms to reduce spam and automated attacks.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 40,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/comment-form-captcha-not-implemented',
			);
		}

		return null;
	}
}
