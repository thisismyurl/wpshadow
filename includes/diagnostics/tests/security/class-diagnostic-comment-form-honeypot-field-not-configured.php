<?php
/**
 * Comment Form Honeypot Field Not Configured Diagnostic
 *
 * Checks if honeypot field is in comment form.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2349
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Comment Form Honeypot Field Not Configured Diagnostic Class
 *
 * Detects missing honeypot field.
 *
 * @since 1.2601.2349
 */
class Diagnostic_Comment_Form_Honeypot_Field_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-form-honeypot-field-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Form Honeypot Field Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if honeypot field is configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2349
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for honeypot filter
		if ( ! has_filter( 'comment_form_default_fields' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Comment form honeypot field is not configured. Add a hidden honeypot field to catch spam bots.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 25,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/comment-form-honeypot-field-not-configured',
			);
		}

		return null;
	}
}
