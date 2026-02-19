<?php
/**
 * Job Application Tracker
 *
 * Manages job applications, applicant tracking, and application workflows.
 *
 * @package WPShadow
 * @subpackage Content
 * @since      1.6050.0000
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
 * @since 1.6050.0000
 */
class Job_Application_Tracker extends Hook_Subscriber_Base {

	const TABLE_APPLICATIONS = 'wpshadow_job_applications';

	/**
	 * Get hooks to subscribe to.
	 *
	 * @since  1.6050.0000
	 * @return array Hook subscriptions.
	 */
	protected static function get_hooks(): array {
		return array(
			'plugins_loaded' => 'create_applications_table',
			'wp_ajax_submit_job_application' => 'handle_application_submission',
		);
	}

	/**
	 * Create applications database table.
	 *
	 * @since 1.6050.0000
	 */
	public static function create_applications_table() {
		global $wpdb;

		$table = $wpdb->prefix . self::TABLE_APPLICATIONS;
		$charset_collate = $wpdb->get_charset_collate();

		if ( $wpdb->get_var( "SHOW TABLES LIKE '$table'" ) !== $table ) {
			$sql = "CREATE TABLE $table (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				job_id bigint(20) unsigned NOT NULL,
				applicant_name varchar(255) NOT NULL,
				applicant_email varchar(255) NOT NULL,
				applicant_phone varchar(20),
				applicant_resume_url longtext,
				cover_letter longtext,
				status varchar(50) DEFAULT 'new',
				rating int(2) DEFAULT 0,
				notes longtext,
				applied_at datetime DEFAULT CURRENT_TIMESTAMP,
				updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				PRIMARY KEY (id),
				KEY job_id (job_id),
				KEY applicant_email (applicant_email),
				KEY status (status),
				KEY applied_at (applied_at)
			) $charset_collate;";

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );
		}
	}

	/**
	 * Handle job application submission via AJAX.
	 *
	 * @since 1.6050.0000
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

		// Insert application
		global $wpdb;
		$result = $wpdb->insert(
			$wpdb->prefix . self::TABLE_APPLICATIONS,
			array(
				'job_id'           => $job_id,
				'applicant_name'   => $applicant_name,
				'applicant_email'  => $applicant_email,
				'applicant_phone'  => $applicant_phone,
				'applicant_resume_url' => $resume_url,
				'cover_letter'     => $cover_letter,
				'status'           => 'new',
			),
			array( '%d', '%s', '%s', '%s', '%s', '%s', '%s' )
		);

		if ( ! $result ) {
			wp_send_json_error( array( 'message' => __( 'Failed to submit application', 'wpshadow' ) ) );
		}

		// Send confirmation email to applicant
		self::send_applicant_confirmation_email( $applicant_email, $applicant_name, $job_id );

		// Send notification to job poster
		self::send_application_notification_to_poster( $job_id, $applicant_name, $applicant_email );

		wp_send_json_success( array( 'message' => __( 'Application submitted successfully!', 'wpshadow' ) ) );
	}

	/**
	 * Get applications for a job.
	 *
	 * @since  1.6050.0000
	 * @param  int   $job_id Job post ID.
	 * @param  array $args   Query arguments.
	 * @return array Array of applications.
	 */
	public static function get_job_applications( $job_id, $args = array() ) {
		global $wpdb;

		$defaults = array(
			'status'    => '',
			'limit'     => 50,
			'offset'    => 0,
			'orderby'   => 'applied_at',
			'order'     => 'DESC',
		);

		$args = wp_parse_args( $args, $defaults );

		$table = $wpdb->prefix . self::TABLE_APPLICATIONS;
		$sql = "SELECT * FROM $table WHERE job_id = %d";
		$params = array( $job_id );

		if ( ! empty( $args['status'] ) ) {
			$sql .= " AND status = %s";
			$params[] = sanitize_text_field( $args['status'] );
		}

		$sql .= " ORDER BY " . sanitize_text_field( $args['orderby'] ) . " " . strtoupper( $args['order'] );
		$sql .= " LIMIT %d OFFSET %d";
		$params[] = absint( $args['limit'] );
		$params[] = absint( $args['offset'] );

		return $wpdb->get_results( $wpdb->prepare( $sql, $params ) );
	}

	/**
	 * Get total application count.
	 *
	 * @since  1.6050.0000
	 * @return int Total applications.
	 */
	public static function get_total_applications() {
		global $wpdb;

		$table = $wpdb->prefix . self::TABLE_APPLICATIONS;
		$total = $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );

		return (int) $total;
	}

	/**
	 * Get application count by status.
	 *
	 * @since  1.6050.0000
	 * @param  string $status Application status.
	 * @return int Count for status.
	 */
	public static function get_applications_count_by_status( $status ) {
		global $wpdb;

		$table = $wpdb->prefix . self::TABLE_APPLICATIONS;
		$count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table} WHERE status = %s",
				sanitize_text_field( $status )
			)
		);

		return (int) $count;
	}

	/**
	 * Get recent applications.
	 *
	 * @since  1.6050.0000
	 * @param  int $limit Number of recent applications to retrieve.
	 * @return array Recent applications.
	 */
	public static function get_recent_applications( $limit = 5 ) {
		global $wpdb;

		$table  = $wpdb->prefix . self::TABLE_APPLICATIONS;
		$recent = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} ORDER BY applied_at DESC LIMIT %d",
				absint( $limit )
			)
		);

		if ( empty( $recent ) ) {
			return array();
		}

		foreach ( $recent as $application ) {
			$application->post_title = get_the_title( (int) $application->job_id );
		}

		return $recent;
	}

	/**
	 * Update application status.
	 *
	 * @since  1.6050.0000
	 * @param  int    $application_id Application ID.
	 * @param  string $status         New status.
	 * @param  string $notes          Optional notes.
	 * @return bool Success.
	 */
	public static function update_application_status( $application_id, $status, $notes = '' ) {
		global $wpdb;

		$valid_statuses = array( 'new', 'reviewing', 'shortlisted', 'rejected', 'interviewed', 'offered', 'hired' );

		if ( ! in_array( $status, $valid_statuses, true ) ) {
			return false;
		}

		$update = $wpdb->update(
			$wpdb->prefix . self::TABLE_APPLICATIONS,
			array(
				'status' => $status,
				'notes'  => $notes,
			),
			array( 'id' => $application_id ),
			array( '%s', '%s' ),
			array( '%d' )
		);

		return false !== $update;
	}

	/**
	 * Send confirmation email to applicant.
	 *
	 * @since  1.6050.0000
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
	 * @since  1.6050.0000
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
	 * @since  1.6050.0000
	 * @param  int $job_id Job post ID.
	 * @return array Statistics array.
	 */
	public static function get_application_stats( $job_id ) {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLE_APPLICATIONS;

		$total = $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM $table WHERE job_id = %d",
			$job_id
		) );

		$statuses = $wpdb->get_results( $wpdb->prepare(
			"SELECT status, COUNT(*) as count FROM $table WHERE job_id = %d GROUP BY status",
			$job_id
		), OBJECT_K );

		return array(
			'total'      => $total,
			'by_status'  => $statuses,
		);
	}
}
