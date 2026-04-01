<?php
/**
 * Blog Comments Closed By Default Diagnostic
 *
 * Checks if blog comments are closed by default.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Blog Comments Closed By Default Diagnostic Class
 *
 * Detects open comments by default.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Blog_Comments_Closed_By_Default extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'blog-comments-closed-by-default';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Blog Comments Closed By Default';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if blog comments are closed by default';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'admin';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if comments are open by default
		$default_comment_status = get_option( 'default_comment_status' );
		if ( 'open' === $default_comment_status ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Comments are open by default on new posts. Close comments by default and selectively enable them to reduce spam and moderation burden.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 15,
				'auto_fixable'  => true,
				'kb_link'       => 'https://wpshadow.com/kb/blog-comments-closed-by-default?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
