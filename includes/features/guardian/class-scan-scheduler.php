<?php
/**
 * Scheduled Scans Manager
 *
 * Manages automatic scheduled diagnostic scans using WordPress cron.
 * Runs health checks on configurable schedule (daily/weekly).
 * Stores results and logs scan activity.
 *
 * @since   1.6032.1010
 * @package WPShadow\Guardian
 */

declare(strict_types=1);

namespace WPShadow\Guardian;

use WPShadow\Core\Activity_Logger;
use WPShadow\Core\Settings_Registry;
use WPShadow\Core\Diagnostic_Registry;
use WPShadow\Core\Hook_Subscriber_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Scan_Scheduler Class
 *
 * Handles automated diagnostic scans on a configurable schedule.
 *
 * @since 1.6032.1010
 */
class Scan_Scheduler extends Hook_Subscriber_Base {

	/**
	 * Settings prefix
	 *
	 * @var string
	 */
	private static $settings_prefix = 'wpshadow_scheduled_scans';

	/**
	 * Cron hook name
	 *
	 * @var string
	 */
	private static $cron_hook = 'wpshadow_scheduled_scan';

	/**
	 * Get hook subscriptions.
	 *
	 * @since  1.7035.1400
	 * @return array Hook subscriptions.
	 */
	protected static function get_hooks(): array {
		return array(
			'wpshadow_register_settings'                        => 'register_settings',
			'wpshadow_scheduled_scan'                           => 'execute_scheduled_scan',
			'admin_notices'                                     => 'check_cron_health',
			'wp_ajax_wpshadow_dismiss_cron_disabled_notice' => 'dismiss_cron_disabled_notice',
		);
	}

	/**
	 * Initialize scheduled scans system (deprecated)
	 *
	 * @deprecated 1.7035.1400 Use Scan_Scheduler::subscribe() instead
	 * @since      1.6032.1010
	 * @return     void
	 */
	public static function init() {
		// Setup/clear cron on activation/deactivation
		register_activation_hook( \WPSHADOW_FILE, array( __CLASS__, 'schedule_cron' ) );
		register_deactivation_hook( \WPSHADOW_FILE, array( __CLASS__, 'unschedule_cron' ) );

		// Subscribe to hooks
		self::subscribe();
	}

	/**
	 * Register scheduled scan settings
	 *
	 * @since  1.6032.1010
	 * @return void
	 */
	public static function register_settings() {
		// Enable scheduled scans
		register_setting( 'wpshadow_guardian', self::$settings_prefix . '_enabled', array(
			'type'              => 'boolean',
			'sanitize_callback' => array( __CLASS__, 'sanitize_boolean' ),
			'default'           => true,
		) );

		// Scan frequency (daily, weekly, bi-weekly)
		register_setting( 'wpshadow_guardian', self::$settings_prefix . '_frequency', array(
			'type'              => 'string',
			'sanitize_callback' => array( __CLASS__, 'sanitize_frequency' ),
			'default'           => 'daily',
		) );

		// Scan time (hours in 24-hour format)
		register_setting( 'wpshadow_guardian', self::$settings_prefix . '_time', array(
			'type'              => 'string',
			'sanitize_callback' => array( __CLASS__, 'sanitize_time' ),
			'default'           => '02:00',
		) );

		// Scan depth (quick, standard, deep)
		register_setting( 'wpshadow_guardian', self::$settings_prefix . '_depth', array(
			'type'              => 'string',
			'sanitize_callback' => array( __CLASS__, 'sanitize_depth' ),
			'default'           => 'standard',
		) );

		// Maximum execution time (in seconds)
		register_setting( 'wpshadow_guardian', self::$settings_prefix . '_max_time', array(
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
			'default'           => 300,
		) );

		// Email results
		register_setting( 'wpshadow_guardian', self::$settings_prefix . '_email_results', array(
			'type'              => 'boolean',
			'sanitize_callback' => array( __CLASS__, 'sanitize_boolean' ),
			'default'           => false,
		) );

		// Last scan timestamp
		register_setting( 'wpshadow_guardian', self::$settings_prefix . '_last_scan', array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'default'           => '',
		) );

		// Next scheduled scan
		register_setting( 'wpshadow_guardian', self::$settings_prefix . '_next_scan', array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'default'           => '',
		) );
	}

	/**
	 * Schedule cron event
	 *
	 * Called on plugin activation to register WordPress cron event.
	 *
	 * @since  1.6032.1010
	 * @return void
	 */
	public static function schedule_cron() {
		// Only schedule if enabled
		if ( ! self::is_enabled() ) {
			return;
		}

		// Clear any existing scheduled event
		wp_clear_scheduled_hook( self::$cron_hook );

		// Get next run time
		$next_run = self::calculate_next_run_time();

		// Schedule event
		wp_schedule_event( $next_run, self::get_frequency(), self::$cron_hook );

		Activity_Logger::log(
			'scheduled_scan_enabled',
			sprintf(
				__( 'Frequency: %s, Next run: %s', 'wpshadow' ),
				self::get_frequency_label(),
				wp_date( 'Y-m-d H:i:s', $next_run )
			)
		);
	}

	/**
	 * Unschedule cron event
	 *
	 * Called on plugin deactivation to remove WordPress cron event.
	 *
	 * @since  1.6032.1010
	 * @return void
	 */
	public static function unschedule_cron() {
		wp_clear_scheduled_hook( self::$cron_hook );

		Activity_Logger::log(
			'scheduled_scan_disabled',
			__( 'Reason: Plugin deactivation', 'wpshadow' )
		);
	}

	/**
	 * Execute scheduled scan
	 *
	 * Runs the diagnostic scan at the scheduled time.
	 *
	 * @since  1.6032.1010
	 * @return void
	 */
	public static function execute_scheduled_scan() {
		// Check if still enabled
		if ( ! self::is_enabled() ) {
			return;
		}

		// Record start time
		$start_time = microtime( true );
		update_option( self::$settings_prefix . '_last_scan', current_time( 'mysql' ) );

		// Run diagnostics
		$results = self::run_diagnostics();

		// Calculate execution time
		$execution_time = microtime( true ) - $start_time;

		// Store results
		self::store_scan_results( $results, $execution_time );

		// Send email if configured
		if ( self::should_email_results() ) {
			self::email_scan_results( $results );
		}

		// Log activity
		$findings_count = count( $results['findings'] ?? array() );
		$critical_count = count( array_filter( $results['findings'] ?? array(), function( $f ) {
			return ( $f['severity'] ?? 'low' ) === 'critical';
		} ) );
		Activity_Logger::log(
			'scheduled_scan_completed',
			sprintf(
				__( 'Time: %.2fs, %d findings (%d critical)', 'wpshadow' ),
				$execution_time,
				$findings_count,
				$critical_count
			)
		);

		// Reschedule next run
		self::schedule_next_run();
	}

	/**
	 * Run all diagnostic checks
	 *
	 * Executes all registered diagnostics based on configured depth.
	 *
	 * @since  1.6032.1010
	 * @return array Results of all diagnostics.
	 */
	private static function run_diagnostics(): array {
		$depth = get_option( self::$settings_prefix . '_depth', 'standard' );
		$max_time = get_option( self::$settings_prefix . '_max_time', 300 );

		// Set max execution time
		if ( function_exists( 'set_time_limit' ) ) {
			@set_time_limit( $max_time );
		}

		$findings = array();
		$skipped = array();
		$diagnostics_run = array();

		// MURPHY-SAFE: Track elapsed time (bug fix - was comparing microtime() against $max_time)
		$start_time = microtime( true );
		$time_limit = $max_time - 30; // 30-second buffer for cleanup

		// Get diagnostics to run based on depth
		$diagnostics = self::get_diagnostics_by_depth( $depth );

		foreach ( $diagnostics as $slug => $class ) {
			// MURPHY-SAFE: Check ELAPSED time (not absolute time)
			$elapsed = microtime( true ) - $start_time;

			if ( $elapsed > $time_limit ) {
				// Time limit reached, skip remaining diagnostics
				Error_Handler::log_warning(
					'Scan time limit reached',
					array(
						'elapsed'  => $elapsed,
						'skipped'  => $slug,
						'limit'    => $time_limit,
					)
				);

				$skipped[] = $slug;
				continue;
			}

			try {
				// Run diagnostic
				if ( class_exists( $class ) && method_exists( $class, 'execute' ) ) {
					$diagnostics_run[] = $slug;
					$result = call_user_func( array( $class, 'execute' ) );

					if ( $result ) {
						$findings[] = array_merge( $result, array(
							'detected_at' => current_time( 'mysql' ),
							'source'      => 'scheduled_scan',
						) );
					}
				} else {
					$skipped[] = $slug;
				}
			} catch ( \Exception $e ) {
				Activity_Logger::log(
					'scheduled_scan_error',
					sprintf(
						__( 'Error in %s: %s', 'wpshadow' ),
						$slug,
						$e->getMessage()
					)
				);
			}
		}

		return array(
			'findings'            => $findings,
			'skipped'             => $skipped,
			'depth'               => $depth,
			'diagnostics_run'     => $diagnostics_run,
			'diagnostics_skipped' => $skipped,
			'diagnostics_total'   => array_keys( $diagnostics ),
		);
	}

	/**
	 * Get diagnostics based on scan depth
	 *
	 * @since  1.6032.1010
	 * @param  string $depth Scan depth (quick, standard, deep).
	 * @return array Array of diagnostic slugs and classes.
	 */
	private static function get_diagnostics_by_depth( $depth ): array {
		// Start with quick diagnostics (fast checks)
		$diagnostics = array(
			// Security quick checks
			'ssl-certificate'           => 'WPShadow\Diagnostics\Diagnostic_SSL_Certificate',
			'wordpress-version'         => 'WPShadow\Diagnostics\Diagnostic_WordPress_Version',
			'plugin-updates'            => 'WPShadow\Diagnostics\Diagnostic_Plugin_Updates',
			'theme-updates'             => 'WPShadow\Diagnostics\Diagnostic_Theme_Updates',
			'file-permissions'          => 'WPShadow\Diagnostics\Diagnostic_File_Permissions',

			// Performance quick checks
			'database-size'             => 'WPShadow\Diagnostics\Diagnostic_Database_Size',
			'php-memory-limit'          => 'WPShadow\Diagnostics\Diagnostic_PHP_Memory_Limit',
			'wp-memory-limit'           => 'WPShadow\Diagnostics\Diagnostic_WP_Memory_Limit',
		);

		// Add standard depth diagnostics
		if ( 'standard' === $depth || 'deep' === $depth ) {
			$diagnostics = array_merge( $diagnostics, array(
				'admin-users'               => 'WPShadow\Diagnostics\Diagnostic_Admin_Users',
				'plugin-conflicts'          => 'WPShadow\Diagnostics\Diagnostic_Plugin_Conflicts',
				'theme-compatibility'       => 'WPShadow\Diagnostics\Diagnostic_Theme_Compatibility',
				'backup-status'             => 'WPShadow\Diagnostics\Diagnostic_Backup_Status',
			) );
		}

		// Add deep diagnostics (slow, comprehensive checks)
		if ( 'deep' === $depth ) {
			$diagnostics = array_merge( $diagnostics, array(
				'orphaned-attachments'      => 'WPShadow\Diagnostics\Diagnostic_Orphaned_Attachments',
				'database-optimization'     => 'WPShadow\Diagnostics\Diagnostic_Database_Optimization',
				'security-headers'          => 'WPShadow\Diagnostics\Diagnostic_Security_Headers',
			) );
		}

		return apply_filters( 'wpshadow_scheduled_scan_diagnostics', $diagnostics, $depth );
	}

	/**
	 * Store scan results
	 *
	 * Saves scan results to database for historical tracking.
	 *
	 * @since  1.6032.1010
	 * @param  array $results Scan results.
	 * @param  float $execution_time Time taken to complete scan.
	 * @return void
	 */
	private static function store_scan_results( $results, $execution_time ) {
		global $wpdb;

		$scan_data = array(
			'scan_date'       => current_time( 'mysql' ),
			'execution_time'  => round( $execution_time, 2 ),
			'findings_count'  => count( $results['findings'] ?? array() ),
			'critical_count'  => count( array_filter( $results['findings'] ?? array(), function( $f ) {
				return ( $f['severity'] ?? 'low' ) === 'critical';
			} ) ),
			'results'         => maybe_serialize( $results ),
		);

		// Insert into scans table (if it exists)
		$table = $wpdb->prefix . 'wpshadow_scans';
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table ) {
			$wpdb->insert( $table, $scan_data ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		}

		// Also update option with latest scan
		update_option( self::$settings_prefix . '_latest_results', $scan_data );
	}

	/**
	 * Email scan results
	 *
	 * Sends scan results via email if configured.
	 *
	 * @since  1.6032.1010
	 * @param  array $results Scan results.
	 * @return void
	 */
	private static function email_scan_results( $results ) {
		$to = get_option( 'admin_email' );
		$findings = $results['findings'] ?? array();

		if ( empty( $findings ) ) {
			return;
		}

		$subject = sprintf(
			/* translators: %d: number of findings */
			esc_html__( '[WPShadow Scan] %d Finding(s) Detected', 'wpshadow' ),
			count( $findings )
		);

		$message = sprintf(
			/* translators: 1: blog name, 2: number of findings */
			esc_html__( 'WPShadow scheduled scan for %1$s detected %2$d issues. View them in your dashboard.', 'wpshadow' ),
			get_bloginfo( 'name' ),
			count( $findings )
		);

		wp_mail( $to, $subject, $message );
	}

	/**
	 * Schedule next run
	 *
	 * Reschedules the next diagnostic scan.
	 *
	 * @since  1.6032.1010
	 * @return void
	 */
	private static function schedule_next_run() {
		wp_clear_scheduled_hook( self::$cron_hook );

		$next_run = self::calculate_next_run_time();
		wp_schedule_event( $next_run, self::get_frequency(), self::$cron_hook );

		update_option( self::$settings_prefix . '_next_scan', wp_date( 'Y-m-d H:i:s', $next_run ) );
	}

	/**
	 * Calculate next run time based on settings
	 *
	 * @since  1.6032.1010
	 * @return int Unix timestamp of next run.
	 */
	private static function calculate_next_run_time(): int {
		$frequency = self::get_frequency();
		$time_str = get_option( self::$settings_prefix . '_time', '02:00' );
		list( $hours, $minutes ) = explode( ':', $time_str );

		// Create next run at configured time
		$next_run = wp_date( 'Y-m-d' ) . ' ' . $hours . ':' . $minutes . ':00';
		$next_run = strtotime( $next_run );

		// If next run is in the past, move to next period
		if ( $next_run < time() ) {
			if ( 'daily' === $frequency ) {
				$next_run = strtotime( '+1 day', $next_run );
			} elseif ( 'weekly' === $frequency ) {
				$next_run = strtotime( '+7 days', $next_run );
			}
		}

		return $next_run;
	}

	/**
	 * Get latest scheduled scan summary.
	 *
	 * Provides a safe, normalized snapshot of the most recent scan results.
	 *
	 * @since  1.8001.1200
	 * @return array {
	 *     Latest scan summary.
	 *
	 *     @type string $scan_date           Date of the scan (Y-m-d H:i:s).
	 *     @type float  $execution_time      Scan duration in seconds.
	 *     @type int    $findings_count      Number of findings.
	 *     @type int    $critical_count      Number of critical findings.
	 *     @type string $depth               Scan depth.
	 *     @type array  $diagnostics_run     Diagnostic slugs that ran.
	 *     @type array  $diagnostics_skipped Diagnostic slugs that were skipped.
	 * }
	 */
	public static function get_latest_scan_results(): array {
		$latest = get_option( self::$settings_prefix . '_latest_results', array() );
		if ( empty( $latest ) || ! is_array( $latest ) ) {
			return array();
		}

		$results = array();
		if ( isset( $latest['results'] ) ) {
			$results = maybe_unserialize( $latest['results'] );
			if ( ! is_array( $results ) ) {
				$results = array();
			}
		}

		return array(
			'scan_date'           => $latest['scan_date'] ?? '',
			'execution_time'      => $latest['execution_time'] ?? 0,
			'findings_count'      => $latest['findings_count'] ?? 0,
			'critical_count'      => $latest['critical_count'] ?? 0,
			'depth'               => $results['depth'] ?? ( $latest['depth'] ?? '' ),
			'diagnostics_run'     => $results['diagnostics_run'] ?? array(),
			'diagnostics_skipped' => $results['diagnostics_skipped'] ?? array(),
		);
	}

	/**
	 * Get diagnostic map for the next scheduled scan.
	 *
	 * Uses the currently configured depth to build the diagnostic list.
	 *
	 * @since  1.8001.1200
	 * @return array Array of diagnostic slugs mapped to class names.
	 */
	public static function get_next_scan_diagnostics(): array {
		$depth = get_option( self::$settings_prefix . '_depth', 'standard' );
		return self::get_diagnostics_by_depth( $depth );
	}

	/**
	 * Get diagnostic map for a specific depth.
	 *
	 * @since  1.8001.1200
	 * @param  string $depth Scan depth (quick, standard, deep).
	 * @return array Array of diagnostic slugs mapped to class names.
	 */
	public static function get_diagnostics_for_depth( string $depth ): array {
		$depth = sanitize_key( $depth );
		if ( empty( $depth ) ) {
			$depth = 'standard';
		}
		return self::get_diagnostics_by_depth( $depth );
	}

	/**
	 * Get the next scheduled scan time.
	 *
	 * @since  1.8001.1200
	 * @return string Next scan time in Y-m-d H:i:s, or empty string if unknown.
	 */
	public static function get_next_scan_time(): string {
		$next_scan = (string) get_option( self::$settings_prefix . '_next_scan', '' );
		if ( ! empty( $next_scan ) ) {
			return $next_scan;
		}

		$timestamp = wp_next_scheduled( self::$cron_hook );
		if ( $timestamp ) {
			return wp_date( 'Y-m-d H:i:s', $timestamp );
		}

		return '';
	}

	/**
	 * Check if scheduled scans are enabled.
	 *
	 * @since  1.8001.1200
	 * @return bool True when scheduled scans are enabled.
	 */
	public static function is_scheduled_scan_enabled(): bool {
		return (bool) get_option( self::$settings_prefix . '_enabled', true );
	}

	/**
	 * Check if scheduled scans are enabled
	 *
	 * @since  1.6032.1010
	 * @return bool
	 */
	private static function is_enabled(): bool {
		return (bool) get_option( self::$settings_prefix . '_enabled', true );
	}

	/**
	 * Check if should email results
	 *
	 * @since  1.6032.1010
	 * @return bool
	 */
	private static function should_email_results(): bool {
		return (bool) get_option( self::$settings_prefix . '_email_results', false );
	}

	/**
	 * Get frequency setting
	 *
	 * @since  1.6032.1010
	 * @return string
	 */
	private static function get_frequency(): string {
		return get_option( self::$settings_prefix . '_frequency', 'daily' );
	}

	/**
	 * Get frequency label for display
	 *
	 * @since  1.6032.1010
	 * @return string
	 */
	private static function get_frequency_label(): string {
		$frequency = self::get_frequency();
		return array(
			'daily' => __( 'Daily', 'wpshadow' ),
			'weekly' => __( 'Weekly', 'wpshadow' ),
		)[ $frequency ] ?? ucfirst( $frequency );
	}

	/**
	 * Check WordPress cron health
	 *
	 * Displays notice if WordPress cron is not working.
	 *
	 * @since  1.6032.1010
	 * @return void
	 */
	public static function check_cron_health() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Check if user has dismissed this notice
		$dismissed = get_user_meta( get_current_user_id(), 'wpshadow_cron_disabled_notice_dismissed', true );
		if ( ! empty( $dismissed ) ) {
			return;
		}

		// Check if WP_DISABLE_CRON is true
		if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) {
			?>
			<div class="notice notice-info is-dismissible" id="wpshadow-cron-disabled-notice">
				<p>
					<?php
					printf(
						wp_kses_post( __( '<strong>WPShadow:</strong> WordPress cron is disabled. Scans will run via the WordPress Heartbeat API instead (triggered by user activity). For unattended scans, <a href="%s">learn how to enable WP-Cron or use a system cron job</a>.', 'wpshadow' ) ),
						esc_url( 'https://wpshadow.com/kb/wordpress-cron-disabled' )
					);
					?>
				</p>
			</div>
			<script>
			jQuery(document).ready(function($) {
				$('#wpshadow-cron-disabled-notice').on('click', '.notice-dismiss', function() {
					$.ajax({
						url: ajaxurl,
						type: 'POST',
						data: {
							action: 'wpshadow_dismiss_cron_disabled_notice',
							nonce: '<?php echo esc_js( wp_create_nonce( 'wpshadow_dismiss_cron_disabled_notice' ) ); ?>'
						}
					});
				});
			});
			</script>
			<?php
		}
	}

	/**
	 * Handle AJAX request to dismiss cron disabled notice
	 *
	 * @since  1.6032.1748
	 * @return void Dies after sending JSON response.
	 */
	public static function dismiss_cron_disabled_notice() {
		// Verify nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'wpshadow_dismiss_cron_disabled_notice' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed', 'wpshadow' ) ) );
		}

		// Verify capability
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'wpshadow' ) ) );
		}

		// Save dismiss state to user meta
		update_user_meta( get_current_user_id(), 'wpshadow_cron_disabled_notice_dismissed', true );

		wp_send_json_success( array( 'message' => __( 'Notice dismissed', 'wpshadow' ) ) );
	}

	/**
	 * Sanitize boolean
	 *
	 * @since  1.6032.1010
	 * @param  mixed $value Value to sanitize.
	 * @return bool
	 */
	public static function sanitize_boolean( $value ): bool {
		return (bool) $value;
	}

	/**
	 * Sanitize frequency
	 *
	 * @since  1.6032.1010
	 * @param  mixed $value Value to sanitize.
	 * @return string
	 */
	public static function sanitize_frequency( $value ): string {
		$allowed = array( 'daily', 'weekly', 'bi-weekly' );
		return in_array( $value, $allowed, true ) ? $value : 'daily';
	}

	/**
	 * Sanitize time
	 *
	 * @since  1.6032.1010
	 * @param  mixed $value Value to sanitize.
	 * @return string
	 */
	public static function sanitize_time( $value ): string {
		if ( ! preg_match( '/^\d{2}:\d{2}$/', $value ) ) {
			return '02:00';
		}
		return $value;
	}

	/**
	 * Sanitize depth
	 *
	 * @since  1.6032.1010
	 * @param  mixed $value Value to sanitize.
	 * @return string
	 */
	public static function sanitize_depth( $value ): string {
		$allowed = array( 'quick', 'standard', 'deep' );
		return in_array( $value, $allowed, true ) ? $value : 'standard';
	}
}
