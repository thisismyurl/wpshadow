<?php
declare(strict_types=1);

namespace WPShadow\Guardian;

use WPShadow\Core\Hook_Subscriber_Base;
use WPShadow\Cloud\Notification_Manager;
use WPShadow\Cloud\Registration_Manager;

/**
 * Guardian Manager
 *
 * Central orchestration for Guardian automated health management.
 * Handles scheduled health checks and auto-fix execution via cron jobs.
 *
 * Philosophy: Automation with user control. Guardian helps proactively
 * manage site health but respects user preferences and stays transparent
 * about all actions taken (Commandment #1: Helpful Neighbor).
 *
 * Cron Jobs:
 * - wpshadow_guardian_health_check (hourly) - Run diagnostics
 * - wpshadow_guardian_auto_fix (daily) - Apply safe fixes
 *
 * Data Storage:
 * - wpshadow_guardian_enabled: Master toggle
 * - wpshadow_guardian_auto_fix_enabled: Auto-fix toggle
 * - wpshadow_guardian_check_interval: Frequency
 * - wpshadow_guardian_auto_fix_time: Scheduled time
 * - wpshadow_guardian_safe_fixes: Array of treatment IDs to auto-apply
 */
class Guardian_Manager extends Hook_Subscriber_Base {

	/**
	 * Get hook subscriptions.
	 *
	 * @since 0.6093.1200
	 * @return array Hook subscriptions.
	 */
	protected static function get_hooks(): array {
		return array(
			'wpshadow_guardian_health_check' => 'run_health_check',
			'wpshadow_guardian_auto_fix'     => 'run_auto_fixes',
		);
	}

	/**
	 * Initialize Guardian system (deprecated)
	 *
	 * Called on plugins_loaded hook.
	 * Sets up cron jobs if not already scheduled.
	 *
	 * @deprecated1.0 Use Guardian_Manager::subscribe() instead
	 * @return     void
	 */
	public static function init(): void {
		// Schedule health check (hourly)
		if ( ! wp_next_scheduled( 'wpshadow_guardian_health_check' ) ) {
			wp_schedule_event( time(), 'hourly', 'wpshadow_guardian_health_check' );
		}

		// Schedule auto-fix (daily)
		if ( ! wp_next_scheduled( 'wpshadow_guardian_auto_fix' ) ) {
			wp_schedule_event( time(), 'daily', 'wpshadow_guardian_auto_fix' );
		}

		// Subscribe to hooks
		self::subscribe();
	}

	/**
	 * Get Guardian settings
	 *
	 * @return array Current Guardian configuration
	 */
	public static function get_settings(): array {
		return array(
			'enabled'              => (bool) get_option( 'wpshadow_guardian_enabled', false ),
			'auto_fix_enabled'     => (bool) get_option( 'wpshadow_guardian_auto_fix_enabled', false ),
			'check_interval'       => get_option( 'wpshadow_guardian_check_interval', 'hourly' ),
			'auto_fix_time'        => get_option( 'wpshadow_guardian_auto_fix_time', '02:00' ),
			'safe_fixes'           => get_option( 'wpshadow_guardian_safe_fixes', array() ),
			'notification_enabled' => (bool) get_option( 'wpshadow_guardian_notification_enabled', true ),
		);
	}

	/**
	 * Check if Guardian is enabled
	 *
	 * @return bool True if Guardian is active
	 */
	public static function is_enabled(): bool {
		return (bool) get_option( 'wpshadow_guardian_enabled', false );
	}

	/**
	 * Update Guardian settings
	 *
	 * Validates settings before storing.
	 * Can only enable features if prerequisites met (e.g., registered for auto-fix).
	 *
	 * @param array $settings New settings (partial or full update)
	 *
	 * @return bool Success
	 */
	public static function update_settings( array $settings ): bool {
		// Get current settings for merging
		$current = self::get_settings();

		// Validate auto-fix enabling
		if ( ! empty( $settings['auto_fix_enabled'] ) ) {
			// Auto-fix requires safe_fixes to be configured
			$safe_fixes = $settings['safe_fixes'] ?? $current['safe_fixes'];
			if ( empty( $safe_fixes ) ) {
				return false; // No safe fixes configured
			}
		}

		// Store settings
		update_option( 'wpshadow_guardian_enabled', (bool) $settings['enabled'] ?? false );
		update_option( 'wpshadow_guardian_auto_fix_enabled', (bool) $settings['auto_fix_enabled'] ?? false );
		update_option( 'wpshadow_guardian_check_interval', sanitize_key( $settings['check_interval'] ?? 'hourly' ) );
		update_option( 'wpshadow_guardian_auto_fix_time', sanitize_text_field( $settings['auto_fix_time'] ?? '02:00' ) );
		update_option( 'wpshadow_guardian_safe_fixes', array_map( 'sanitize_text_field', $settings['safe_fixes'] ?? array() ) );
		update_option( 'wpshadow_guardian_notification_enabled', (bool) $settings['notification_enabled'] ?? true );

		do_action( 'wpshadow_guardian_settings_updated', $settings );

		return true;
	}

	/**
	 * Run scheduled health check (cron job)
	 *
	 * Executes all diagnostics and reports findings.
	 * Called hourly via wp-cron.
	 */
	public static function run_health_check(): void {
		// Skip if Guardian disabled
		if ( ! get_option( 'wpshadow_guardian_enabled' ) ) {
			return;
		}

		// Run background analyzers (CSS complexity, etc.)
		// These set transients that diagnostics can consume
		self::run_background_analyzers();

		// Run diagnostics through registry wrapper (executes hooks/logging consistently).
		$diagnostics_run = array();
		$diagnostics     = \WPShadow\Diagnostics\Diagnostic_Registry::get_all();
		foreach ( $diagnostics as $diagnostic ) {
			if ( ! class_exists( $diagnostic ) ) {
				continue;
			}

			$diagnostics_run[] = method_exists( $diagnostic, 'get_slug' ) ? $diagnostic::get_slug() : $diagnostic;
		}

		$findings       = \WPShadow\Diagnostics\Diagnostic_Registry::run_enabled_scans();
		$critical_count = count(
			array_filter(
				$findings,
				static function ( $finding ) {
					return is_array( $finding ) && isset( $finding['severity'] ) && 'critical' === $finding['severity'];
				}
			)
		);

		// Log health check
		Guardian_Activity_Logger::log_health_check( $findings );

		// Store latest diagnostics run for Guardian dashboard display.
		update_option(
			'wpshadow_guardian_last_diagnostics',
			array(
				'timestamp'   => current_time( 'mysql' ),
				'diagnostics' => $diagnostics_run,
			)
		);

		// Update baseline for anomaly detection
		Baseline_Manager::update_baseline( $findings );

		// Send notification when top-severity findings are present.
		if ( $critical_count > 0 && get_option( 'wpshadow_guardian_notification_enabled' ) ) {
			Notification_Manager::send_notification(
				'critical',
				array(
					'findings'       => $findings,
					'critical_count' => $critical_count,
					'timestamp'      => current_time( 'mysql' ),
				),
				'guardian_health_check'
			);
		}

		do_action( 'wpshadow_guardian_health_check_complete', $findings );
	}

	/**
	 * Run scheduled auto-fixes (cron job)
	 *
	 * Applies user-approved safe fixes.
	 * Called daily via wp-cron (default 2 AM).
	 * Always creates backup before fixing.
	 */
	public static function run_auto_fixes(): void {
		// Skip if auto-fix disabled
		if ( ! get_option( 'wpshadow_guardian_auto_fix_enabled' ) ) {
			return;
		}

		// Get user-approved safe fixes
		$safe_fixes = get_option( 'wpshadow_guardian_safe_fixes', array() );

		if ( empty( $safe_fixes ) ) {
			return; // No fixes to apply
		}

		$execution = Auto_Fix_Executor::execute_scheduled_fixes();
		$results   = $execution['treatments'] ?? array();

		// Send report email
		if ( ! empty( $results ) && get_option( 'wpshadow_guardian_notification_enabled' ) ) {
			self::send_auto_fix_report( $results );
		}

		do_action( 'wpshadow_guardian_auto_fix_complete', $results );
	}

	/**
	 * Send auto-fix report email
	 *
	 * @param array $results Fix results from run_auto_fixes()
	 */
	private static function send_auto_fix_report( array $results ): void {
		$admin_email   = get_option( 'admin_email' );
		$success_count = count( array_filter( $results, fn( $r ) => $r['success'] ) );
		$fail_count    = count( $results ) - $success_count;

		$subject = sprintf(
			__( 'WPShadow Guardian: %d fix(es) applied', 'wpshadow' ),
			$success_count
		);

		$message  = '<html><body style="font-family: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto;">';
		$message .= '<h2>' . esc_html__( 'Guardian Auto-Fix Report', 'wpshadow' ) . '</h2>';
		$message .= '<p style="font-size: 14px; color: #666;">' . wp_date( 'M d, Y H:i' ) . '</p>';

		if ( $success_count > 0 ) {
			$message .= '<h3 style="color: #27ae60;">✅ ' . sprintf(
				__( '%d Fix(es) Applied', 'wpshadow' ),
				$success_count
			) . '</h3>';

			foreach ( $results as $result ) {
				if ( $result['success'] ) {
					$message .= '<p><strong>' . esc_html( $result['treatment'] ) . ':</strong> ' .
						esc_html( $result['message'] ) . '</p>';

					if ( ! empty( $result['backup_id'] ) ) {
						$message .= '<p style="font-size: 12px; color: #999;">Backup: ' .
							esc_html( $result['backup_id'] ) . '</p>';
					}
				}
			}
		}

		if ( $fail_count > 0 ) {
			$message .= '<h3 style="color: #e74c3c;">⚠️ ' . sprintf(
				__( '%d Fix(es) Failed', 'wpshadow' ),
				$fail_count
			) . '</h3>';

			foreach ( $results as $result ) {
				if ( ! $result['success'] ) {
					$message .= '<p><strong>' . esc_html( $result['treatment'] ) . ':</strong> ' .
						esc_html( $result['message'] ) . '</p>';
				}
			}
		}

		$message .= '<p style="margin-top: 30px; font-size: 12px; color: #999;">' .
			__( 'All fixes create automated backups. You can restore from the WPShadow dashboard anytime within 30 days.', 'wpshadow' ) .
			'</p>';

		$message .= '</body></html>';

		wp_mail(
			$admin_email,
			$subject,
			$message,
			array( 'Content-Type: text/html; charset=UTF-8' )
		);
	}

	/**
	 * Disable Guardian and clean up cron jobs
	 *
	 * Called when user disables Guardian in settings.
	 */
	public static function disable(): void {
		// Remove scheduled cron jobs
		$health_check = wp_next_scheduled( 'wpshadow_guardian_health_check' );
		if ( $health_check ) {
			wp_unschedule_event( $health_check, 'wpshadow_guardian_health_check' );
		}

		$auto_fix = wp_next_scheduled( 'wpshadow_guardian_auto_fix' );
		if ( $auto_fix ) {
			wp_unschedule_event( $auto_fix, 'wpshadow_guardian_auto_fix' );
		}

		// Disable in options
		update_option( 'wpshadow_guardian_enabled', false );

		do_action( 'wpshadow_guardian_disabled' );
	}

	/**
	 * Get guardian statistics
	 *
	 * @return array Guardian usage stats
	 */
	public static function get_statistics(): array {
		$activity_log = get_option( 'wpshadow_guardian_activity_log', array() );

		$health_checks = array_filter(
			$activity_log,
			fn( $entry ) => $entry['type'] === 'health_check'
		);

		$auto_fixes = array_filter(
			$activity_log,
			fn( $entry ) => $entry['type'] === 'auto_fix'
		);

		$successful_fixes = array_filter(
			$auto_fixes,
			fn( $entry ) => $entry['success'] ?? false
		);

		return array(
			'health_checks_total' => count( $health_checks ),
			'auto_fixes_total'    => count( $auto_fixes ),
			'auto_fixes_success'  => count( $successful_fixes ),
			'auto_fixes_failed'   => count( $auto_fixes ) - count( $successful_fixes ),
			'success_rate'        => count( $auto_fixes ) > 0
				? round( ( count( $successful_fixes ) / count( $auto_fixes ) ) * 100, 1 )
				: 0,
		);
	}

	/**
	 * Run background analyzers
	 *
	 * Executes background analysis tasks that set transient data
	 * for diagnostics to consume. These are performance-intensive
	 * operations that only need to run periodically.
	 *
	 * Current analyzers:
	 * - CSS_Analyzer: CSS selector complexity analysis
	 *
	 * Future analyzers:
	 * - JavaScript_Analyzer: JS complexity/errors
	 * - Image_Analyzer: Image optimization opportunities
	 * - Database_Analyzer: Query performance patterns
	 */
	private static function run_background_analyzers(): void {
		// CSS complexity analysis (cached 24 hours)
		CSS_Analyzer::analyze();

		// Icon strategy analysis (cached 24 hours)
		Icon_Analyzer::analyze();

		// Layout thrashing detection (cached 24 hours)
		Layout_Thrashing_Analyzer::analyze();

		// Hook for adding custom analyzers
		do_action( 'wpshadow_guardian_background_analyzers' );
	}
}
