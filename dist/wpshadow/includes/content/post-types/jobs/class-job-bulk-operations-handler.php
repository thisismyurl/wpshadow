<?php
/**
 * Job Board Bulk Operations Handler
 *
 * Handles bulk operations for job postings like publish, archive, duplicate, etc.
 *
 * @package WPShadow
 * @subpackage Content
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\JobPostings;

use WPShadow\Core\Hook_Subscriber_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Job Board Bulk Operations Handler Class
 *
 * @since 1.6093.1200
 */
class Job_Bulk_Operations_Handler extends Hook_Subscriber_Base {

	/**
	 * Get hooks to subscribe to.
	 *
	 * @since 1.6093.1200
	 * @return array Hook subscriptions.
	 */
	protected static function get_hooks(): array {
		return array(
			'bulk_actions-edit-wps_job_posting' => 'add_bulk_actions',
			'handle_bulk_actions-edit-wps_job_posting' => 'handle_bulk_actions',
		);
	}

	/**
	 * Add bulk action options.
	 *
	 * @since 1.6093.1200
	 * @param  array $actions Existing bulk actions.
	 * @return array Modified bulk actions.
	 */
	public static function add_bulk_actions( $actions ) {
		$actions['archive_jobs'] = __( 'Archive Jobs', 'wpshadow' );
		$actions['duplicate_jobs'] = __( 'Duplicate Jobs', 'wpshadow' );
		$actions['close_jobs'] = __( 'Close Jobs', 'wpshadow' );
		$actions['extend_deadline'] = __( 'Extend Deadline (7 days)', 'wpshadow' );
		$actions['send_email_notification'] = __( 'Send Notification Email', 'wpshadow' );

		return $actions;
	}

	/**
	 * Handle bulk action execution.
	 *
	 * @since 1.6093.1200
	 * @param  string $sendback The redirect URL.
	 * @param  string $doaction The action being performed.
	 * @param  array  $post_ids Array of post IDs.
	 * @return string Redirect URL.
	 */
	public static function handle_bulk_actions( $sendback, $doaction, $post_ids ) {
		switch ( $doaction ) {
			case 'archive_jobs':
				self::archive_jobs( $post_ids );
				break;

			case 'duplicate_jobs':
				self::duplicate_jobs( $post_ids );
				break;

			case 'close_jobs':
				self::close_jobs( $post_ids );
				break;

			case 'extend_deadline':
				self::extend_deadlines( $post_ids );
				break;

			case 'send_email_notification':
				self::send_notifications( $post_ids );
				break;
		}

		return $sendback;
	}

	/**
	 * Archive multiple jobs.
	 *
	 * @since 1.6093.1200
	 * @param  array $post_ids Post IDs to archive.
	 * @return int Count of archived posts.
	 */
	private static function archive_jobs( $post_ids ) {
		$count = 0;

		foreach ( (array) $post_ids as $post_id ) {
			$result = wp_update_post( array(
				'ID'          => $post_id,
				'post_status' => 'draft',
			) );

			if ( ! is_wp_error( $result ) ) {
				$count++;
			}
		}

		return $count;
	}

	/**
	 * Duplicate multiple jobs.
	 *
	 * @since 1.6093.1200
	 * @param  array $post_ids Post IDs to duplicate.
	 * @return int Count of duplicated posts.
	 */
	private static function duplicate_jobs( $post_ids ) {
		$count = 0;

		foreach ( (array) $post_ids as $post_id ) {
			$post = get_post( $post_id );

			if ( ! $post ) {
				continue;
			}

			$new_post = array(
				'post_author'    => $post->post_author,
				'post_content'   => $post->post_content,
				'post_excerpt'   => $post->post_excerpt,
				'post_title'     => sprintf(
					/* translators: %s: original post title */
					__( '%s (Copy)', 'wpshadow' ),
					$post->post_title
				),
				'post_status'    => 'draft',
				'post_type'      => $post->post_type,
				'comment_status' => $post->comment_status,
				'ping_status'    => $post->ping_status,
			);

			$new_id = wp_insert_post( $new_post );

			if ( ! is_wp_error( $new_id ) ) {
				// Copy meta fields
				$meta = get_post_meta( $post_id );
				foreach ( $meta as $key => $values ) {
					foreach ( (array) $values as $value ) {
						add_post_meta( $new_id, $key, $value );
					}
				}

				// Copy taxonomies
				$taxonomies = get_object_taxonomies( 'wps_job_posting' );
				foreach ( $taxonomies as $taxonomy ) {
					$terms = wp_get_post_terms( $post_id, $taxonomy, array( 'fields' => 'ids' ) );
					if ( ! empty( $terms ) ) {
						wp_set_post_terms( $new_id, $terms, $taxonomy );
					}
				}

				$count++;
			}
		}

		return $count;
	}

	/**
	 * Close multiple jobs.
	 *
	 * @since 1.6093.1200
	 * @param  array $post_ids Post IDs to close.
	 * @return int Count of closed posts.
	 */
	private static function close_jobs( $post_ids ) {
		$count = 0;

		foreach ( (array) $post_ids as $post_id ) {
			$result = update_post_meta( $post_id, 'wps_job_status', 'closed' );

			if ( $result !== false ) {
				$count++;
			}
		}

		return $count;
	}

	/**
	 * Extend deadlines for multiple jobs.
	 *
	 * @since 1.6093.1200
	 * @param  array $post_ids Post IDs to extend.
	 * @return int Count of extended posts.
	 */
	private static function extend_deadlines( $post_ids ) {
		$count = 0;
		$extension_days = 7;

		foreach ( (array) $post_ids as $post_id ) {
			$current_deadline = get_post_meta( $post_id, 'wps_job_deadline', true );

			if ( $current_deadline ) {
				$new_deadline = strtotime( "+{$extension_days} days", strtotime( $current_deadline ) );
				$new_deadline_formatted = date( 'Y-m-d', $new_deadline );
				update_post_meta( $post_id, 'wps_job_deadline', $new_deadline_formatted );
				$count++;
			}
		}

		return $count;
	}

	/**
	 * Send notification emails for jobs.
	 *
	 * @since 1.6093.1200
	 * @param  array $post_ids Post IDs to notify about.
	 * @return int Count of notifications sent.
	 */
	private static function send_notifications( $post_ids ) {
		$count = 0;
		$admin_email = get_option( 'admin_email' );

		foreach ( (array) $post_ids as $post_id ) {
			$post = get_post( $post_id );
			$author_email = get_the_author_meta( 'user_email', $post->post_author );

			$subject = sprintf(
				/* translators: %s: job title */
				__( 'Job Posting Update: %s', 'wpshadow' ),
				$post->post_title
			);

			$message = sprintf(
				__( 'Your job posting "%s" is still active. Review applications and consider extending the deadline if needed.', 'wpshadow' ),
				$post->post_title
			);

			if ( wp_mail( $author_email, $subject, $message ) ) {
				$count++;
			}
		}

		return $count;
	}
}
