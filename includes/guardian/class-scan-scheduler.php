<?php
/**
 * Scheduled Scans Manager
 *
 * Manages automatic scheduled diagnostic scans using WordPress cron.
 * Runs health checks on configurable schedule (daily/weekly).
 * Stores results and logs scan activity.
 *
 * @since   1.26032.1010
 * @package WPShadow\Guardian
 */

declare(strict_types=1);

namespace WPShadow\Guardian;

use WPShadow\Core\Activity_Logger;
use WPShadow\Core\Settings_Registry;
use WPShadow\Core\Diagnostic_Registry;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Scan_Scheduler Class
 *
 * Handles automated diagnostic scans on a configurable schedule.
 *
 * @since 1.26032.1010
 */
class Scan_Scheduler {

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
	 * Initialize scheduled scans system
	 *
	 * @since  1.26032.1010
	 * @return void
	 */
	public static function init() {
		// Register settings
		add_action( 'wpshadow_register_settings', array( __CLASS__, 'register_settings' ) );

		// Register cron hook
		add_action( self::$cron_hook, array( __CLASS__, 'execute_scheduled_scan' ) );

		// Setup/clear cron on activation/deactivation
		register_activation_hook( \WPSHADOW_FILE, array( __CLASS__, 'schedule_cron' ) );
		register_deactivation_hook( \WPSHADOW_FILE, array( __CLASS__, 'unschedule_cron' ) );

		// Check cron status
		add_action( 'admin_notices', array( __CLASS__, 'check_cron_health' ) );
	}

	/**
	 * Register scheduled scan settings
	 *
	 * @since  1.26032.1010
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
	 * @since  1.26032.1010
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
			array(
				'frequency' => self::get_frequency_label(),
				'next_run'  => wp_date( 'Y-m-d H:i:s', $next_run ),
			)
		);
	}

	/**
	 * Unschedule cron event
	 *
	 * Called on plugin deactivation to remove WordPress cron event.
	 *
	 * @since  1.26032.1010
	 * @return void
	 */
	public static function unschedule_cron() {
		wp_clear_scheduled_hook( self::$cron_hook );

		Activity_Logger::log(
			'scheduled_scan_disabled',
			array(
				'reason' => 'plugin_deactivation',
			)
		);
	}

	/**
	 * Execute scheduled scan
	 *
	 * Runs the diagnostic scan at the scheduled time.
	 *
	 * @since  1.26032.1010
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
		Activity_Logger::log(
			'scheduled_scan_completed',
			array(
				'execution_time' => round( $execution_time, 2 ),
				'findings_count' => count( $results['findings'] ?? array() ),
				'critical_count' => count( array_filter( $results['findings'] ?? array(), function( $f ) {
					return ( $f['severity'] ?? 'low' ) === 'critical';
				} ) ),
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
	 * @since  1.26032.1010
	 * @return array Results of all diagnostics.
	 */
	private static function run_diagnostics(): array {
		$depth = get_option( self::$settings_prefix . '_depth', 'standard' );
		$max_time = get_option( self::$settings_prefix . '_max_time', 300 );

		// Set max execution time
		set_time_limit( $max_time );

		$findings = array();
		$skipped = array();

		// Get diagnostics to run based on depth
		$diagnostics = self::get_diagnostics_by_depth( $depth );

		foreach ( $diagnostics as $slug => $class ) {
			// Check time limit
			if ( function_exists( 'get_time_limit' ) && microtime( true ) > $max_time ) {
				$skipped[] = $slug;
				continue;
			}

			try {
				// Run diagnostic
				if ( class_exists( $class ) && method_exists( $class, 'execute' ) ) {
					$result = call_user_func( array( $class, 'execute' ) );

					if ( $result ) {
						$findings[] = array_merge( $result, array(
							'detected_at' => current_time( 'mysql' ),
							'source'      => 'scheduled_scan',
						) );
					}
				}
			} catch ( \Exception $e ) {
				Activity_Logger::log(
					'scheduled_scan_error',
					array(
						'diagnostic' => $slug,
						'error'      => $e->getMessage(),
					)
				);
			}
		}

		return array(
			'findings' => $findings,
			'skipped'  => $skipped,
			'depth'    => $depth,
		);
	}

	/**
	 * Get diagnostics based on scan depth
	 *
	 * @since  1.26032.1010
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
	 * @since  1.26032.1010
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
	 * @since  1.26032.1010
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
	 * @since  1.26032.1010
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
	 * @since  1.26032.1010
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
	 * Check if scheduled scans are enabled
	 *
	 * @since  1.26032.1010
	 * @return bool
	 */
	private static function is_enabled(): bool {
		return (bool) get_option( self::$settings_prefix . '_enabled', true );
	}

	/**
	 * Check if should email results
	 *
	 * @since  1.26032.1010
	 * @return bool
	 */
	private static function should_email_results(): bool {
		return (bool) get_option( self::$settings_prefix . '_email_results', false );
	}

	/**
	 * Get frequency setting
	 *
	 * @since  1.26032.1010
	 * @return string
	 */
	private static function get_frequency(): string {
		return get_option( self::$settings_prefix . '_frequency', 'daily' );
	}

	/**
	 * Get frequency label for display
	 *
	 * @since  1.26032.1010
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
	 * @since  1.26032.1010
	 * @return void
	 */
	public static function check_cron_health() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Check if WP_DISABLE_CRON is true
		if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) {
			?>
			<div class="notice notice-info">
				<p>
					<?php
					printf(
						wp_kses_post( __( '<strong>WPShadow:</strong> WordPress cron is disabled. Scans will run via the WordPress Heartbeat API instead (triggered by user activity). For unattended scans, <a href="%s">learn how to enable WP-Cron or use a system cron job</a>.', 'wpshadow' ) ),
						esc_url( 'https://wpshadow.com/kb/wordpress-cron-disabled' )
					);
					?>
				</p>
			</div>
			<?php
		}
	}

	/**
	 * Sanitize boolean
	 *
	 * @since  1.26032.1010
	 * @param  mixed $value Value to sanitize.
	 * @return bool
	 */
	public static function sanitize_boolean( $value ): bool {
		return (bool) $value;
	}

	/**
	 * Sanitize frequency
	 *
	 * @since  1.26032.1010
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
	 * @since  1.26032.1010
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
	 * @since  1.26032.1010
	 * @param  mixed $value Value to sanitize.
	 * @return string
	 */
	public static function sanitize_depth( $value ): string {
		$allowed = array( 'quick', 'standard', 'deep' );
		return in_array( $value, $allowed, true ) ? $value : 'standard';
	}
}
