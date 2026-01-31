<?php
/**
 * Comment Text Length Limits Diagnostic
 *
 * Verifies comment length limits are properly enforced.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26031.1300
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Comment Text Length Diagnostic Class
 *
 * @since 1.26031.1300
 */
class Diagnostic_Comment_Text_Length_Limits extends Diagnostic_Base {

	protected static $slug = 'comment-text-length-limits';
	protected static $title = 'Comment Text Length Limits';
	protected static $description = 'Verifies comment length limits are enforced';
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26031.1300
	 * @return array|null
	 */
	public static function check() {
		// Check for custom comment length validation.
		$has_length_filter = has_filter( 'preprocess_comment', 'wp_filter_kses' ) ||
		                     has_filter( 'comment_text', 'wp_kses_post' ) ||
		                     has_filter( 'preprocess_comment' );

		// Check database for excessively long comments.
		global $wpdb;
		$long_comments = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->comments} WHERE CHAR_LENGTH(comment_content) > 5000"
		);

		if ( $long_comments > 0 && ! $has_length_filter ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of long comments */
					__( 'Found %d comments exceeding 5000 characters with no length validation', 'wpshadow' ),
					$long_comments
				),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/comment-text-length-limits',
			);
		}

		return null;
	}
}
