<?php
/**
 * No GDPR Export Completion Confirmation Diagnostic
 *
 * Tests whether admins receive notification when personal data export requests are fulfilled.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Privacy
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_No_GDPR_Export_Completion_Confirmation Class
 *
 * Checks if admins are notified when export requests are completed.
 *
 * @since 0.6093.1200
 */
class Diagnostic_No_GDPR_Export_Completion_Confirmation extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-gdpr-export-completion-confirmation';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'GDPR Export Completion Tracking';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies that admins are notified when personal data export requests are fulfilled';

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
		global $wpdb;

		$issues = array();

		// 1. Check if request table exists.
		$table_name = $wpdb->prefix . 'actionscheduler_actions';

		// WordPress uses wp_user_request for privacy requests.
		$request_table = $wpdb->prefix . 'posts';
		$request_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$request_table} WHERE post_type = %s",
				'user_request'
			)
		);

		if ( 0 === (int) $request_count ) {
			// No requests yet - can't determine if notifications work.
			return null;
		}

		// 2. Check if admin notification hooks are registered.
		$notification_hooks = array(
			'user_request_action_confirmed',
			'wp_privacy_personal_data_export_file_created',
		);

		$missing_hooks = array();
		foreach ( $notification_hooks as $hook ) {
			if ( ! has_action( $hook ) ) {
				$missing_hooks[] = $hook;
			}
		}

		if ( ! empty( $missing_hooks ) ) {
			$issues[] = sprintf(
				/* translators: %s: comma-separated list of hooks */
				__( 'Missing notification hooks: %s', 'wpshadow' ),
				implode( ', ', $missing_hooks )
			);
		}

		// 3. Check if recent requests have status tracking.
		$recent_requests = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ID, post_status, post_modified
				FROM {$request_table}
				WHERE post_type = %s
				ORDER BY post_modified DESC
				LIMIT 10",
				'user_request'
			)
		);

		if ( ! empty( $recent_requests ) ) {
			$pending_count = 0;
			$old_pending   = 0;
			$now           = current_time( 'timestamp' );

			foreach ( $recent_requests as $request ) {
				if ( 'request-pending' === $request->post_status ) {
					$pending_count++;
					$age = $now - strtotime( $request->post_modified );

					// If pending for more than 7 days, might be stuck.
					if ( $age > ( 7 * DAY_IN_SECONDS ) ) {
						$old_pending++;
					}
				}
			}

			if ( $old_pending > 0 ) {
				$issues[] = sprintf(
					/* translators: %d: number of requests */
					_n(
						'%d export request pending for more than 7 days',
						'%d export requests pending for more than 7 days',
						$old_pending,
						'wpshadow'
					),
					$old_pending
				);
			}
		}

		// 4. Check if admin email notifications are configured.
		$admin_email = get_option( 'admin_email' );
		if ( empty( $admin_email ) || ! is_email( $admin_email ) ) {
			$issues[] = __( 'Admin email not configured - notifications cannot be sent', 'wpshadow' );
		}

		// 5. Check for custom notification filters.
		$filters = array(
			'user_request_confirmed_email_to',
			'user_request_confirmed_email_subject',
			'user_request_confirmed_email_content',
		);

		$filter_count = 0;
		foreach ( $filters as $filter ) {
			if ( has_filter( $filter ) ) {
				$filter_count++;
			}
		}

		if ( $filter_count === 0 && $request_count > 0 ) {
			// No filters applied - using default behavior (which is fine).
		}

		// 6. Check if cron is working (needed for processing requests).
		$cron_disabled = defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON;
		if ( $cron_disabled ) {
			$issues[] = __( 'WP-Cron is disabled - export requests may not be processed automatically', 'wpshadow' );
		}

		// 7. Check for audit trail capability.
		$completed_requests = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$request_table}
				WHERE post_type = %s
				AND post_status = %s",
				'user_request',
				'request-completed'
			)
		);

		if ( $completed_requests > 0 ) {
			// Check if completed requests have action logs.
			$has_action_logs = false;

			// WordPress doesn't log GDPR actions by default.
			$action_log_plugins = array(
				'simple-history/index.php',
				'wp-security-audit-log/wp-security-audit-log.php',
				'stream/stream.php',
			);

			foreach ( $action_log_plugins as $plugin ) {
				if ( is_plugin_active( $plugin ) ) {
					$has_action_logs = true;
					break;
				}
			}

			if ( ! $has_action_logs ) {
				$issues[] = __( 'No audit logging plugin detected - GDPR actions are not being logged for compliance', 'wpshadow' );
			}
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: comma-separated list of issues */
				__( 'GDPR export tracking issues: %s', 'wpshadow' ),
				implode( '; ', $issues )
			),
			'severity'     => 'medium',
			'threat_level' => 70,
			'auto_fixable' => true,
			'kb_link'      => 'https://wpshadow.com/kb/gdpr-export-completion-tracking?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'      => array(
				'issues'          => $issues,
				'total_requests'  => $request_count,
				'admin_email'     => $admin_email,
			),
		);
	}
}
