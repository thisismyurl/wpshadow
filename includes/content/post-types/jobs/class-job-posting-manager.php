<?php
/**
 * Job Posting Manager
 *
 * Manages job posting operations, notifications, and utilities.
 *
 * @package WPShadow
 * @subpackage Content
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\JobPostings;

use WPShadow\Core\Hook_Subscriber_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Job Posting Manager Class
 *
 * Handles job posting creation, publishing, and notifications.
 *
 * @since 0.6093.1200
 */
class Job_Posting_Manager extends Hook_Subscriber_Base {

	/**
	 * Get hooks to subscribe to.
	 *
	 * @since 0.6093.1200
	 * @return array Hook subscriptions.
	 */
	protected static function get_hooks(): array {
		return array(
			'publish_wps_job_posting' => array(
				array( 'send_publication_notification', 10, 1 ),
				array( 'update_featured_jobs', 10, 1 ),
			),
			'transition_post_status' => 'check_application_deadline',
			'admin_notices'          => 'show_expiring_jobs_notice',
		);
	}

	/**
	 * Send email notification when job is published.
	 *
	 * @since 0.6093.1200
	 * @param  int $post_id Post ID.
	 * @return void
	 */
	public static function send_publication_notification( $post_id ) {
		$post = get_post( $post_id );

		if ( ! $post || 'wps_job_posting' !== $post->post_type ) {
			return;
		}

		$admin_email = get_option( 'admin_email' );

		$subject = sprintf(
			/* translators: %s: job title */
			__( '[%s] New Job Posted: %s', 'wpshadow' ),
			get_bloginfo( 'name' ),
			get_the_title( $post_id )
		);

		$message = sprintf(
			"Job: %s\nAuthor: %s\nURL: %s\n\nEdit: %s",
			get_the_title( $post_id ),
			get_the_author_meta( 'display_name', $post->post_author ),
			get_permalink( $post_id ),
			admin_url( "post.php?post={$post_id}&action=edit" )
		);

		wp_mail( $admin_email, $subject, $message );
	}

	/**
	 * Update featured jobs cache when job is published.
	 *
	  * @since 0.6093.1200
	 * @param  int $post_id Post ID.
	 * @return void
	 */
	public static function update_featured_jobs( $post_id ) {
		$is_featured = get_post_meta( $post_id, '_wps_job_featured', true );

		if ( $is_featured ) {
			wp_cache_set( 'wpshadow_featured_jobs_updated', time() );
		}
	}

	/**
	 * Check and handle application deadline expiration.
	 *
	 * @since 0.6093.1200
	 * @param  string $new_status New post status.
	 * @param  string $old_status Old post status.
	 * @param  object $post       Post object.
	 * @return void
	 */
	public static function check_application_deadline( $new_status, $old_status, $post ) {
		if ( 'wps_job_posting' !== $post->post_type ) {
			return;
		}

		// If transitioning to publish
		if ( 'publish' === $new_status && 'publish' !== $old_status ) {
			$deadline = get_post_meta( $post->ID, '_wps_job_deadline_date', true );

			if ( $deadline ) {
				$deadline_ts = strtotime( $deadline );
				$current_ts  = time();

				if ( $deadline_ts < $current_ts ) {
					wp_update_post( array(
						'ID'          => $post->ID,
						'post_status' => 'draft',
					) );

					add_action( 'admin_notices', function () {
						printf(
							'<div class="notice notice-warning"><p>%s</p></div>',
							esc_html__( 'Job posting deadline has passed. Job has been saved as draft.', 'wpshadow' )
						);
					} );
				}
			}
		}
	}

	/**
	 * Show notice for jobs expiring soon.
	 *
	 * @since 0.6093.1200
	 */
	public static function show_expiring_jobs_notice() {
		// Only show on job post type
		$screen = get_current_screen();
		if ( ! $screen || 'edit-wps_job_posting' !== $screen->id ) {
			return;
		}

		// Get jobs expiring within 7 days
		$args = array(
			'post_type'      => 'wps_job_posting',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'meta_query'     => array(
				array(
					'key'     => '_wps_job_deadline_date',
					'value'   => array( date( 'Y-m-d' ), date( 'Y-m-d', strtotime( '+7 days' ) ) ),
					'compare' => 'BETWEEN',
					'type'    => 'DATE',
				),
			),
		);

		$expiring_jobs = get_posts( $args );

		if ( ! empty( $expiring_jobs ) ) {
			printf(
				'<div class="notice notice-warning is-dismissible"><p><strong>%s</strong> %s</p></div>',
				count( $expiring_jobs ),
				esc_html__( 'job postings are expiring within the next 7 days.', 'wpshadow' )
			);
		}
	}

	/**
	 * Get featured jobs.
	 *
	 * @since 0.6093.1200
	 * @param  int $limit Number of jobs to fetch.
	 * @return array Array of featured job IDs.
	 */
	public static function get_featured_jobs( $limit = 5 ) {
		$args = array(
			'post_type'      => 'wps_job_posting',
			'posts_per_page' => $limit,
			'post_status'    => 'publish',
			'meta_query'     => array(
				array(
					'key'   => '_wps_job_featured',
					'value' => '1',
				),
			),
			'orderby'        => 'date',
			'order'          => 'DESC',
		);

		return get_posts( $args );
	}

	/**
	 * Get jobs by location.
	 *
	 * @since 0.6093.1200
	 * @param  string $location Job location.
	 * @param  int    $limit    Number of jobs to fetch.
	 * @return array Array of job posts.
	 */
	public static function get_jobs_by_location( $location, $limit = 10 ) {
		$args = array(
			'post_type'      => 'wps_job_posting',
			'posts_per_page' => $limit,
			'post_status'    => 'publish',
			'meta_query'     => array(
				array(
					'key'   => '_wps_job_location',
					'value' => $location,
				),
			),
		);

		return get_posts( $args );
	}

	/**
	 * Export jobs as CSV.
	 *
	 * @since 0.6093.1200
	 * @param  array $job_ids Job post IDs to export.
	 * @return string CSV data.
	 */
	public static function export_jobs_as_csv( $job_ids ) {
		$csv = array(
			array(
				'ID',
				'Title',
				'Company',
				'Location',
				'Job Type',
				'Salary Min',
				'Salary Max',
				'Currency',
				'Deadline',
				'Status',
				'URL',
			),
		);

		foreach ( $job_ids as $job_id ) {
			$post = get_post( $job_id );

			if ( ! $post || 'wps_job_posting' !== $post->post_type ) {
				continue;
			}

			$job_types = wp_get_post_terms( $job_id, 'wps_job_type', array( 'fields' => 'names' ) );

			$csv[] = array(
				$job_id,
				get_the_title( $job_id ),
				get_post_meta( $job_id, '_wps_job_company_name', true ),
				get_post_meta( $job_id, '_wps_job_location', true ),
				implode( ', ', $job_types ),
				get_post_meta( $job_id, '_wps_job_salary_min', true ),
				get_post_meta( $job_id, '_wps_job_salary_max', true ),
				get_post_meta( $job_id, '_wps_job_salary_currency', true ) ?? 'USD',
				get_post_meta( $job_id, '_wps_job_deadline_date', true ),
				$post->post_status,
				get_permalink( $job_id ),
			);
		}

		$output = fopen( 'php://output', 'w' );
		foreach ( $csv as $row ) {
			fputcsv( $output, $row );
		}
		fclose( $output );

		return ob_get_clean();
	}
}
