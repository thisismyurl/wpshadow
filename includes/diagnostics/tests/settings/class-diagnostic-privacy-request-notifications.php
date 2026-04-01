<?php
/**
 * Privacy Request Notifications Diagnostic
 *
 * Validates that privacy request notifications are configured and that
 * administrators are notified of data export and erasure requests.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Privacy Request Notifications Diagnostic Class
 *
 * Checks privacy request notification configuration.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Privacy_Request_Notifications extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'privacy-request-notifications';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Privacy Request Notifications';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates privacy request notification settings';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'privacy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check admin email configuration.
		$admin_email = get_option( 'admin_email' );
		if ( empty( $admin_email ) || ! is_email( $admin_email ) ) {
			$issues[] = __( 'Admin email is not configured or invalid (privacy notifications will fail)', 'wpshadow' );
		}

		// Check for privacy policy page.
		$privacy_page_id = get_option( 'wp_page_for_privacy_policy' );
		if ( empty( $privacy_page_id ) ) {
			$issues[] = __( 'Privacy Policy page not configured (required for compliance)', 'wpshadow' );
		}

		// Check if privacy tools are available.
		if ( ! function_exists( 'wp_privacy_personal_data_export_page' ) ) {
			$issues[] = __( 'Privacy tools not available (WordPress privacy functions missing)', 'wpshadow' );
		}

		// Check for pending privacy requests.
		global $wpdb;
		$pending_requests = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts}
			WHERE post_type = 'user_request' AND post_status = 'request-pending'"
		);

		if ( $pending_requests > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of pending requests */
				__( '%d pending privacy requests need attention', 'wpshadow' ),
				$pending_requests
			);
		}

		// Check for notification emails for privacy requests.
		$notify_admin = apply_filters( 'wp_privacy_notify_admin', true );
		if ( ! $notify_admin ) {
			$issues[] = __( 'Privacy request notifications to admin are disabled', 'wpshadow' );
		}

		// Check for request confirmation emails.
		$notify_user = apply_filters( 'wp_privacy_notify_user', true );
		if ( ! $notify_user ) {
			$issues[] = __( 'Privacy request confirmation emails to users are disabled', 'wpshadow' );
		}

		// Check for email delivery issues (if logged).
		$failed_emails = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->postmeta}
			WHERE meta_key LIKE '%privacy_email_failed%'
			OR meta_key LIKE '%user_request_failed%'"
		);

		if ( $failed_emails > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of failed emails */
				__( '%d failed privacy notification emails detected', 'wpshadow' ),
				$failed_emails
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of notification issues */
					__( 'Found %d privacy request notification issues.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'medium',
				'threat_level' => 30,
				'auto_fixable' => false,
				'details'      => array(
					'issues'         => $issues,
					'pending_count'  => absint( $pending_requests ),
					'recommendation' => __( 'Ensure privacy request notifications are enabled and admin email is valid. Monitor pending requests regularly.', 'wpshadow' ),
				),
			);
		}

		return null;
	}
}
