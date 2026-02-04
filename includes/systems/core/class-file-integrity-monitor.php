<?php
/**
 * File Integrity Monitor
 *
 * Detects unauthorized modifications to plugin files by comparing current
 * file hashes against known-good values. Alerts administrators to potential
 * security breaches or compromised installations.
 *
 * **Detection Methods:**
 * - SHA-256 hash comparison of critical files
 * - Baseline hash creation on plugin activation
 * - Scheduled daily integrity checks
 * - Manual scan via dashboard
 * - Alert on modification detection
 *
 * **Philosophy Alignment:**
 * - #10 (Beyond Pure): Proactive security monitoring
 * - #8 (Inspire Confidence): Users know files haven't been tampered with
 * - #1 (Helpful Neighbor): Clear guidance on remediation
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
 * File Integrity Monitor Class
 *
 * Monitors plugin files for unauthorized changes.
 *
 * @since 1.6035.0948
 */
class File_Integrity_Monitor {

	/**
	 * Option name for storing file hashes
	 *
	 * @var string
	 */
	const HASH_OPTION = 'wpshadow_file_hashes';

	/**
	 * Critical files to monitor
	 *
	 * @var array<string>
	 */
	private static $critical_files = array(
		'wpshadow.php',
		'includes/systems/core/class-ajax-handler-base.php',
		'includes/systems/core/class-security-validator.php',
		'includes/systems/core/class-treatment-base.php',
		'includes/systems/core/class-diagnostic-base.php',
		'includes/systems/treatments/class-treatment-registry.php',
		'includes/diagnostics/class-diagnostic-registry.php',
	);

	/**
	 * Initialize file integrity monitoring.
	 *
	 * @since  1.6035.0948
	 * @return void
	 */
	public static function init(): void {
		// Create baseline on plugin activation
		register_activation_hook( WPSHADOW_BASENAME, array( __CLASS__, 'create_baseline' ) );

		// Schedule daily integrity check
		if ( ! wp_next_scheduled( 'wpshadow_file_integrity_check' ) ) {
			wp_schedule_event( time(), 'daily', 'wpshadow_file_integrity_check' );
		}

		add_action( 'wpshadow_file_integrity_check', array( __CLASS__, 'run_integrity_check' ) );
	}

	/**
	 * Create baseline hash database.
	 *
	 * Scans all plugin files and stores their SHA-256 hashes.
	 *
	 * @since  1.6035.0948
	 * @return array {
	 *     Baseline creation result.
	 *
	 *     @type bool   $success Whether baseline created.
	 *     @type int    $files   Number of files hashed.
	 *     @type string $message Result message.
	 * }
	 */
	public static function create_baseline(): array {
		$plugin_dir = WPSHADOW_PATH;
		$hashes     = array();
		$file_count = 0;

		// Get all PHP files in plugin
		$files = self::get_plugin_files();

		foreach ( $files as $file ) {
			$full_path = $plugin_dir . $file;
			
			if ( ! file_exists( $full_path ) || ! is_readable( $full_path ) ) {
				continue;
			}

			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			$content = file_get_contents( $full_path );
			if ( false === $content ) {
				continue;
			}

			$hashes[ $file ] = hash( 'sha256', $content );
			$file_count++;
		}

		// Store hashes with timestamp
		$baseline = array(
			'created'   => time(),
			'version'   => WPSHADOW_VERSION,
			'hashes'    => $hashes,
			'file_count' => $file_count,
		);

		update_option( self::HASH_OPTION, $baseline, false );

		return array(
			'success' => true,
			'files'   => $file_count,
			'message' => sprintf(
				/* translators: %d: number of files */
				__( 'Baseline created for %d plugin files', 'wpshadow' ),
				$file_count
			),
		);
	}

	/**
	 * Run integrity check against baseline.
	 *
	 * @since  1.6035.0948
	 * @return array {
	 *     Integrity check results.
	 *
	 *     @type bool  $passed      Whether integrity check passed.
	 *     @type array $modified    Modified files.
	 *     @type array $missing     Missing files.
	 *     @type array $unexpected  New/unexpected files.
	 *     @type int   $checked     Files checked.
	 * }
	 */
	public static function run_integrity_check(): array {
		$baseline = get_option( self::HASH_OPTION, array() );

		if ( empty( $baseline ) || ! isset( $baseline['hashes'] ) ) {
			// No baseline exists - create one
			return self::create_baseline();
		}

		$plugin_dir = WPSHADOW_PATH;
		$modified   = array();
		$missing    = array();
		$unexpected = array();
		$checked    = 0;

		// Check baseline files for modifications
		foreach ( $baseline['hashes'] as $file => $expected_hash ) {
			$full_path = $plugin_dir . $file;

			if ( ! file_exists( $full_path ) ) {
				$missing[] = $file;
				continue;
			}

			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			$content = file_get_contents( $full_path );
			if ( false === $content ) {
				continue;
			}

			$current_hash = hash( 'sha256', $content );

			if ( $current_hash !== $expected_hash ) {
				$modified[] = array(
					'file'          => $file,
					'expected_hash' => $expected_hash,
					'current_hash'  => $current_hash,
				);
			}

			$checked++;
		}

		// Check for unexpected files
		$current_files = self::get_plugin_files();
		$baseline_files = array_keys( $baseline['hashes'] );
		$new_files = array_diff( $current_files, $baseline_files );

		foreach ( $new_files as $file ) {
			// Ignore transient/cache files
			if ( self::is_transient_file( $file ) ) {
				continue;
			}
			$unexpected[] = $file;
		}

		$passed = empty( $modified ) && empty( $missing ) && empty( $unexpected );

		// Log security event if integrity compromised
		if ( ! $passed ) {
			self::log_integrity_failure( $modified, $missing, $unexpected );
		}

		return array(
			'passed'     => $passed,
			'modified'   => $modified,
			'missing'    => $missing,
			'unexpected' => $unexpected,
			'checked'    => $checked,
		);
	}

	/**
	 * Get user-friendly integrity report.
	 *
	 * Philosophy #1 (Helpful Neighbor): Explain what happened and how to fix.
	 *
	 * @since  1.6035.0948
	 * @param  array $check_result Result from run_integrity_check().
	 * @return string HTML report.
	 */
	public static function get_integrity_report( array $check_result ): string {
		if ( $check_result['passed'] ) {
			return sprintf(
				'<div class="notice notice-success"><p>%s</p></div>',
				esc_html__( '✅ All plugin files verified. No unauthorized modifications detected.', 'wpshadow' )
			);
		}

		$report = '<div class="notice notice-error"><p><strong>' . esc_html__( '⚠️ File Integrity Warning', 'wpshadow' ) . '</strong></p>';

		if ( ! empty( $check_result['modified'] ) ) {
			$report .= '<p>' . esc_html__( 'Modified files detected:', 'wpshadow' ) . '</p><ul>';
			foreach ( $check_result['modified'] as $file ) {
				$report .= '<li><code>' . esc_html( $file['file'] ) . '</code></li>';
			}
			$report .= '</ul>';
		}

		if ( ! empty( $check_result['missing'] ) ) {
			$report .= '<p>' . esc_html__( 'Missing files:', 'wpshadow' ) . '</p><ul>';
			foreach ( $check_result['missing'] as $file ) {
				$report .= '<li><code>' . esc_html( $file ) . '</code></li>';
			}
			$report .= '</ul>';
		}

		if ( ! empty( $check_result['unexpected'] ) ) {
			$report .= '<p>' . esc_html__( 'Unexpected files:', 'wpshadow' ) . '</p><ul>';
			foreach ( $check_result['unexpected'] as $file ) {
				$report .= '<li><code>' . esc_html( $file ) . '</code></li>';
			}
			$report .= '</ul>';
		}

		$report .= '<p><strong>' . esc_html__( 'Recommended Actions:', 'wpshadow' ) . '</strong></p>';
		$report .= '<ol>';
		$report .= '<li>' . esc_html__( 'Verify these changes were intentional (plugin update, manual edit)', 'wpshadow' ) . '</li>';
		$report .= '<li>' . esc_html__( 'If unauthorized: Immediately deactivate plugin and run malware scan', 'wpshadow' ) . '</li>';
		$report .= '<li>' . esc_html__( 'Reinstall plugin from WordPress.org to restore clean files', 'wpshadow' ) . '</li>';
		$report .= '<li>' . esc_html__( 'Review server access logs for unauthorized access', 'wpshadow' ) . '</li>';
		$report .= '<li>' . esc_html__( 'Update baseline after legitimate changes using "Reset Baseline" button', 'wpshadow' ) . '</li>';
		$report .= '</ol>';
		$report .= '</div>';

		return $report;
	}

	/**
	 * Get all plugin PHP files (recursive).
	 *
	 * @since  1.6035.0948
	 * @return array<string> Relative file paths.
	 */
	private static function get_plugin_files(): array {
		$plugin_dir = WPSHADOW_PATH;
		$files      = array();

		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator( $plugin_dir, \RecursiveDirectoryIterator::SKIP_DOTS )
		);

		foreach ( $iterator as $file ) {
			if ( $file->isFile() && 'php' === $file->getExtension() ) {
				$relative_path = str_replace( $plugin_dir, '', $file->getPathname() );
				
				// Skip vendor, tests, node_modules
				if ( self::should_monitor_file( $relative_path ) ) {
					$files[] = ltrim( $relative_path, '/' );
				}
			}
		}

		return $files;
	}

	/**
	 * Check if file should be monitored.
	 *
	 * @since  1.6035.0948
	 * @param  string $file Relative file path.
	 * @return bool True if should monitor.
	 */
	private static function should_monitor_file( string $file ): bool {
		$excluded_patterns = array(
			'/vendor/',
			'/node_modules/',
			'/tests/',
			'/dev-tools/',
			'/.git/',
		);

		foreach ( $excluded_patterns as $pattern ) {
			if ( false !== strpos( $file, $pattern ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Check if file is transient/cache.
	 *
	 * @since  1.6035.0948
	 * @param  string $file Relative file path.
	 * @return bool True if transient.
	 */
	private static function is_transient_file( string $file ): bool {
		$transient_patterns = array(
			'/cache/',
			'/tmp/',
			'.log',
			'.bak',
		);

		foreach ( $transient_patterns as $pattern ) {
			if ( false !== strpos( $file, $pattern ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Log integrity failure for security monitoring.
	 *
	 * @since  1.6035.0948
	 * @param  array $modified   Modified files.
	 * @param  array $missing    Missing files.
	 * @param  array $unexpected Unexpected files.
	 * @return void
	 */
	private static function log_integrity_failure( array $modified, array $missing, array $unexpected ): void {
		if ( class_exists( 'WPShadow\Core\Activity_Logger' ) ) {
			Activity_Logger::log(
				'security_file_integrity_failure',
				array(
					'modified'   => count( $modified ),
					'missing'    => count( $missing ),
					'unexpected' => count( $unexpected ),
					'severity'   => 'critical',
					'details'    => array(
						'modified_files'   => wp_list_pluck( $modified, 'file' ),
						'missing_files'    => $missing,
						'unexpected_files' => $unexpected,
					),
				)
			);
		}

		/**
		 * Fires when file integrity check fails.
		 *
		 * @since 1.6035.0948
		 *
		 * @param array $modified   Modified files.
		 * @param array $missing    Missing files.
		 * @param array $unexpected Unexpected files.
		 */
		do_action( 'wpshadow_file_integrity_failure', $modified, $missing, $unexpected );

		// Send admin email alert
		self::send_integrity_alert( $modified, $missing, $unexpected );
	}

	/**
	 * Send email alert to site administrator.
	 *
	 * @since  1.6035.0948
	 * @param  array $modified   Modified files.
	 * @param  array $missing    Missing files.
	 * @param  array $unexpected Unexpected files.
	 * @return void
	 */
	private static function send_integrity_alert( array $modified, array $missing, array $unexpected ): void {
		$admin_email = get_option( 'admin_email' );
		$site_name   = get_option( 'blogname' );

		$subject = sprintf(
			/* translators: %s: site name */
			__( '[Security Alert] File Integrity Check Failed - %s', 'wpshadow' ),
			$site_name
		);

		$message = __( 'WPShadow has detected unauthorized file modifications on your WordPress site.', 'wpshadow' ) . "\n\n";

		if ( ! empty( $modified ) ) {
			$message .= sprintf(
				/* translators: %d: number of files */
				_n( '%d file was modified', '%d files were modified', count( $modified ), 'wpshadow' ),
				count( $modified )
			) . "\n";
		}

		if ( ! empty( $missing ) ) {
			$message .= sprintf(
				/* translators: %d: number of files */
				_n( '%d file is missing', '%d files are missing', count( $missing ), 'wpshadow' ),
				count( $missing )
			) . "\n";
		}

		if ( ! empty( $unexpected ) ) {
			$message .= sprintf(
				/* translators: %d: number of files */
				_n( '%d unexpected file found', '%d unexpected files found', count( $unexpected ), 'wpshadow' ),
				count( $unexpected )
			) . "\n";
		}

		$message .= "\n" . __( 'Immediate Actions Required:', 'wpshadow' ) . "\n";
		$message .= '1. ' . __( 'Log into your WordPress dashboard', 'wpshadow' ) . "\n";
		$message .= '2. ' . __( 'Review the File Integrity Report in WPShadow > Security', 'wpshadow' ) . "\n";
		$message .= '3. ' . __( 'If unauthorized: Deactivate plugin and run malware scan', 'wpshadow' ) . "\n";
		$message .= '4. ' . __( 'Reinstall plugin from WordPress.org if compromised', 'wpshadow' ) . "\n\n";

		$message .= sprintf(
			/* translators: %s: site URL */
			__( 'View dashboard: %s', 'wpshadow' ),
			admin_url( 'admin.php?page=wpshadow-security' )
		);

		wp_mail( $admin_email, $subject, $message );
	}
}
