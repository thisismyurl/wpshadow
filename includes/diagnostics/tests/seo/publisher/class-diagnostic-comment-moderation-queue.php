<?php
/**
 * Comment Moderation Queue Diagnostic
 *
 * Checks if pending comments are being processed.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Comment Moderation Queue Diagnostic Class
 *
 * Verifies that pending comments are being reviewed and processed
 * in a timely manner.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Comment_Moderation_Queue extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-moderation-queue';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Moderation Queue';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if pending comments are being processed';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'publisher';

	/**
	 * Run the comment moderation queue diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if moderation issues detected, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues    = array();
		$warnings  = array();
		$stats     = array();

		// Get pending comments.
		$pending_comments = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_approved = '0'"
		);

		$stats['pending_comments'] = intval( $pending_comments );

		if ( $pending_comments > 50 ) {
			$issues[] = sprintf(
				/* translators: %d: number */
				__( '%d pending comments awaiting moderation', 'wpshadow' ),
				$pending_comments
			);
		} elseif ( $pending_comments > 20 ) {
			$warnings[] = sprintf(
				/* translators: %d: number */
				__( '%d pending comments in moderation queue', 'wpshadow' ),
				$pending_comments
			);
		}

		// Check age of oldest pending comment.
		if ( $pending_comments > 0 ) {
			$oldest_pending = $wpdb->get_row(
				"SELECT comment_date, comment_author, post_title FROM {$wpdb->comments} 
				 LEFT JOIN {$wpdb->posts} ON {$wpdb->comments}.comment_post_ID = {$wpdb->posts}.ID
				 WHERE comment_approved = '0' 
				 ORDER BY comment_date ASC LIMIT 1"
			);

			if ( $oldest_pending ) {
				$oldest_time = strtotime( $oldest_pending->comment_date );
				$days_pending = ( time() - $oldest_time ) / ( 24 * 3600 );
				$stats['oldest_pending_age_days'] = round( $days_pending, 1 );

				if ( $days_pending > 30 ) {
					$issues[] = sprintf(
						/* translators: %d: days */
						__( 'Oldest pending comment is %d days old', 'wpshadow' ),
						intval( $days_pending )
					);
				} elseif ( $days_pending > 7 ) {
					$warnings[] = sprintf(
						/* translators: %d: days */
						__( 'Oldest pending comment is %d days old - consider reviewing', 'wpshadow' ),
						intval( $days_pending )
					);
				}
			}
		}

		// Check spam comments.
		$spam_comments = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_approved = 'spam'"
		);

		$stats['spam_comments'] = intval( $spam_comments );

		if ( $spam_comments > 100 ) {
			$warnings[] = sprintf(
				/* translators: %d: number */
				__( '%d spam comments in queue - ensure spam filtering is working', 'wpshadow' ),
				$spam_comments
			);
		}

		// Check trash comments.
		$trash_comments = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_approved = 'trash'"
		);

		$stats['trash_comments'] = intval( $trash_comments );

		if ( $trash_comments > 50 ) {
			$warnings[] = sprintf(
				/* translators: %d: number */
				__( '%d comments in trash - consider emptying', 'wpshadow' ),
				$trash_comments
			);
		}

		// Check comment moderation settings.
		$comment_moderation = get_option( 'comment_moderation' );
		$moderation_notify = get_option( 'moderation_notify' );

		$stats['comment_moderation_enabled'] = boolval( $comment_moderation );
		$stats['moderation_notifications'] = boolval( $moderation_notify );

		if ( ! $comment_moderation ) {
			$warnings[] = __( 'Comment moderation is disabled - consider enabling it', 'wpshadow' );
		}

		if ( ! $moderation_notify ) {
			$warnings[] = __( 'Moderation notifications disabled - won\'t know about pending comments', 'wpshadow' );
		}

		// Check for Akismet.
		$akismet_key = get_option( 'akismet_api_key' );
		$has_akismet = ! empty( $akismet_key );
		$stats['akismet_enabled'] = $has_akismet;

		if ( ! $has_akismet ) {
			$warnings[] = __( 'Akismet not configured - consider using it for spam filtering', 'wpshadow' );
		}

		// Check comment approval rate.
		$total_comments = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_approved = '1'"
		);

		$approval_rate = $total_comments > 0 ? ( $total_comments / ( $total_comments + $spam_comments + $pending_comments ) ) * 100 : 0;
		$stats['approval_rate'] = round( $approval_rate, 1 );

		// Check if all comments are being auto-approved (no moderation).
		if ( $approval_rate > 95 && $pending_comments === 0 && $spam_comments === 0 ) {
			$warnings[] = __( 'All comments auto-approved - consider enabling moderation for better quality', 'wpshadow' );
		}

		// Check comment settings per post type.
		$open_posts = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} WHERE comment_status = 'open' AND post_status = 'publish'"
		);

		$stats['posts_accepting_comments'] = intval( $open_posts );

		// Check moderation queue processing time.
		$moderation_avg_wait = 0;
		$recently_approved   = $wpdb->get_results(
			"SELECT DATEDIFF(NOW(), comment_date) as wait_days
			 FROM {$wpdb->comments}
			 WHERE comment_approved = '0'
			 AND comment_date > DATE_SUB(NOW(), INTERVAL 7 DAY)
			 LIMIT 10"
		);

		if ( ! empty( $recently_approved ) ) {
			$wait_times = array_column( $recently_approved, 'wait_days' );
			$moderation_avg_wait = array_sum( $wait_times ) / count( $wait_times );
			$stats['avg_moderation_wait_days'] = round( $moderation_avg_wait, 2 );

			if ( $moderation_avg_wait > 7 ) {
				$warnings[] = sprintf(
					/* translators: %d: days */
					__( 'Average moderation wait time is %d days', 'wpshadow' ),
					intval( $moderation_avg_wait )
				);
			}
		}

		// If critical issues found.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Comment moderation queue has critical issues: ', 'wpshadow' ) . implode( ', ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/comment-moderation-queue',
				'context'      => array(
					'stats'    => $stats,
					'issues'   => $issues,
					'warnings' => $warnings,
				),
			);
		}

		// If only warnings.
		if ( ! empty( $warnings ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Comment moderation queue has recommendations: ', 'wpshadow' ) . implode( ', ', $warnings ),
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/comment-moderation-queue',
				'context'      => array(
					'stats'    => $stats,
					'warnings' => $warnings,
				),
			);
		}

		return null; // Comment moderation queue is healthy.
	}
}
