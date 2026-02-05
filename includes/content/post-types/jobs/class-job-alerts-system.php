<?php
/**
 * Job Alerts System
 *
 * Manages job alerts and email subscriptions for users.
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
 * Job Alerts System Class
 *
 * @since 1.6050.0000
 */
class Job_Alerts_System extends Hook_Subscriber_Base {

	const TABLE_ALERTS = 'wpshadow_job_alerts';

	/**
	 * Get hooks to subscribe to.
	 *
	 * @since  1.6050.0000
	 * @return array Hook subscriptions.
	 */
	protected static function get_hooks(): array {
		return array(
			'plugins_loaded'               => 'create_alerts_table',
			'wp_ajax_subscribe_job_alert'  => 'handle_alert_subscription',
			'publish_wps_job_posting'      => 'send_alerts_for_new_job',
		);
	}

	/**
	 * Create job alerts database table.
	 *
	 * @since 1.6050.0000
	 */
	public static function create_alerts_table() {
		global $wpdb;

		$table = $wpdb->prefix . self::TABLE_ALERTS;
		$charset_collate = $wpdb->get_charset_collate();

		if ( $wpdb->get_var( "SHOW TABLES LIKE '$table'" ) !== $table ) {
			$sql = "CREATE TABLE $table (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				email varchar(255) NOT NULL,
				job_category bigint(20),
				job_type bigint(20),
				location varchar(255),
				keywords varchar(500),
				status varchar(50) DEFAULT 'active',
				frequency varchar(50) DEFAULT 'weekly',
				created_at datetime DEFAULT CURRENT_TIMESTAMP,
				updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				PRIMARY KEY (id),
				KEY email (email),
				KEY status (status),
				KEY job_category (job_category)
			) $charset_collate;";

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );
		}
	}

	/**
	 * Handle job alert subscription via AJAX.
	 *
	 * @since 1.6050.0000
	 */
	public static function handle_alert_subscription() {
		check_ajax_referer( 'wpshadow_job_alert_nonce' );

		$email = sanitize_email( $_POST['email'] ?? '' );
		$category = absint( $_POST['category'] ?? 0 );
		$job_type = absint( $_POST['job_type'] ?? 0 );
		$location = sanitize_text_field( $_POST['location'] ?? '' );
		$keywords = sanitize_text_field( $_POST['keywords'] ?? '' );
		$frequency = sanitize_text_field( $_POST['frequency'] ?? 'weekly' );

		if ( ! is_email( $email ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid email address', 'wpshadow' ) ) );
		}

		global $wpdb;

		// Check if alert already exists
		$existing = $wpdb->get_row( $wpdb->prepare(
			"SELECT id FROM {$wpdb->prefix}" . self::TABLE_ALERTS . " WHERE email = %s AND job_category = %d AND job_type = %d",
			$email,
			$category,
			$job_type
		) );

		if ( $existing ) {
			wp_send_json_error( array( 'message' => __( 'You are already subscribed to this alert', 'wpshadow' ) ) );
		}

		$result = $wpdb->insert(
			$wpdb->prefix . self::TABLE_ALERTS,
			array(
				'email'        => $email,
				'job_category' => $category > 0 ? $category : null,
				'job_type'     => $job_type > 0 ? $job_type : null,
				'location'     => $location,
				'keywords'     => $keywords,
				'frequency'    => $frequency,
				'status'       => 'active',
			),
			array( '%s', '%d', '%d', '%s', '%s', '%s', '%s' )
		);

		if ( ! $result ) {
			wp_send_json_error( array( 'message' => __( 'Failed to create alert', 'wpshadow' ) ) );
		}

		// Send confirmation email
		self::send_alert_confirmation_email( $email );

		wp_send_json_success( array( 'message' => __( 'Alert created successfully! Check your email to confirm.', 'wpshadow' ) ) );
	}

	/**
	 * Send alerts when a new job is published.
	 *
	 * @since  1.6050.0000
	 * @param  int $post_id Job post ID.
	 * @return void
	 */
	public static function send_alerts_for_new_job( $post_id ) {
		$post = get_post( $post_id );

		if ( 'wps_job_posting' !== $post->post_type ) {
			return;
		}

		global $wpdb;

		// Get job details
		$job_categories = wp_get_post_terms( $post_id, 'wps_job_category', array( 'fields' => 'ids' ) );
		$job_types = wp_get_post_terms( $post_id, 'wps_job_type', array( 'fields' => 'ids' ) );
		$job_location = get_post_meta( $post_id, 'wps_job_location', true );
		$job_keywords = wp_strip_all_tags( $post->post_content );

		// Find matching alerts
		$sql = "SELECT DISTINCT email FROM {$wpdb->prefix}" . self::TABLE_ALERTS . " WHERE status = 'active' AND (";
		$conditions = array();
		$params = array();

		if ( ! empty( $job_categories ) ) {
			$placeholders = implode( ',', array_fill( 0, count( $job_categories ), '%d' ) );
			$conditions[] = "job_category IN ($placeholders)";
			$params = array_merge( $params, $job_categories );
		}

		if ( ! empty( $job_types ) ) {
			$placeholders = implode( ',', array_fill( 0, count( $job_types ), '%d' ) );
			$conditions[] = "job_type IN ($placeholders)";
			$params = array_merge( $params, $job_types );
		}

		if ( ! empty( $job_location ) ) {
			$conditions[] = "location LIKE %s";
			$params[] = '%' . $job_location . '%';
		}

		if ( empty( $conditions ) ) {
			return;
		}

		$sql .= implode( ' OR ', $conditions ) . ')';

		$alerts = $wpdb->get_results( $wpdb->prepare( $sql, $params ) );

		// Send emails to matching subscribers
		foreach ( $alerts as $alert ) {
			self::send_job_alert_email( $alert->email, $post );
		}
	}

	/**
	 * Send alert confirmation email.
	 *
	 * @since  1.6050.0000
	 * @param  string $email Subscriber email.
	 * @return void
	 */
	private static function send_alert_confirmation_email( $email ) {
		$subject = sprintf(
			/* translators: %s: site name */
			__( 'Confirm Your Job Alert Subscription - %s', 'wpshadow' ),
			get_bloginfo( 'name' )
		);

		$message = sprintf(
			__( "Thank you for subscribing to job alerts!\n\nPlease confirm your subscription by clicking the link below:\n%s\n\nBest regards,\n%s", 'wpshadow' ),
			add_query_arg( 'action', 'confirm_job_alert', home_url() ),
			get_bloginfo( 'name' )
		);

		wp_mail( $email, $subject, $message );
	}

	/**
	 * Send new job alert email.
	 *
	 * @since  1.6050.0000
	 * @param  string   $email Subscriber email.
	 * @param  \WP_Post $job   Job post object.
	 * @return void
	 */
	private static function send_job_alert_email( $email, $job ) {
		$subject = sprintf(
			/* translators: %s: job title */
			__( 'New Job Alert: %s', 'wpshadow' ),
			$job->post_title
		);

		$job_url = get_permalink( $job->ID );
		$location = get_post_meta( $job->ID, 'wps_job_location', true );

		$message = sprintf(
			__( "Hi,\n\nA new job matching your alert criteria has been posted:\n\n%s\nLocation: %s\n\nView the full job posting: %s\n\nBest regards,\n%s", 'wpshadow' ),
			$job->post_title,
			$location,
			$job_url,
			get_bloginfo( 'name' )
		);

		wp_mail( $email, $subject, $message );
	}

	/**
	 * Get active alerts for an email.
	 *
	 * @since  1.6050.0000
	 * @param  string $email Email address.
	 * @return array Array of alerts.
	 */
	public static function get_subscriber_alerts( $email ) {
		global $wpdb;

		return $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}" . self::TABLE_ALERTS . " WHERE email = %s AND status = 'active'",
			$email
		) );
	}
}
