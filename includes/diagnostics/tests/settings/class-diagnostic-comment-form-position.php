<?php
/**
 * Comment Form Position Diagnostic
 *
 * Verifies comment form placement is optimized for user engagement.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6032.1755
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Comment Form Position Diagnostic Class
 *
 * Checks comment form positioning relative to existing comments.
 *
 * @since 1.6032.1755
 */
class Diagnostic_Comment_Form_Position extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-form-position';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Form Position';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies comment form position';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'comments';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6032.1755
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check pagination settings that affect form position.
		$page_comments = get_option( 'page_comments', 0 );
		$default_comments_page = get_option( 'default_comments_page', 'newest' );

		if ( $page_comments ) {
			if ( $default_comments_page === 'oldest' ) {
				$issues[] = __( 'Showing oldest comments first may hide comment form below pagination', 'wpshadow' );
			}

			// Check if site has posts with many comments.
			global $wpdb;
			$max_comments = $wpdb->get_var(
				"SELECT MAX(comment_count) FROM {$wpdb->posts} WHERE comment_count > 0"
			);

			if ( $max_comments > 100 ) {
				$issues[] = __( 'Posts with many comments may push form far down page', 'wpshadow' );
			}
		}

		// Check if comments are open by default.
		$default_comment_status = get_option( 'default_comment_status', 'open' );
		if ( $default_comment_status !== 'open' ) {
			// Form position less relevant if comments are closed by default.
			return null;
		}

		// Check theme support for comment form filtering.
		if ( ! current_theme_supports( 'html5', 'comment-form' ) ) {
			$issues[] = __( 'Theme lacks HTML5 comment form support - may limit positioning options', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'low',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/comment-form-position',
			);
		}

		return null;
	}
}
