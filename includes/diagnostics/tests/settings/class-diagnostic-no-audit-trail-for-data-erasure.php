<?php
/**
 * No Audit Trail for Data Erasure Diagnostic
 *
 * Detects whether personal data erasure requests are logged for compliance audits.
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
 * Diagnostic_No_Audit_Trail_For_Data_Erasure Class
 *
 * Verifies that data erasure requests are properly logged.
 *
 * @since 0.6093.1200
 */
class Diagnostic_No_Audit_Trail_For_Data_Erasure extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-audit-trail-for-data-erasure';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Data Erasure Audit Trail';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies that personal data erasure requests are logged for compliance audits';

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

		// 1. Check if erasure requests exist.
		$request_table = $wpdb->prefix . 'posts';
		$erasure_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$request_table}
				WHERE post_type = %s
				AND post_content LIKE %s",
				'user_request',
				'%remove_personal_data%'
			)
		);

		if ( 0 === (int) $erasure_count ) {
			// No erasure requests yet - can't test audit trail.
			return null;
		}

		// 2. Check for audit logging plugins.
		$audit_plugins = array(
			'simple-history/index.php'                          => 'Simple History',
			'wp-security-audit-log/wp-security-audit-log.php'   => 'WP Activity Log',
			'stream/stream.php'                                 => 'Stream',
			'aryo-activity-log/aryo-activity-log.php'           => 'Activity Log',
			'wp-activity-log/wp-activity-log.php'               => 'WP Activity Log',
		);

		$has_audit_plugin = false;
		$active_auditors  = array();

		foreach ( $audit_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$has_audit_plugin = true;
				$active_auditors[] = $plugin_name;
			}
		}

		if ( ! $has_audit_plugin ) {
			$issues[] = __( 'No audit logging plugin detected - erasure actions are not being tracked', 'wpshadow' );
		}

		// 3. Check WordPress request log table.
		$completed_requests = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ID, post_status, post_modified, post_content
				FROM {$request_table}
				WHERE post_type = %s
				AND post_content LIKE %s
				ORDER BY post_modified DESC
				LIMIT 10",
				'user_request',
				'%remove_personal_data%'
			)
		);

		if ( ! empty( $completed_requests ) ) {
			// Check if completed requests have confirmation dates.
			$untracked = 0;
			foreach ( $completed_requests as $request ) {
				if ( 'request-completed' === $request->post_status ) {
					// Check if there's a completion timestamp.
					$completed_timestamp = get_post_meta( $request->ID, '_completed_timestamp', true );

					if ( empty( $completed_timestamp ) ) {
						$untracked++;
					}
				}
			}

			if ( $untracked > 0 ) {
				$issues[] = sprintf(
					/* translators: %d: number of requests */
					_n(
						'%d erasure request lacks completion timestamp',
						'%d erasure requests lack completion timestamps',
						$untracked,
						'wpshadow'
					),
					$untracked
				);
			}
		}

		// 4. Check for administrator action tracking.
		$admin_actions = array(
			'user_request_confirmed',
			'user_request_action_confirmed',
		);

		$missing_tracking = array();
		foreach ( $admin_actions as $action ) {
			if ( ! has_action( $action ) ) {
				$missing_tracking[] = $action;
			}
		}

		if ( ! empty( $missing_tracking ) ) {
			$issues[] = sprintf(
				/* translators: %s: comma-separated list of actions */
				__( 'Missing action tracking hooks: %s', 'wpshadow' ),
				implode( ', ', $missing_tracking )
			);
		}

		// 5. Check log retention policy.
		$log_retention = get_option( 'wp_privacy_log_retention', false );

		if ( false === $log_retention ) {
			$issues[] = __( 'Log retention policy not configured - may violate data minimization principle', 'wpshadow' );
		}

		// 6. Verify request details are being captured.
		if ( ! empty( $completed_requests ) ) {
			$missing_details = 0;
			foreach ( $completed_requests as $request ) {
				$request_email = get_post_meta( $request->ID, '_user_email', true );
				$confirmed_at  = get_post_meta( $request->ID, '_confirmed_timestamp', true );

				if ( empty( $request_email ) || empty( $confirmed_at ) ) {
					$missing_details++;
				}
			}

			if ( $missing_details > 0 ) {
				$issues[] = sprintf(
					/* translators: %d: number of requests */
					_n(
						'%d erasure request missing critical details',
						'%d erasure requests missing critical details',
						$missing_details,
						'wpshadow'
					),
					$missing_details
				);
			}
		}

		// 7. Check if erasure results are being logged.
		$has_result_logging = false;
		if ( ! empty( $completed_requests ) ) {
			foreach ( $completed_requests as $request ) {
				$erasure_data = get_post_meta( $request->ID, '_erasure_data', true );

				if ( ! empty( $erasure_data ) ) {
					$has_result_logging = true;
					break;
				}
			}

			if ( ! $has_result_logging ) {
				$issues[] = __( 'Erasure results are not being logged - cannot verify what was deleted', 'wpshadow' );
			}
		}

		// 8. Check for data controller accountability.
		$site_admin = get_option( 'admin_email' );
		$privacy_policy_page = get_option( 'wp_page_for_privacy_policy' );

		if ( empty( $site_admin ) || empty( $privacy_policy_page ) ) {
			$issues[] = __( 'Data controller information incomplete - audit trail lacks accountability context', 'wpshadow' );
		}

		// 9. Check for old request cleanup.
		$old_requests = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$request_table}
				WHERE post_type = %s
				AND post_status = %s
				AND post_modified < DATE_SUB(NOW(), INTERVAL 6 MONTH)",
				'user_request',
				'request-completed'
			)
		);

		if ( (int) $old_requests > 10 ) {
			$issues[] = sprintf(
				/* translators: %d: number of old requests */
				_n(
					'%d old erasure request should be archived',
					'%d old erasure requests should be archived',
					$old_requests,
					'wpshadow'
				),
				$old_requests
			);
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: comma-separated list of issues */
				__( 'Audit trail gaps detected: %s', 'wpshadow' ),
				implode( '; ', $issues )
			),
			'severity'     => 'high',
			'threat_level' => 80,
			'auto_fixable' => true,
			'kb_link'      => 'https://wpshadow.com/kb/data-erasure-audit-trail?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'      => array(
				'issues'           => $issues,
				'erasure_count'    => $erasure_count,
				'audit_plugins'    => $active_auditors,
			),
		);
	}
}
