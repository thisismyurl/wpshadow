<?php
/**
 * Comment Close Automation Treatment
 *
 * Tests automatic comment closing on old posts.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6030.1531
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Comment Close Automation Treatment Class
 *
 * Validates that comments are automatically closed on old posts.
 *
 * @since 1.6030.1531
 */
class Treatment_Comment_Close_Automation extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-close-automation';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Close Automation';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests automatic comment closing on old posts';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'settings';

	/**
	 * The family label
	 *
	 * @var string
	 */
	protected static $family_label = 'Settings';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6030.1531
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if automatic comment closing is enabled.
		$close_comments_for_old_posts = get_option( 'close_comments_for_old_posts', '0' );
		if ( '0' === $close_comments_for_old_posts || 0 === $close_comments_for_old_posts ) {
			$issues[] = __( 'Automatic comment closing is disabled', 'wpshadow' );
		} else {
			// Check the threshold for closing comments.
			$close_comments_days_old = (int) get_option( 'close_comments_days_old', 14 );
			if ( $close_comments_days_old < 7 ) {
				$issues[] = sprintf(
					/* translators: %d: number of days */
					__( 'Comment closing threshold is too short (%d days) - may close comments too quickly', 'wpshadow' ),
					$close_comments_days_old
				);
			} elseif ( $close_comments_days_old > 365 ) {
				$issues[] = sprintf(
					/* translators: %d: number of days */
					__( 'Comment closing threshold is very long (%d days) - old posts remain vulnerable to spam', 'wpshadow' ),
					$close_comments_days_old
				);
			}

			// Check if there are old posts with comments still open.
			global $wpdb;
			$cutoff_date                  = gmdate( 'Y-m-d H:i:s', strtotime( "-{$close_comments_days_old} days" ) );
			$old_posts_with_open_comments = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->posts} 
					WHERE post_type = 'post' 
					AND post_status = 'publish' 
					AND comment_status = 'open' 
					AND post_date < %s 
					AND post_date > '2000-01-01'",
					$cutoff_date
				)
			);

			if ( $old_posts_with_open_comments > 0 ) {
				$issues[] = sprintf(
					/* translators: %d: number of posts */
					__( 'Found %d old posts with comments still open despite automation being enabled', 'wpshadow' ),
					$old_posts_with_open_comments
				);
			}
		}

		// Check if there's a cron job for comment closing (WordPress does this on-the-fly).
		// Note: WordPress doesn't use a cron for this, it checks on page load.
		// But we can check if any custom implementations exist.
		$has_comment_close_cron = wp_next_scheduled( 'close_old_post_comments' ) ||
									wp_next_scheduled( 'wp_scheduled_auto_draft_delete' );
		if ( $has_comment_close_cron ) {
			// This is actually good, but we note it for completeness.
			$issues[] = __( 'Custom cron job for comment closing detected - verify it works correctly', 'wpshadow' );
		}

		// Check for sticky posts (which might be exceptions).
		global $wpdb;
		$sticky_posts = get_option( 'sticky_posts', array() );
		if ( ! empty( $sticky_posts ) && '1' === $close_comments_for_old_posts ) {
			// Sanitize sticky post IDs.
			$sticky_ids_sanitized = array_map( 'absint', $sticky_posts );
			$placeholders         = implode( ',', array_fill( 0, count( $sticky_ids_sanitized ), '%d' ) );

			$sticky_with_open_comments = $wpdb->get_var(
				$wpdb->prepare(
					// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					"SELECT COUNT(*) FROM {$wpdb->posts} 
					WHERE ID IN ($placeholders)
					AND comment_status = %s",
					// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					array_merge( $sticky_ids_sanitized, array( 'open' ) )
				)
			);

			if ( $sticky_with_open_comments > 0 ) {
				$issues[] = sprintf(
					/* translators: %d: number of sticky posts */
					__( '%d sticky posts have comments open - they may need special handling', 'wpshadow' ),
					$sticky_with_open_comments
				);
			}
		}

		// If no issues found, return null.
		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'                 => self::$slug,
			'title'              => self::$title,
			'description'        => sprintf(
				/* translators: %d: number of issues */
				__( 'Found %d comment closing automation issues', 'wpshadow' ),
				count( $issues )
			),
			'severity'           => 'low',
			'threat_level'       => 30,
			'site_health_status' => 'recommended',
			'auto_fixable'       => false,
			'kb_link'            => 'https://wpshadow.com/kb/comment-close-automation',
			'family'             => self::$family,
			'details'            => array(
				'issues'                       => $issues,
				'close_comments_enabled'       => $close_comments_for_old_posts,
				'close_comments_days_old'      => isset( $close_comments_days_old ) ? $close_comments_days_old : 0,
				'old_posts_with_open_comments' => isset( $old_posts_with_open_comments ) ? $old_posts_with_open_comments : 0,
			),
		);
	}
}
