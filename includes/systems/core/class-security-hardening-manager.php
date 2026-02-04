<?php
/**
 * Security Hardening Manager
 *
 * Central orchestrator for all security hardening features including rate
 * limiting, file integrity monitoring, treatment sandbox validation, and
 * security audit logging.
 *
 * **Features:**
 * - Initialize all security subsystems
 * - Coordinate security checks on schedule
 * - Aggregate security alerts and notifications
 * - Provide unified security dashboard
 * - Configure security settings
 *
 * **Philosophy Alignment:**
 * - #10 (Beyond Pure): Comprehensive security by design
 * - #8 (Inspire Confidence): Users trust site is protected
 * - #1 (Helpful Neighbor): Clear security guidance and recommendations
 *
 * @package    WPShadow
 * @subpackage Core
 * @since      1.6035.0948
 */

declare(strict_types=1);

namespace WPShadow\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Security Hardening Manager Class
 *
 * Coordinates all security hardening features.
 *
 * @since 1.6035.0948
 */
class Security_Hardening_Manager {

	/**
	 * Initialize security hardening.
	 *
	 * @since  1.6035.0948
	 * @return void
	 */
	public static function init(): void {
		// Initialize file integrity monitoring
		if ( self::is_feature_enabled( 'file_integrity' ) ) {
			File_Integrity_Monitor::init();
		}

		// Hook treatment sandbox validation
		if ( self::is_feature_enabled( 'treatment_sandbox' ) ) {
			add_filter( 'wpshadow_before_treatment_apply', array( __CLASS__, 'validate_treatment_safety' ), 10, 2 );
		}

		// Schedule security health check
		if ( ! wp_next_scheduled( 'wpshadow_security_health_check' ) ) {
			wp_schedule_event( time(), 'daily', 'wpshadow_security_health_check' );
		}
		add_action( 'wpshadow_security_health_check', array( __CLASS__, 'run_security_health_check' ) );

		// Add admin notices for security alerts
		add_action( 'admin_notices', array( __CLASS__, 'show_security_alerts' ) );

		// Register security audit log cleanup
		if ( ! wp_next_scheduled( 'wpshadow_cleanup_security_logs' ) ) {
			wp_schedule_event( time(), 'weekly', 'wpshadow_cleanup_security_logs' );
		}
		add_action( 'wpshadow_cleanup_security_logs', array( __CLASS__, 'cleanup_old_security_logs' ) );
	}

	/**
	 * Check if security feature is enabled.
	 *
	 * @since  1.6035.0948
	 * @param  string $feature Feature key (file_integrity|rate_limiting|treatment_sandbox).
	 * @return bool True if enabled.
	 */
	public static function is_feature_enabled( string $feature ): bool {
		$enabled_features = get_option( 'wpshadow_security_features', array(
			'file_integrity'    => true,
			'rate_limiting'     => true,
			'treatment_sandbox' => true,
			'security_audit'    => true,
		) );

		return isset( $enabled_features[ $feature ] ) && $enabled_features[ $feature ];
	}

	/**
	 * Enable security feature.
	 *
	 * @since  1.6035.0948
	 * @param  string $feature Feature key.
	 * @return bool True if enabled successfully.
	 */
	public static function enable_feature( string $feature ): bool {
		$enabled_features = get_option( 'wpshadow_security_features', array() );
		$enabled_features[ $feature ] = true;
		
		$result = update_option( 'wpshadow_security_features', $enabled_features );

		if ( $result ) {
			self::log_security_event( 'security_feature_enabled', array( 'feature' => $feature ) );
		}

		return $result;
	}

	/**
	 * Disable security feature.
	 *
	 * @since  1.6035.0948
	 * @param  string $feature Feature key.
	 * @return bool True if disabled successfully.
	 */
	public static function disable_feature( string $feature ): bool {
		$enabled_features = get_option( 'wpshadow_security_features', array() );
		$enabled_features[ $feature ] = false;
		
		$result = update_option( 'wpshadow_security_features', $enabled_features );

		if ( $result ) {
			self::log_security_event( 'security_feature_disabled', array( 'feature' => $feature ) );
		}

		return $result;
	}

	/**
	 * Validate treatment safety before application.
	 *
	 * Hooks into treatment execution pipeline.
	 *
	 * @since  1.6035.0948
	 * @param  bool   $allow            Whether to allow treatment.
	 * @param  string $treatment_class  Treatment class name.
	 * @return bool True if safe to proceed.
	 */
	public static function validate_treatment_safety( bool $allow, string $treatment_class ): bool {
		if ( ! $allow ) {
			return false; // Already blocked by another filter
		}

		return Treatment_Sandbox::pre_treatment_validation( $treatment_class );
	}

	/**
	 * Run comprehensive security health check.
	 *
	 * @since  1.6035.0948
	 * @return array {
	 *     Security health check result.
	 *
	 *     @type bool  $healthy    Overall security health status.
	 *     @type array $issues     Security issues found.
	 *     @type array $checks     Individual check results.
	 * }
	 */
	public static function run_security_health_check(): array {
		$checks = array();
		$issues = array();

		// File integrity check
		if ( self::is_feature_enabled( 'file_integrity' ) ) {
			$integrity = File_Integrity_Monitor::run_integrity_check();
			$checks['file_integrity'] = $integrity;
			
			if ( ! $integrity['passed'] ) {
				$issues[] = __( 'File integrity check failed - unauthorized file modifications detected', 'wpshadow' );
			}
		}

		// Rate limiting status
		if ( self::is_feature_enabled( 'rate_limiting' ) ) {
			$rate_limit_violations = self::get_recent_rate_limit_violations();
			$checks['rate_limiting'] = array(
				'violations_24h' => count( $rate_limit_violations ),
			);
			
			if ( count( $rate_limit_violations ) > 100 ) {
				$issues[] = sprintf(
					/* translators: %d: number of violations */
					__( 'High number of rate limit violations detected: %d in last 24 hours', 'wpshadow' ),
					count( $rate_limit_violations )
				);
			}
		}

		// Treatment sandbox status
		if ( self::is_feature_enabled( 'treatment_sandbox' ) ) {
			$failed_validations = self::get_recent_failed_validations();
			$checks['treatment_sandbox'] = array(
				'failed_validations' => count( $failed_validations ),
			);
		}

		$healthy = empty( $issues );

		self::log_security_event(
			'security_health_check_completed',
			array(
				'healthy' => $healthy,
				'issues_count' => count( $issues ),
				'checks' => $checks,
			)
		);

		return array(
			'healthy' => $healthy,
			'issues'  => $issues,
			'checks'  => $checks,
		);
	}

	/**
	 * Get security health score (0-100).
	 *
	 * @since  1.6035.0948
	 * @return array {
	 *     Security score details.
	 *
	 *     @type int   $score       Score out of 100.
	 *     @type array $breakdown   Score breakdown by category.
	 *     @type string $grade      Letter grade (A-F).
	 * }
	 */
	public static function get_security_score(): array {
		$health_check = self::run_security_health_check();
		
		$breakdown = array(
			'file_integrity'    => 0,
			'rate_limiting'     => 0,
			'treatment_sandbox' => 0,
		);

		// File integrity (40 points)
		if ( isset( $health_check['checks']['file_integrity'] ) ) {
			if ( $health_check['checks']['file_integrity']['passed'] ) {
				$breakdown['file_integrity'] = 40;
			}
		}

		// Rate limiting (30 points)
		if ( isset( $health_check['checks']['rate_limiting'] ) ) {
			$violations = $health_check['checks']['rate_limiting']['violations_24h'];
			if ( $violations === 0 ) {
				$breakdown['rate_limiting'] = 30;
			} elseif ( $violations < 10 ) {
				$breakdown['rate_limiting'] = 20;
			} elseif ( $violations < 50 ) {
				$breakdown['rate_limiting'] = 10;
			}
		}

		// Treatment sandbox (30 points)
		if ( isset( $health_check['checks']['treatment_sandbox'] ) ) {
			if ( $health_check['checks']['treatment_sandbox']['failed_validations'] === 0 ) {
				$breakdown['treatment_sandbox'] = 30;
			}
		}

		$total_score = array_sum( $breakdown );

		// Grade assignment
		if ( $total_score >= 90 ) {
			$grade = 'A';
		} elseif ( $total_score >= 80 ) {
			$grade = 'B';
		} elseif ( $total_score >= 70 ) {
			$grade = 'C';
		} elseif ( $total_score >= 60 ) {
			$grade = 'D';
		} else {
			$grade = 'F';
		}

		return array(
			'score'     => $total_score,
			'breakdown' => $breakdown,
			'grade'     => $grade,
		);
	}

	/**
	 * Show security alerts in admin.
	 *
	 * @since  1.6035.0948
	 * @return void
	 */
	public static function show_security_alerts(): void {
		$screen = get_current_screen();
		if ( ! $screen || 'dashboard' !== $screen->id ) {
			return; // Only show on dashboard
		}

		$alerts = get_transient( 'wpshadow_security_alerts' );
		if ( empty( $alerts ) ) {
			return;
		}

		foreach ( $alerts as $alert ) {
			printf(
				'<div class="notice notice-error is-dismissible"><p><strong>%s</strong> %s</p></div>',
				esc_html__( '🔒 WPShadow Security Alert:', 'wpshadow' ),
				esc_html( $alert['message'] )
			);
		}
	}

	/**
	 * Add security alert for admin notification.
	 *
	 * @since  1.6035.0948
	 * @param  string $message Alert message.
	 * @param  string $severity Severity (info|warning|critical).
	 * @return void
	 */
	public static function add_security_alert( string $message, string $severity = 'warning' ): void {
		$alerts = get_transient( 'wpshadow_security_alerts' );
		if ( false === $alerts ) {
			$alerts = array();
		}

		$alerts[] = array(
			'message'  => $message,
			'severity' => $severity,
			'time'     => time(),
		);

		set_transient( 'wpshadow_security_alerts', $alerts, DAY_IN_SECONDS );

		self::log_security_event(
			'security_alert_created',
			array(
				'message'  => $message,
				'severity' => $severity,
			)
		);
	}

	/**
	 * Get recent rate limit violations.
	 *
	 * @since  1.6035.0948
	 * @param  int $hours Hours to look back (default 24).
	 * @return array Violation records.
	 */
	private static function get_recent_rate_limit_violations( int $hours = 24 ): array {
		if ( ! class_exists( 'WPShadow\Core\Activity_Logger' ) ) {
			return array();
		}

		$since = time() - ( $hours * HOUR_IN_SECONDS );
		
		// Query activity log for rate limit violations
		return Activity_Logger::query( array(
			'event_type' => 'security_rate_limit_exceeded',
			'since'      => $since,
		) );
	}

	/**
	 * Get recent failed treatment validations.
	 *
	 * @since  1.6035.0948
	 * @param  int $days Days to look back (default 7).
	 * @return array Validation failure records.
	 */
	private static function get_recent_failed_validations( int $days = 7 ): array {
		if ( ! class_exists( 'WPShadow\Core\Activity_Logger' ) ) {
			return array();
		}

		$since = time() - ( $days * DAY_IN_SECONDS );
		
		return Activity_Logger::query( array(
			'event_type' => 'security_treatment_validation',
			'since'      => $since,
			'meta_query' => array(
				array(
					'key'   => 'safe',
					'value' => false,
				),
			),
		) );
	}

	/**
	 * Log security event.
	 *
	 * @since  1.6035.0948
	 * @param  string $event_type Event type identifier.
	 * @param  array  $data       Event data.
	 * @return void
	 */
	public static function log_security_event( string $event_type, array $data = array() ): void {
		if ( class_exists( 'WPShadow\Core\Activity_Logger' ) ) {
			Activity_Logger::log( $event_type, array_merge(
				$data,
				array(
					'category' => 'security',
					'user_id'  => get_current_user_id(),
					'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
				)
			) );
		}
	}

	/**
	 * Cleanup old security logs (scheduled task).
	 *
	 * @since  1.6035.0948
	 * @return int Number of logs deleted.
	 */
	public static function cleanup_old_security_logs(): int {
		if ( ! class_exists( 'WPShadow\Core\Activity_Logger' ) ) {
			return 0;
		}

		// Delete logs older than 90 days
		$cutoff = time() - ( 90 * DAY_IN_SECONDS );
		
		return Activity_Logger::delete_old_logs( $cutoff, array( 'category' => 'security' ) );
	}
}
