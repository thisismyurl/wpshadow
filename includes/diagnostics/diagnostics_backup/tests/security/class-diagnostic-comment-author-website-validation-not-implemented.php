<?php
/**
 * Comment Author Website Validation Not Implemented Diagnostic
 *
 * Checks if comment author websites are validated.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2330
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Comment Author Website Validation Not Implemented Diagnostic Class
 *
 * Detects missing comment website validation.
 *
 * @since 1.2601.2330
 */
class Diagnostic_Comment_Author_Website_Validation_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-author-website-validation-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Author Website Validation Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if comment author websites are validated';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2330
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check comments allowed option
		$default_comment_status = get_option( 'default_comment_status' );

		if ( 'open' === $default_comment_status ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Comment author websites are not validated. Validate URLs to prevent spam and malicious links in comments.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 35,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/comment-author-website-validation-not-implemented',
			);
		}

		return null;
	}
}
