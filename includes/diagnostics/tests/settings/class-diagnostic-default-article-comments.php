<?php
/**
 * Default Article Comments Diagnostic
 *
 * Verifies comments are appropriately enabled/disabled by default.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6032.1900
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default Article Comments Diagnostic Class
 *
 * Checks default comment status configuration for articles.
 *
 * @since 1.6032.1900
 */
class Diagnostic_Default_Article_Comments extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'default-article-comments';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Default Article Comments';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies default comment status for articles';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'comments';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6032.1900
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check default comment status.
		$default_comment_status = get_option( 'default_comment_status', 'open' );
		$default_pingback_flag = get_option( 'default_pingback_flag', 1 );

		// If comments are disabled by default, explain implications.
		if ( $default_comment_status !== 'open' ) {
			if ( $default_comment_status === 'closed' ) {
				$issues[] = __( 'Comments disabled by default on new articles - users cannot engage', 'wpshadow' );
			}
		}

		// Check if any posts have comments disabled.
		global $wpdb;
		$closed_posts = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts}
			WHERE post_type = 'post' AND comment_status = 'closed' AND post_status = 'publish'"
		);

		if ( $closed_posts > ( wp_count_posts()->publish / 2 ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts with closed comments */
				__( 'Many posts (%d) have comments disabled - inconsistent policy', 'wpshadow' ),
				$closed_posts
			);
		}

		// Check if spam protection is enabled when comments are open.
		if ( $default_comment_status === 'open' ) {
			$comment_moderation = get_option( 'comment_moderation', 0 );
			$comment_registration = get_option( 'comment_registration', 0 );
			$disallowed_keys = get_option( 'disallowed_keys', '' );

			if ( ! $comment_moderation && ! $comment_registration && empty( $disallowed_keys ) ) {
				$issues[] = __( 'Comments open with no moderation or protection - high spam risk', 'wpshadow' );
			}
		}

		// Check pingback setting when comments are enabled.
		if ( $default_comment_status === 'open' && $default_pingback_flag ) {
			$issues[] = __( 'Pingbacks enabled on open comments - additional spam vector', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 35,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/default-article-comments',
			);
		}

		return null;
	}
}
