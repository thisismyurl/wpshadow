<?php
/**
 * Unapproved Comments Blocking Content Diagnostic
 *
 * Checks if unapproved comments are blocking page performance.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2309
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH ' ) ) {
	exit;
}

/**
 * Unapproved Comments Blocking Content Diagnostic Class
 *
 * Detects if unapproved comments impact page performance.
 *
 * @since 1.2601.2309
 */
class Diagnostic_Unapproved_Comments_Blocking_Content extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'unapproved-comments-blocking-content';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Unapproved Comments Blocking Content';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if unapproved comments are blocking page performance';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2309
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Check if comments are allowed and awaiting approval
		$moderate_comments = get_option( 'comment_moderation', 0 );

		if ( ! $moderate_comments ) {
			return null;
		}

		// Count unapproved comments per post
		$unapproved_per_post = $wpdb->get_results(
			"SELECT comment_post_ID, COUNT(*) as count 
			 FROM {$wpdb->comments} 
			 WHERE comment_approved = 0 
			 GROUP BY comment_post_ID 
			 HAVING count > 10"
		);

		if ( ! empty( $unapproved_per_post ) ) {
			$high_backlog_posts = count( $unapproved_per_post );

			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					/* translators: %d: number of posts */
					__( '%d posts have large comment moderation backlogs', 'wpshadow' ),
					$high_backlog_posts
				),
				'severity'      => 'medium',
				'threat_level'  => 40,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/unapproved-comments-blocking-content',
			);
		}

		return null;
	}
}
