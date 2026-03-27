<?php
/**
 * Job Application Tracker
 *
 * Manages job applications, applicant tracking, and application workflows.
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
 * Job Application Tracker Class
 *
 * Handles application submissions, tracking, and management.
 *
 * @since 1.6093.1200
 */
class Job_Application_Tracker extends Hook_Subscriber_Base {

	const POST_TYPE = 'wps_job_application';

	/**
	 * Get hooks to subscribe to.
	 *
	 * @since 1.6093.1200
	 * @return array Hook subscriptions.
	 */
	protected static function get_hooks(): array {
		return array(
			'init' => 'register_post_type',
			'wp_ajax_submit_job_application' => 'handle_application_submission',
		);
	}

	/**
	 * Register job application post type.
	 *
	 * @since 1.6093.1200
	 */
	public static function register_post_type() {
		register_post_type(
			self::POST_TYPE,
			array(
				'labels'              => array(
					'name'          => __( 'Job Applications', 'wpshadow' ),
					'singular_name' => __( 'Job Application', 'wpshadow' ),
				),
				'public'              => false,
				'show_ui'             => false,
				'show_in_menu'        => false,
				'supports'            => array( 'title' ),
				'capability_type'     => 'post',
				'map_meta_cap'        => true,
			)
		);
	}

	/**
	 * Handle job application submission via AJAX.
	 *
	 * @since 1.6093.1200
	 */
	public static function handle_application_submission() {
		check_ajax_referer( 'wpshadow_job_application_nonce' );

		if ( ! isset( $_POST['job_id'] ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid job ID', 'wpshadow' ) ) );
		}

		$job_id = absint( $_POST['job_id'] );
		$post = get_post( $job_id );

		if ( ! $post || 'wps_job_posting' !== $post->post_type ) {
			wp_send_json_error( array( 'message' => __( 'Invalid job posting', 'wpshadow' ) ) );
		}

		$applicant_name = sanitize_text_field( $_POST['applicant_name'] ?? '' );
		$applicant_email = sanitize_email( $_POST['applicant_email'] ?? '' );
		$applicant_phone = sanitize_text_field( $_POST['applicant_phone'] ?? '' );
		$cover_letter = wp_kses_post( $_POST['cover_letter'] ?? '' );

		// Validate required fields
		if ( empty( $applicant_name ) || empty( $applicant_email ) ) {
			wp_send_json_error( array( 'message' => __( 'Name and email are required', 'wpshadow' ) ) );
		}

		// Handle resume upload
		$resume_url = '';
		if ( ! empty( $_FILES['resume'] ) ) {
			$upload = wp_handle_upload( $_FILES['resume'], array( 'test_form' => false ) );
			if ( isset( $upload['url'] ) ) {
				$resume_url = $upload['url'];
			}
		}

		$application_id = wp_insert_post(
			array(
				'post_type'   => self::POST_TYPE,
				'post_status' => 'publish',
				'post_title'  => sprintf(
					/* translators: %s: applicant name */
					__( 'Application from %s', 'wpshadow' ),
					$applicant_name
				),
				'post_parent' => $job_id,
			)
		);

		if ( is_wp_error( $application_id ) || ! $application_id ) {
			wp_send_json_error( array( 'message' => __( 'Failed to submit application', 'wpshadow' ) ) );
		}

		update_post_meta( $application_id, 'wps_application_job_id', $job_id );
		update_post_meta( $application_id, 'wps_application_applicant_name', $applicant_name );
		update_post_meta( $application_id, 'wps_application_applicant_email', $applicant_email );
		update_post_meta( $application_id, 'wps_application_applicant_phone', $applicant_phone );
		update_post_meta( $application_id, 'wps_application_resume_url', $resume_url );
		update_post_meta( $application_id, 'wps_application_cover_letter', $cover_letter );
		update_post_meta( $application_id, 'wps_application_status', 'new' );
		update_post_meta( $application_id, 'wps_application_rating', 0 );
		update_post_meta( $application_id, 'wps_application_notes', '' );
		update_post_meta( $application_id, 'wps_application_applied_at', current_time( 'mysql' ) );
		update_post_meta( $application_id, 'wps_application_updated_at', current_time( 'mysql' ) );

		// Send confirmation email to applicant
		self::send_applicant_confirmation_email( $applicant_email, $applicant_name, $job_id );

		// Send notification to job poster
		self::send_application_notification_to_poster( $job_id, $applicant_name, $applicant_email );

		wp_send_json_success( array( 'message' => __( 'Application submitted successfully!', 'wpshadow' ) ) );
	}

	/**
	 * Get applications for a job.
	 *
	 * @since 1.6093.1200
	 * @param  int   $job_id Job post ID.
	 * @param  array $args   Query arguments.
	 * @return array Array of applications.
	 */
	public static function get_job_applications( $job_id, $args = array() ) {
		$defaults = array(
			'status'    => '',
			'limit'     => 50,
			'offset'    => 0,
			'orderby'   => 'applied_at',
			'order'     => 'DESC',
		);

		$args = wp_parse_args( $args, $defaults );

		$meta_query = array(
			array(
				'key'   => 'wps_application_job_id',
				'value' => $job_id,
				'type'  => 'NUMERIC',
			),
		);

		if ( ! empty( $args['status'] ) ) {
			$meta_query[] = array(
				'key'   => 'wps_application_status',
				'value' => sanitize_text_field( $args['status'] ),
			);
		}

		$query = new \WP_Query(
			array(
				'post_type'      => self::POST_TYPE,
				'post_status'    => 'publish',
				'posts_per_page' => absint( $args['limit'] ),
				'offset'         => absint( $args['offset'] ),
				'orderby'        => 'date',
				'order'          => strtoupper( (string) $args['order'] ) === 'ASC' ? 'ASC' : 'DESC',
				'meta_query'     => $meta_query,
			)
		);

		$applications = array();
		foreach ( $query->posts as $post ) {
			$applications[] = self::map_post_to_application( $post->ID );
		}

		return array_filter( $applications );
	}

	/**
	 * Get total application count.
	 *
	 * @since 1.6093.1200
	 * @return int Total applications.
	 */
	public static function get_total_applications() {
		$count = wp_count_posts( self::POST_TYPE );
		if ( ! $count ) {
			return 0;
		}

		return (int) ( $count->publish ?? 0 );
	}

	/**
	 * Get application count by status.
	 *
	 * @since 1.6093.1200
	 * @param  string $status Application status.
	 * @return int Count for status.
	 */
	public static function get_applications_count_by_status( $status ) {
		$query = new \WP_Query(
			array(
				'post_type'      => self::POST_TYPE,
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'meta_query'     => array(
					array(
						'key'   => 'wps_application_status',
						'value' => sanitize_text_field( $status ),
					),
				),
			)
		);

		return (int) $query->found_posts;
	}

	/**
	 * Get recent applications.
	 *
	 * @since 1.6093.1200
	 * @param  int $limit Number of recent applications to retrieve.
	 * @return array Recent applications.
	 */
	public static function get_recent_applications( $limit = 5 ) {
		$query = new \WP_Query(
			array(
				'post_type'      => self::POST_TYPE,
				'post_status'    => 'publish',
				'posts_per_page' => absint( $limit ),
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		$recent = array();
		foreach ( $query->posts as $post ) {
			$mapped = self::map_post_to_application( $post->ID );
			if ( null !== $mapped ) {
				$recent[] = $mapped;
			}
		}

		if ( empty( $recent ) ) {
			return array();
		}

		return $recent;
	}

	/**
	 * Update application status.
	 *
	 * @since 1.6093.1200
	 * @param  int    $application_id Application ID.
	 * @param  string $status         New status.
	 * @param  string $notes          Optional notes.
	 * @return bool Success.
	 */
	public static function update_application_status( $application_id, $status, $notes = '' ) {
		$valid_statuses = array( 'new', 'reviewing', 'shortlisted', 'rejected', 'interviewed', 'offered', 'hired' );

		if ( ! in_array( $status, $valid_statuses, true ) ) {
			return false;
		}

		$application_id = absint( $application_id );
		if ( $application_id <= 0 || self::POST_TYPE !== get_post_type( $application_id ) ) {
			return false;
		}

		update_post_meta( $application_id, 'wps_application_status', $status );
		update_post_meta( $application_id, 'wps_application_notes', sanitize_textarea_field( $notes ) );
		update_post_meta( $application_id, 'wps_application_updated_at', current_time( 'mysql' ) );

		return true;
	}

	/**
	 * Send confirmation email to applicant.
	 *
	 * @since 1.6093.1200
	 * @param  string $email  Applicant email.
	 * @param  string $name   Applicant name.
	 * @param  int    $job_id Job post ID.
	 * @return void
	 */
	private static function send_applicant_confirmation_email( $email, $name, $job_id ) {
		$job_title = get_the_title( $job_id );
		$subject = sprintf(
			/* translators: %s: job title */
			__( 'Application Received: %s', 'wpshadow' ),
			$job_title
		);

		$message = sprintf(
			__( "Hi %s,\n\nThank you for applying for %s. We've received your application and will review it shortly.\n\nBest regards,\n%s", 'wpshadow' ),
			$name,
			$job_title,
			get_bloginfo( 'name' )
		);

		wp_mail( $email, $subject, $message );
	}

	/**
	 * Send application notification to job poster.
	 *
	 * @since 1.6093.1200
	 * @param  int    $job_id Job post ID.
	 * @param  string $name   Applicant name.
	 * @param  string $email  Applicant email.
	 * @return void
	 */
	private static function send_application_notification_to_poster( $job_id, $name, $email ) {
		$post = get_post( $job_id );
		$poster_email = get_the_author_meta( 'user_email', $post->post_author );

		$subject = sprintf(
			/* translators: %s: applicant name */
			__( 'New Application: %s', 'wpshadow' ),
			$name
		);

		$message = sprintf(
			__( "New application received for %s\n\nApplicant: %s\nEmail: %s\n\nReview: %s", 'wpshadow' ),
			get_the_title( $job_id ),
			$name,
			$email,
			admin_url( "admin.php?page=job-applications&job_id=$job_id" )
		);

		wp_mail( $poster_email, $subject, $message );
	}

	/**
	 * Get application statistics for a job.
	 *
	 * @since 1.6093.1200
	 * @param  int $job_id Job post ID.
	 * @return array Statistics array.
	 */
	public static function get_application_stats( $job_id ) {
		$applications = self::get_job_applications(
			$job_id,
			array(
				'limit'  => 9999,
				'offset' => 0,
			)
		);

		$total    = count( $applications );
		$statuses = array();

		foreach ( $applications as $application ) {
			$status = (string) ( $application->status ?? 'new' );
			if ( ! isset( $statuses[ $status ] ) ) {
				$statuses[ $status ] = (object) array(
					'status' => $status,
					'count'  => 0,
				);
			}

			$statuses[ $status ]->count++;
		}

		return array(
			'total'      => $total,
			'by_status'  => $statuses,
		);
	}

	/**
	 * Convert application post to legacy response shape.
	 *
	 * @since 1.6093.1200
	 * @param  int $post_id Application post ID.
	 * @return \stdClass|null Application object.
	 */
	private static function map_post_to_application( $post_id ) {
		$post_id = absint( $post_id );
		if ( $post_id <= 0 || self::POST_TYPE !== get_post_type( $post_id ) ) {
			return null;
		}

		$job_id = (int) get_post_meta( $post_id, 'wps_application_job_id', true );

		return (object) array(
			'id'                   => $post_id,
			'job_id'               => $job_id,
			'applicant_name'       => (string) get_post_meta( $post_id, 'wps_application_applicant_name', true ),
			'applicant_email'      => (string) get_post_meta( $post_id, 'wps_application_applicant_email', true ),
			'applicant_phone'      => (string) get_post_meta( $post_id, 'wps_application_applicant_phone', true ),
			'applicant_resume_url' => (string) get_post_meta( $post_id, 'wps_application_resume_url', true ),
			'cover_letter'         => (string) get_post_meta( $post_id, 'wps_application_cover_letter', true ),
			'status'               => (string) get_post_meta( $post_id, 'wps_application_status', true ),
			'rating'               => (int) get_post_meta( $post_id, 'wps_application_rating', true ),
			'notes'                => (string) get_post_meta( $post_id, 'wps_application_notes', true ),
			'applied_at'           => (string) get_post_meta( $post_id, 'wps_application_applied_at', true ),
			'updated_at'           => (string) get_post_meta( $post_id, 'wps_application_updated_at', true ),
			'post_title'           => get_the_title( $job_id ),
		);
	}
}
