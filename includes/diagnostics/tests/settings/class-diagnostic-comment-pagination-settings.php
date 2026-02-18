<?php
/**
 * Comment Pagination Settings Diagnostic
 *
 * Verifies comment pagination is configured for performance and usability.
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
 * Comment Pagination Settings Diagnostic Class
 *
 * Checks comment pagination configuration.
 *
 * @since 1.6032.1755
 */
class Diagnostic_Comment_Pagination_Settings extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-pagination-settings';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Pagination Settings';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies comment pagination configuration';

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

		// Check if pagination is enabled.
		$page_comments = get_option( 'page_comments', 0 );
		$comments_per_page = get_option( 'comments_per_page', 50 );

		// Check posts with many comments.
		global $wpdb;
		$max_comments = $wpdb->get_var(
			"SELECT MAX(comment_count) FROM {$wpdb->posts} WHERE comment_count > 0"
		);

		if ( ! $page_comments && $max_comments > 50 ) {
			$issues[] = sprintf(
				/* translators: %d: maximum comment count */
				__( 'Pagination disabled but posts have up to %d comments - may impact performance', 'wpshadow' ),
				$max_comments
			);
		}

		if ( $page_comments ) {
			// Check comments per page setting.
			if ( $comments_per_page < 10 ) {
				$issues[] = __( 'Very few comments per page - users may need many page loads', 'wpshadow' );
			} elseif ( $comments_per_page > 200 ) {
				$issues[] = sprintf(
					/* translators: %d: comments per page */
					__( 'Too many comments per page (%d) may slow page load', 'wpshadow' ),
					$comments_per_page
				);
			}

			// Check default page to display.
			$default_comments_page = get_option( 'default_comments_page', 'newest' );
			if ( $default_comments_page === 'oldest' && $max_comments > 100 ) {
				$issues[] = __( 'Showing oldest comments first on posts with many comments may hide recent discussion', 'wpshadow' );
			}
		}

		// Check if AJAX comment loading is available.
		if ( $page_comments && ! wp_script_is( 'comment-reply', 'registered' ) ) {
			$issues[] = __( 'Comment pagination enabled but comment-reply script not available', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/comment-pagination-settings',
			);
		}

		return null;
	}
}
