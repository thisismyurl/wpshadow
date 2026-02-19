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

	const USER_META_KEY = 'wpshadow_job_alerts';

	/**
	 * Get hooks to subscribe to.
	 *
	 * @since  1.6050.0000
	 * @return array Hook subscriptions.
	 */
	protected static function get_hooks(): array {
		return array(
			'wp_ajax_subscribe_job_alert'  => 'handle_alert_subscription',
			'publish_wps_job_posting'      => 'send_alerts_for_new_job',
		);
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

		$user = get_user_by( 'email', $email );
		if ( ! $user ) {
			wp_send_json_error( array( 'message' => __( 'Please use a registered account email for job alerts.', 'wpshadow' ) ) );
		}

		$alerts = get_user_meta( (int) $user->ID, self::USER_META_KEY, true );
		if ( ! is_array( $alerts ) ) {
			$alerts = array();
		}

		foreach ( $alerts as $alert ) {
			if ( ! is_array( $alert ) ) {
				continue;
			}

			if ( ( $alert['status'] ?? 'active' ) !== 'active' ) {
				continue;
			}

			if ( (int) ( $alert['job_category'] ?? 0 ) === $category && (int) ( $alert['job_type'] ?? 0 ) === $job_type ) {
				wp_send_json_error( array( 'message' => __( 'You are already subscribed to this alert', 'wpshadow' ) ) );
			}
		}

		$alerts[] = array(
			'id'           => uniqid( 'alert_', true ),
			'email'        => $email,
			'job_category' => $category > 0 ? $category : 0,
			'job_type'     => $job_type > 0 ? $job_type : 0,
			'location'     => $location,
			'keywords'     => $keywords,
			'frequency'    => $frequency,
			'status'       => 'active',
			'created_at'   => current_time( 'mysql' ),
			'updated_at'   => current_time( 'mysql' ),
		);

		if ( ! update_user_meta( (int) $user->ID, self::USER_META_KEY, $alerts ) ) {
			wp_send_json_error( array( 'message' => __( 'You are already subscribed to this alert', 'wpshadow' ) ) );
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

		// Get job details
		$job_categories = wp_get_post_terms( $post_id, 'wps_job_category', array( 'fields' => 'ids' ) );
		$job_types = wp_get_post_terms( $post_id, 'wps_job_type', array( 'fields' => 'ids' ) );
		$job_location = get_post_meta( $post_id, 'wps_job_location', true );
		$job_keywords = wp_strip_all_tags( $post->post_title . ' ' . $post->post_content );

		$user_ids = get_users(
			array(
				'fields'   => 'ids',
				'meta_key' => self::USER_META_KEY,
			)
		);

		if ( empty( $user_ids ) ) {
			return;
		}

		$sent_to = array();
		foreach ( $user_ids as $user_id ) {
			$alerts = get_user_meta( (int) $user_id, self::USER_META_KEY, true );
			if ( ! is_array( $alerts ) || empty( $alerts ) ) {
				continue;
			}

			foreach ( $alerts as $alert ) {
				if ( ! is_array( $alert ) ) {
					continue;
				}

				if ( 'active' !== ( $alert['status'] ?? 'active' ) ) {
					continue;
				}

				if ( ! self::alert_matches_job( $alert, $job_categories, $job_types, (string) $job_location, $job_keywords ) ) {
					continue;
				}

				$email = sanitize_email( $alert['email'] ?? '' );
				if ( ! is_email( $email ) ) {
					continue;
				}

				if ( in_array( $email, $sent_to, true ) ) {
					continue;
				}

				self::send_job_alert_email( $email, $post );
				$sent_to[] = $email;
			}
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
		$user = get_user_by( 'email', sanitize_email( $email ) );
		if ( ! $user ) {
			return array();
		}

		$alerts = get_user_meta( (int) $user->ID, self::USER_META_KEY, true );
		if ( ! is_array( $alerts ) ) {
			return array();
		}

		$active = array_filter(
			$alerts,
			function ( $alert ) {
				return is_array( $alert ) && ( $alert['status'] ?? 'active' ) === 'active';
			}
		);

		return array_map(
			function ( $alert ) {
				return (object) $alert;
			},
			array_values( $active )
		);
	}

	/**
	 * Determine if an alert matches a newly published job.
	 *
	 * @since  1.7050.0000
	 * @param  array  $alert         Alert data.
	 * @param  array  $job_categories Job category IDs.
	 * @param  array  $job_types      Job type IDs.
	 * @param  string $job_location   Job location.
	 * @param  string $job_keywords   Job text keywords.
	 * @return bool True when alert matches.
	 */
	private static function alert_matches_job( array $alert, array $job_categories, array $job_types, string $job_location, string $job_keywords ): bool {
		$matches = false;

		$alert_category = (int) ( $alert['job_category'] ?? 0 );
		if ( $alert_category > 0 && in_array( $alert_category, $job_categories, true ) ) {
			$matches = true;
		}

		$alert_type = (int) ( $alert['job_type'] ?? 0 );
		if ( $alert_type > 0 && in_array( $alert_type, $job_types, true ) ) {
			$matches = true;
		}

		$alert_location = sanitize_text_field( $alert['location'] ?? '' );
		if ( '' !== $alert_location && '' !== $job_location && false !== stripos( $job_location, $alert_location ) ) {
			$matches = true;
		}

		$alert_keywords = sanitize_text_field( $alert['keywords'] ?? '' );
		if ( '' !== $alert_keywords && false !== stripos( $job_keywords, $alert_keywords ) ) {
			$matches = true;
		}

		return $matches;
	}
}
