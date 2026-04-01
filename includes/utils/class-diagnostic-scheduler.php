<?php

declare(strict_types=1);

namespace WPShadow\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic Scheduler
 *
 * Manages diagnostic run frequencies and scheduling via WordPress Heartbeat system.
 * Frequencies are measured in seconds and can be tied to specific triggers.
 *
 * Philosophy: Shows value (#9) through intelligent scheduling that balances
 * diagnostic thoroughness with performance impact.
 */
class Diagnostic_Scheduler {

	/**
	 * Minimum seconds between Guardian heartbeat batches.
	 *
	 * @since 0.6093.1200
	 * @var int
	 */
	private const HEARTBEAT_MIN_BATCH_INTERVAL = 60;


	/**
	 * Frequency presets (in seconds)
	 */
	const FREQUENCY_EVERY_REQUEST = 0;          // Run on every request
	const FREQUENCY_HOURLY        = 3600;              // 1 hour
	const FREQUENCY_6_HOURS       = 21600;            // 6 hours
	const FREQUENCY_DAILY         = 86400;              // 24 hours
	const FREQUENCY_WEEKLY        = 604800;            // 7 days
	const FREQUENCY_MONTHLY       = 2592000;          // 30 days
	const FREQUENCY_QUARTERLY     = 7776000;        // 90 days

	/**
	 * Trigger types for diagnostics
	 */
	const TRIGGER_PLUGIN_CHANGE  = 'plugin_change';        // On plugin activate/deactivate/update
	const TRIGGER_THEME_CHANGE   = 'theme_change';          // On theme activate/change/update
	const TRIGGER_CORE_UPDATE    = 'core_update';            // On WordPress core update
	const TRIGGER_SETTING_CHANGE = 'setting_change';      // On settings change
	const TRIGGER_HEARTBEAT      = 'heartbeat';                // Via WordPress Heartbeat
	const TRIGGER_SCHEDULED      = 'scheduled';                // Scheduled via WordPress crons
	const TRIGGER_MANUAL         = 'manual';                      // Manual trigger from admin

	/**
	 * Diagnostic frequency definitions
	 *
	 * Format:
	 * 'diagnostic_slug' => [
	 *     'frequency'    => seconds between runs (FREQUENCY_* constant),
	 *     'triggers'     => array of events that should trigger immediate run,
	 *     'priority'     => 'critical', 'high', 'medium', 'low',
	 *     'background'   => true/false (can run in background via heartbeat)
	 * ]
	 */
	protected static $schedule_definitions = array();

	/**
	 * Initialize scheduler
	 */
	public static function init(): void {
		self::$schedule_definitions = self::get_default_schedules();
		add_action( 'wp_loaded', array( __CLASS__, 'register_heartbeat_hooks' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_heartbeat_script' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_heartbeat_script' ) );
		self::register_trigger_hooks();
	}

	/**
	 * Get default diagnostic schedules
	 */
	public static function get_default_schedules(): array {
		return array(
			// Security diagnostics - run frequently
			'admin-email'                  => array(
				'frequency'  => self::FREQUENCY_WEEKLY,
				'triggers'   => array( self::TRIGGER_SETTING_CHANGE ),
				'priority'   => 'critical',
				'background' => false,
			),
			'admin-username'               => array(
				'frequency'  => self::FREQUENCY_WEEKLY,
				'triggers'   => array( self::TRIGGER_SETTING_CHANGE ),
				'priority'   => 'critical',
				'background' => false,
			),
			'ssl'                          => array(
				'frequency'  => self::FREQUENCY_DAILY,
				'triggers'   => array(),
				'priority'   => 'critical',
				'background' => true,
			),
			'https-everywhere'             => array(
				'frequency'  => self::FREQUENCY_DAILY,
				'triggers'   => array(),
				'priority'   => 'high',
				'background' => true,
			),

			// Plugin/Theme security - run on changes + weekly
			'outdated-plugins'             => array(
				'frequency'  => self::FREQUENCY_DAILY,
				'triggers'   => array( self::TRIGGER_PLUGIN_CHANGE ),
				'priority'   => 'high',
				'background' => true,
			),
			'abandoned-plugins'            => array(
				'frequency'  => self::FREQUENCY_WEEKLY,
				'triggers'   => array( self::TRIGGER_PLUGIN_CHANGE ),
				'priority'   => 'high',
				'background' => true,
			),
			'plugin-conflicts-likely'      => array(
				'frequency'  => self::FREQUENCY_DAILY,
				'triggers'   => array( self::TRIGGER_PLUGIN_CHANGE ),
				'priority'   => 'high',
				'background' => true,
			),

			// Database & performance - run daily
			'database-health'              => array(
				'frequency'  => self::FREQUENCY_DAILY,
				'triggers'   => array(),
				'priority'   => 'high',
				'background' => true,
			),
			'database-post-revisions'      => array(
				'frequency'  => self::FREQUENCY_DAILY,
				'triggers'   => array(),
				'priority'   => 'medium',
				'background' => true,
			),
			'autoloaded-options-size'      => array(
				'frequency'  => self::FREQUENCY_DAILY,
				'triggers'   => array(),
				'priority'   => 'medium',
				'background' => true,
			),

			// Backups - run frequently
			'backup'                       => array(
				'frequency'  => self::FREQUENCY_DAILY,
				'triggers'   => array(),
				'priority'   => 'critical',
				'background' => true,
			),
			'core-backups-recent'          => array(
				'frequency'  => self::FREQUENCY_DAILY,
				'triggers'   => array(),
				'priority'   => 'high',
				'background' => true,
			),

			// Performance - run every 6 hours
			'core-homepage-load-time'      => array(
				'frequency'  => self::FREQUENCY_6_HOURS,
				'triggers'   => array(),
				'priority'   => 'high',
				'background' => true,
			),
			'core-response-time-total'     => array(
				'frequency'  => self::FREQUENCY_6_HOURS,
				'triggers'   => array(),
				'priority'   => 'high',
				'background' => true,
			),

			// Content quality - run weekly
			'pub-alt-text-coverage'        => array(
				'frequency'  => self::FREQUENCY_WEEKLY,
				'triggers'   => array(),
				'priority'   => 'medium',
				'background' => true,
			),
			'broken-links'                 => array(
				'frequency'  => self::FREQUENCY_WEEKLY,
				'triggers'   => array(),
				'priority'   => 'medium',
				'background' => true,
			),

			// SEO - run weekly or on content changes
			'seo-missing-meta-description' => array(
				'frequency'  => self::FREQUENCY_WEEKLY,
				'triggers'   => array(),
				'priority'   => 'low',
				'background' => true,
			),
			'seo-missing-h1-tag'           => array(
				'frequency'  => self::FREQUENCY_WEEKLY,
				'triggers'   => array(),
				'priority'   => 'medium',
				'background' => true,
			),

			// Malware - run often
			'database-malware-scanning'    => array(
				'frequency'  => self::FREQUENCY_DAILY,
				'triggers'   => array( self::TRIGGER_PLUGIN_CHANGE, self::TRIGGER_CORE_UPDATE ),
				'priority'   => 'critical',
				'background' => true,
			),

			// RSS & Headers - once a week is fine
			'head-cleanup'                 => array(
				'frequency'  => self::FREQUENCY_WEEKLY,
				'triggers'   => array( self::TRIGGER_THEME_CHANGE ),
				'priority'   => 'low',
				'background' => true,
			),
		);
	}

	/**
	 * Get schedule for a diagnostic
	 */
	public static function get_schedule( string $diagnostic_slug ): ?array {
		$schedule = self::$schedule_definitions[ $diagnostic_slug ] ?? self::get_default_for_new( $diagnostic_slug );

		$overrides = self::get_frequency_overrides();
		if ( isset( $overrides[ $diagnostic_slug ] ) ) {
			$schedule['frequency'] = (int) $overrides[ $diagnostic_slug ];
		}

		return $schedule;
	}

	/**
	 * Get configured frequency overrides.
	 *
	 * @since  0.6091.1200
	 * @return array<string, int> Frequency overrides keyed by diagnostic slug.
	 */
	public static function get_frequency_overrides(): array {
		$stored = get_option( 'wpshadow_diagnostic_frequency_overrides', array() );
		if ( ! is_array( $stored ) ) {
			return array();
		}

		$overrides = array();
		foreach ( $stored as $slug => $frequency ) {
			$clean_slug = sanitize_key( (string) $slug );
			$clean_freq = self::sanitize_frequency_value( (int) $frequency );
			if ( '' !== $clean_slug ) {
				$overrides[ $clean_slug ] = $clean_freq;
			}
		}

		return $overrides;
	}

	/**
	 * Save a frequency override for one diagnostic.
	 *
	 * @since  0.6091.1200
	 * @param  string $diagnostic_slug Diagnostic slug.
	 * @param  int    $frequency       Desired frequency in seconds.
	 * @return int Sanitized stored frequency.
	 */
	public static function set_frequency_override( string $diagnostic_slug, int $frequency ): int {
		$slug = sanitize_key( $diagnostic_slug );
		if ( '' === $slug ) {
			return self::FREQUENCY_WEEKLY;
		}

		$sanitized_frequency = self::sanitize_frequency_value( $frequency );
		$overrides           = self::get_frequency_overrides();
		$overrides[ $slug ]  = $sanitized_frequency;
		update_option( 'wpshadow_diagnostic_frequency_overrides', $overrides );

		return $sanitized_frequency;
	}

	/**
	 * Keep frequency values within allowed presets.
	 *
	 * @since  0.6091.1200
	 * @param  int $frequency Frequency in seconds.
	 * @return int Sanitized frequency preset.
	 */
	private static function sanitize_frequency_value( int $frequency ): int {
		$allowed = array(
			self::FREQUENCY_EVERY_REQUEST,
			self::FREQUENCY_HOURLY,
			self::FREQUENCY_6_HOURS,
			self::FREQUENCY_DAILY,
			self::FREQUENCY_WEEKLY,
			self::FREQUENCY_MONTHLY,
			self::FREQUENCY_QUARTERLY,
		);

		if ( in_array( $frequency, $allowed, true ) ) {
			return $frequency;
		}

		return self::FREQUENCY_WEEKLY;
	}

	/**
	 * Get default schedule for unknown diagnostics
	 */
	protected static function get_default_for_new( string $slug ): array {
		// Categorize by slug patterns
		if ( strpos( $slug, 'malware' ) !== false || strpos( $slug, 'security' ) !== false ) {
			return array(
				'frequency'  => self::FREQUENCY_DAILY,
				'triggers'   => array( self::TRIGGER_PLUGIN_CHANGE, self::TRIGGER_CORE_UPDATE ),
				'priority'   => 'critical',
				'background' => true,
			);
		}

		if ( strpos( $slug, 'ssl' ) !== false || strpos( $slug, 'https' ) !== false || strpos( $slug, 'certificate' ) !== false ) {
			return array(
				'frequency'  => self::FREQUENCY_DAILY,
				'triggers'   => array(),
				'priority'   => 'critical',
				'background' => true,
			);
		}

		if ( strpos( $slug, 'backup' ) !== false ) {
			return array(
				'frequency'  => self::FREQUENCY_DAILY,
				'triggers'   => array(),
				'priority'   => 'critical',
				'background' => true,
			);
		}

		if ( strpos( $slug, 'performance' ) !== false || strpos( $slug, 'perf-' ) === 0 || strpos( $slug, 'load' ) !== false ) {
			return array(
				'frequency'  => self::FREQUENCY_6_HOURS,
				'triggers'   => array(),
				'priority'   => 'high',
				'background' => true,
			);
		}

		if ( strpos( $slug, 'plugin' ) !== false || strpos( $slug, 'theme' ) !== false ) {
			return array(
				'frequency'  => self::FREQUENCY_DAILY,
				'triggers'   => array( self::TRIGGER_PLUGIN_CHANGE, self::TRIGGER_THEME_CHANGE ),
				'priority'   => 'high',
				'background' => true,
			);
		}

		if ( strpos( $slug, 'seo-' ) === 0 || strpos( $slug, 'design-' ) === 0 || strpos( $slug, 'pub-' ) === 0 ) {
			return array(
				'frequency'  => self::FREQUENCY_WEEKLY,
				'triggers'   => array(),
				'priority'   => 'low',
				'background' => true,
			);
		}

		// Default: weekly, medium priority
		return array(
			'frequency'  => self::FREQUENCY_WEEKLY,
			'triggers'   => array(),
			'priority'   => 'medium',
			'background' => true,
		);
	}

	/**
	 * Check if diagnostic should run now
	 */
	public static function should_run( string $diagnostic_slug ): bool {
		$last_run  = get_option( "wpshadow_last_run_{$diagnostic_slug}", 0 );
		$schedule  = self::get_schedule( $diagnostic_slug );
		$frequency = $schedule['frequency'] ?? self::FREQUENCY_DAILY;

		// If frequency is 0 (run on every request), return true
		if ( 0 === $frequency ) {
			return true;
		}

		$time_since_last_run = time() - (int) $last_run;
		return $time_since_last_run >= $frequency;
	}

	/**
	 * Record diagnostic run (uses transient with autoload=false for optimization)
	 */
	public static function record_run( string $diagnostic_slug ): void {
		// Store as option without autoload, transient for 30 days
		update_option( "wpshadow_last_run_{$diagnostic_slug}", time(), false );
	}

	/**
	 * Register WordPress Heartbeat hooks
	 */
	public static function register_heartbeat_hooks(): void {
		add_filter( 'heartbeat_received', array( __CLASS__, 'process_heartbeat' ), 10, 2 );
		add_filter( 'heartbeat_nopriv_received', array( __CLASS__, 'process_heartbeat' ), 10, 2 );
	}

	/**
	 * Ensure the Heartbeat API is available on front-end and admin pages.
	 *
	 * @since 0.6093.1200
	 * @return void
	 */
	public static function enqueue_heartbeat_script(): void {
		if ( function_exists( 'wp_doing_ajax' ) && wp_doing_ajax() ) {
			return;
		}

		if ( function_exists( 'wp_doing_cron' ) && wp_doing_cron() ) {
			return;
		}

		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return;
		}

		if ( function_exists( 'is_feed' ) && is_feed() ) {
			return;
		}

		if ( function_exists( 'is_trackback' ) && is_trackback() ) {
			return;
		}

		if ( function_exists( 'is_robots' ) && is_robots() ) {
			return;
		}

		if ( wp_script_is( 'heartbeat', 'registered' ) ) {
			wp_enqueue_script( 'heartbeat' );
		}
	}

	/**
	 * Process diagnostics via WordPress Heartbeat
	 *
	 * Runs background diagnostics during heartbeat to keep data fresh
	 * without impacting dashboard load time.
	 *
	 * This method is called automatically by WordPress heartbeat and executes
	 * background-safe diagnostics via Guardian_Executor.
	 *
	 * @since 0.6093.1200
	 * @param  array $response Heartbeat response data.
	 * @param  array $data     Heartbeat request data.
	 * @return array Modified heartbeat response with Guardian data.
	 */
	public static function process_heartbeat( array $response, array $data ): array {
		$last_batch_at = (int) get_transient( 'wpshadow_heartbeat_last_batch_at' );
		if ( $last_batch_at > 0 && ( time() - $last_batch_at ) < self::HEARTBEAT_MIN_BATCH_INTERVAL ) {
			return $response;
		}

		if ( get_transient( 'wpshadow_heartbeat_processing_lock' ) ) {
			return $response;
		}

		set_transient( 'wpshadow_heartbeat_processing_lock', 1, 15 );

		try {
			// Ensure diagnostic registries are initialized before execution.
			self::ensure_diagnostic_registries_loaded();

			// Execute background diagnostics via Guardian.
			if ( class_exists( 'WPShadow\Core\Guardian_Executor' ) ) {
				$result = Guardian_Executor::execute_background_diagnostics();
				// Add Guardian data to heartbeat response.
				$response['wpshadow_guardian'] = array(
					'executed'        => $result['executed'],
					'findings_count'  => $result['findings_count'],
					'execution_time'  => $result['execution_time'],
					'diagnostics_run' => $result['diagnostics_run'],
				);
				// Add findings to response if any detected.
				if ( ! empty( $result['findings'] ) ) {
					$response['wpshadow_guardian']['new_findings'] = $result['findings'];
				}
			}

			set_transient( 'wpshadow_heartbeat_last_batch_at', time(), self::HEARTBEAT_MIN_BATCH_INTERVAL );
		} finally {
			delete_transient( 'wpshadow_heartbeat_processing_lock' );
		}

		return $response;
	}

	/**
	 * Ensure diagnostic and treatment registries are loaded
	 *
	 * Called before heartbeat execution to guarantee registries are initialized.
	 * Handles both normal pageload and AJAX/heartbeat contexts.
	 *
	 * @since 0.6093.1200
	 * @return void
	 */
	protected static function ensure_diagnostic_registries_loaded(): void {
		// Initialize Diagnostic_Registry if not already done
		if ( class_exists( '\WPShadow\Diagnostics\Diagnostic_Registry' ) ) {
			// Force initialization
			\WPShadow\Diagnostics\Diagnostic_Registry::init();
		}

		// Initialize Treatment_Registry if not already done
		if ( class_exists( '\WPShadow\Treatments\Treatment_Registry' ) ) {
			// Force initialization
			\WPShadow\Treatments\Treatment_Registry::init();
		}

		// Avoid forcing registry cache clears on every heartbeat tick.
	}

	/**
	 * Register trigger hooks
	 *
	 * These hooks trigger immediate re-runs of specific diagnostics
	 */
	protected static function register_trigger_hooks(): void {
		// Plugin changes
		add_action( 'activated_plugin', array( __CLASS__, 'on_plugin_change' ) );
		add_action( 'deactivated_plugin', array( __CLASS__, 'on_plugin_change' ) );
		add_action( 'upgrader_process_complete', array( __CLASS__, 'on_upgrader_complete' ) );

		// Theme changes
		add_action( 'after_switch_theme', array( __CLASS__, 'on_theme_change' ) );

		// Settings changes
		add_action( 'update_option_siteurl', array( __CLASS__, 'on_setting_change' ) );
		add_action( 'update_option_home', array( __CLASS__, 'on_setting_change' ) );
	}

	/**
	 * Trigger on plugin change
	 */
	public static function on_plugin_change(): void {
		// Reset last run times for plugin-related diagnostics
		delete_option( 'wpshadow_last_run_outdated-plugins' );
		delete_option( 'wpshadow_last_run_abandoned-plugins' );
		delete_option( 'wpshadow_last_run_plugin-conflicts-likely' );
	}

	/**
	 * Trigger on upgrader complete
	 */
	public static function on_upgrader_complete( $upgrader, $options ): void {
		// Reset all last run times to trigger fresh diagnostics using WordPress functions
		$all_options = wp_load_alloptions();

		// Delete all options starting with 'wpshadow_last_run_'
		foreach ( $all_options as $option_name => $option_value ) {
			if ( strpos( $option_name, 'wpshadow_last_run_' ) === 0 ) {
				delete_option( $option_name );
			}
		}
	}

	/**
	 * Trigger on theme change
	 */
	public static function on_theme_change(): void {
		delete_option( 'wpshadow_last_run_head-cleanup' );
		delete_option( 'wpshadow_last_run_core-response-time-total' );
	}

	/**
	 * Trigger on settings change
	 */
	public static function on_setting_change(): void {
		delete_option( 'wpshadow_last_run_admin-email' );
		delete_option( 'wpshadow_last_run_ssl' );
	}

	/**
	 * Get next scheduled run time for diagnostic
	 *
	 * Returns Unix timestamp of when diagnostic should next run
	 */
	public static function get_next_run_time( string $diagnostic_slug ): int {
		$last_run  = get_option( "wpshadow_last_run_{$diagnostic_slug}", 0 );
		$schedule  = self::get_schedule( $diagnostic_slug );
		$frequency = $schedule['frequency'] ?? self::FREQUENCY_DAILY;

		return (int) $last_run + $frequency;
	}

	/**
	 * Get all diagnostics grouped by priority
	 */
	public static function get_by_priority( string $priority = '' ): array {
		$schedules = self::get_default_schedules();

		if ( empty( $priority ) ) {
			return $schedules;
		}

		return array_filter(
			$schedules,
			fn( $config ) => $config['priority'] === $priority
		);
	}

	/**
	 * Get background-safe diagnostics
	 *
	 * These can run during heartbeat without affecting user experience
	 */
	public static function get_background_safe(): array {
		$schedules = self::get_default_schedules();
		return array_filter(
			$schedules,
			fn( $config ) => $config['background'] ?? false
		);
	}
}
