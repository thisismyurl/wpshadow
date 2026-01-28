<?php
/**
 * PHP Error Logging Status Diagnostic
 *
 * Checks if PHP error logging is properly configured for WordPress debugging.
 * Validates WP_DEBUG, WP_DEBUG_LOG, WP_DEBUG_DISPLAY constants and debug.log file status.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6028.1620
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PHP Error Logging Status Diagnostic Class
 *
 * Detects improper PHP error logging configuration that could:
 * - Expose sensitive debug information on production
 * - Prevent error tracking during development
 * - Create security vulnerabilities
 * - Impact performance with excessive logging
 *
 * @since 1.6028.1620
 */
class Diagnostic_PHP_Error_Logging extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @since 1.6028.1620
	 * @var   string
	 */
	protected static $slug = 'php-error-logging';

	/**
	 * The diagnostic title
	 *
	 * @since 1.6028.1620
	 * @var   string
	 */
	protected static $title = 'PHP Error Logging Status';

	/**
	 * The diagnostic description
	 *
	 * @since 1.6028.1620
	 * @var   string
	 */
	protected static $description = 'Validates PHP error logging configuration and debug.log file status';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @since 1.6028.1620
	 * @var   string
	 */
	protected static $family = 'code-quality';

	/**
	 * Cache duration in seconds (1 hour)
	 *
	 * @since 1.6028.1620
	 */
	private const CACHE_DURATION = 3600;

	/**
	 * Maximum debug.log file size before flagging (5MB)
	 *
	 * @since 1.6028.1620
	 */
	private const MAX_DEBUG_LOG_SIZE = 5242880;

	/**
	 * Run the diagnostic check
	 *
	 * Analyzes PHP error logging configuration and debug.log file status.
	 * Flags issues like:
	 * - Debug enabled on production without protection
	 * - Missing error logging on development
	 * - Oversized debug.log files
	 * - Debug display enabled (security risk)
	 *
	 * @since  1.6028.1620
	 * @return array|null Finding array if issues detected, null otherwise.
	 */
	public static function check() {
		// Check transient cache first.
		$cache_key = 'wpshadow_diagnostic_php_error_logging';
		$cached    = get_transient( $cache_key );
		if ( false !== $cached ) {
			return self::evaluate_results( $cached );
		}

		// Analyze error logging configuration.
		$analysis = self::analyze_error_logging();

		// Cache results.
		set_transient( $cache_key, $analysis, self::CACHE_DURATION );

		return self::evaluate_results( $analysis );
	}

	/**
	 * Analyze PHP error logging configuration
	 *
	 * @since  1.6028.1620
	 * @return array Analysis results containing configuration status and issues.
	 */
	private static function analyze_error_logging(): array {
		$analysis = array(
			'wp_debug'              => defined( 'WP_DEBUG' ) && WP_DEBUG,
			'wp_debug_log'          => defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG,
			'wp_debug_display'      => defined( 'WP_DEBUG_DISPLAY' ) && WP_DEBUG_DISPLAY,
			'script_debug'          => defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG,
			'is_production'         => self::is_production_environment(),
			'debug_log_exists'      => false,
			'debug_log_path'        => '',
			'debug_log_size'        => 0,
			'debug_log_writable'    => false,
			'recent_errors'         => 0,
			'issues'                => array(),
			'config_issues'         => array(),
			'has_critical_issues'   => false,
		);

		// Check debug.log file.
		$debug_log_info = self::check_debug_log_file();
		$analysis       = array_merge( $analysis, $debug_log_info );

		// Validate configuration based on environment.
		$analysis = self::validate_configuration( $analysis );

		return $analysis;
	}

	/**
	 * Check if this is a production environment
	 *
	 * @since  1.6028.1620
	 * @return bool True if production environment detected.
	 */
	private static function is_production_environment(): bool {
		// Check environment constant.
		if ( defined( 'WP_ENVIRONMENT_TYPE' ) ) {
			return 'production' === WP_ENVIRONMENT_TYPE || 'prod' === WP_ENVIRONMENT_TYPE;
		}

		// Check domain patterns.
		$site_url = home_url();
		
		// Development indicators.
		$dev_patterns = array( 'localhost', '127.0.0.1', '.local', '.test', '.dev', 'staging' );
		foreach ( $dev_patterns as $pattern ) {
			if ( false !== strpos( $site_url, $pattern ) ) {
				return false;
			}
		}

		// Assume production if no dev indicators found.
		return true;
	}

	/**
	 * Check debug.log file status
	 *
	 * @since  1.6028.1620
	 * @return array Debug log file information.
	 */
	private static function check_debug_log_file(): array {
		$info = array(
			'debug_log_exists'   => false,
			'debug_log_path'     => '',
			'debug_log_size'     => 0,
			'debug_log_writable' => false,
			'recent_errors'      => 0,
		);

		// Determine debug.log path.
		$debug_log_path = WP_CONTENT_DIR . '/debug.log';
		if ( defined( 'WP_DEBUG_LOG' ) && is_string( WP_DEBUG_LOG ) ) {
			$debug_log_path = WP_DEBUG_LOG;
		}

		$info['debug_log_path'] = $debug_log_path;

		// Check if file exists.
		if ( file_exists( $debug_log_path ) ) {
			$info['debug_log_exists']   = true;
			$info['debug_log_size']     = filesize( $debug_log_path );
			$info['debug_log_writable'] = is_writable( $debug_log_path );

			// Count recent errors (last 7 days).
			$info['recent_errors'] = self::count_recent_errors( $debug_log_path );
		} else {
			// Check if directory is writable for log creation.
			$log_dir                    = dirname( $debug_log_path );
			$info['debug_log_writable'] = is_writable( $log_dir );
		}

		return $info;
	}

	/**
	 * Count recent errors in debug.log
	 *
	 * @since  1.6028.1620
	 * @param  string $log_path Path to debug.log file.
	 * @return int Number of error entries in the last 7 days.
	 */
	private static function count_recent_errors( string $log_path ): int {
		if ( ! file_exists( $log_path ) || ! is_readable( $log_path ) ) {
			return 0;
		}

		// For large files, read last 100KB only.
		$file_size = filesize( $log_path );
		$read_size = min( $file_size, 102400 ); // 100KB.
		
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fopen
		$handle = fopen( $log_path, 'r' );
		if ( false === $handle ) {
			return 0;
		}

		// Seek to end minus read size.
		if ( $file_size > $read_size ) {
			fseek( $handle, -$read_size, SEEK_END );
		}

		$content = fread( $handle, $read_size );
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fclose
		fclose( $handle );

		// Count error entries (lines starting with [timestamp]).
		$seven_days_ago = time() - ( 7 * DAY_IN_SECONDS );
		$count          = 0;

		// Match WordPress debug.log format: [DD-MMM-YYYY HH:MM:SS UTC].
		preg_match_all( '/\[(\d{2}-\w{3}-\d{4})\s+(\d{2}:\d{2}:\d{2})\s+UTC\]/', $content, $matches, PREG_SET_ORDER );

		foreach ( $matches as $match ) {
			$timestamp = strtotime( $match[1] . ' ' . $match[2] );
			if ( $timestamp && $timestamp >= $seven_days_ago ) {
				++$count;
			}
		}

		return $count;
	}

	/**
	 * Validate error logging configuration
	 *
	 * @since  1.6028.1620
	 * @param  array $analysis Current analysis data.
	 * @return array Updated analysis with validation results.
	 */
	private static function validate_configuration( array $analysis ): array {
		$issues         = array();
		$config_issues  = array();
		$has_critical   = false;

		// Production environment checks.
		if ( $analysis['is_production'] ) {
			// CRITICAL: Debug display enabled on production.
			if ( $analysis['wp_debug_display'] ) {
				$issues[]        = __( 'WP_DEBUG_DISPLAY is enabled on production (exposes sensitive information)', 'wpshadow' );
				$config_issues[] = 'debug_display_production';
				$has_critical    = true;
			}

			// CRITICAL: Debug enabled without logging.
			if ( $analysis['wp_debug'] && ! $analysis['wp_debug_log'] ) {
				$issues[]        = __( 'WP_DEBUG enabled without WP_DEBUG_LOG (errors not being logged)', 'wpshadow' );
				$config_issues[] = 'debug_without_logging';
			}

			// WARNING: Script debug enabled on production.
			if ( $analysis['script_debug'] ) {
				$issues[]        = __( 'SCRIPT_DEBUG enabled on production (impacts performance)', 'wpshadow' );
				$config_issues[] = 'script_debug_production';
			}

			// WARNING: Debug.log not protected.
			if ( $analysis['debug_log_exists'] && ! self::is_debug_log_protected( $analysis['debug_log_path'] ) ) {
				$issues[]        = __( 'debug.log file is publicly accessible (security risk)', 'wpshadow' );
				$config_issues[] = 'debug_log_unprotected';
				$has_critical    = true;
			}
		} else {
			// Development environment checks.
			// NOTICE: Debugging not enabled on development.
			if ( ! $analysis['wp_debug'] ) {
				$issues[]        = __( 'WP_DEBUG is disabled on development environment', 'wpshadow' );
				$config_issues[] = 'debug_disabled_dev';
			}

			// NOTICE: Error logging not enabled.
			if ( ! $analysis['wp_debug_log'] ) {
				$issues[]        = __( 'WP_DEBUG_LOG is disabled (errors not being logged)', 'wpshadow' );
				$config_issues[] = 'logging_disabled_dev';
			}
		}

		// Environment-agnostic checks.
		// WARNING: Oversized debug.log file.
		if ( $analysis['debug_log_exists'] && $analysis['debug_log_size'] > self::MAX_DEBUG_LOG_SIZE ) {
			$size_mb         = round( $analysis['debug_log_size'] / 1048576, 2 );
			$issues[]        = sprintf(
				/* translators: %s: file size in MB */
				__( 'debug.log file is very large (%sMB) - consider rotating logs', 'wpshadow' ),
				$size_mb
			);
			$config_issues[] = 'oversized_debug_log';
		}

		// WARNING: Debug log not writable.
		if ( $analysis['wp_debug_log'] && ! $analysis['debug_log_writable'] ) {
			$issues[]        = __( 'debug.log file or directory is not writable', 'wpshadow' );
			$config_issues[] = 'debug_log_not_writable';
		}

		// INFO: Many recent errors.
		if ( $analysis['recent_errors'] > 100 ) {
			$issues[]        = sprintf(
				/* translators: %d: number of errors */
				__( '%d errors logged in the last 7 days', 'wpshadow' ),
				$analysis['recent_errors']
			);
			$config_issues[] = 'high_error_count';
		}

		$analysis['issues']              = $issues;
		$analysis['config_issues']       = $config_issues;
		$analysis['has_critical_issues'] = $has_critical;

		return $analysis;
	}

	/**
	 * Check if debug.log is protected from public access
	 *
	 * @since  1.6028.1620
	 * @param  string $log_path Path to debug.log file.
	 * @return bool True if protected, false otherwise.
	 */
	private static function is_debug_log_protected( string $log_path ): bool {
		$log_dir = dirname( $log_path );

		// Check for .htaccess protection.
		$htaccess_path = $log_dir . '/.htaccess';
		if ( file_exists( $htaccess_path ) ) {
			$htaccess_content = file_get_contents( $htaccess_path );
			if ( false !== $htaccess_content ) {
				// Check for deny/forbid rules.
				if ( preg_match( '/deny\s+from\s+all|require\s+all\s+denied|Require\s+all\s+denied/i', $htaccess_content ) ) {
					return true;
				}
			}
		}

		// Check for index.php protection.
		if ( file_exists( $log_dir . '/index.php' ) ) {
			return true;
		}

		// Check if wp-content is protected (common).
		if ( WP_CONTENT_DIR === $log_dir ) {
			$parent_htaccess = WP_CONTENT_DIR . '/.htaccess';
			if ( file_exists( $parent_htaccess ) ) {
				$htaccess_content = file_get_contents( $parent_htaccess );
				if ( false !== $htaccess_content && preg_match( '/deny\s+from\s+all.*\.log/i', $htaccess_content ) ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Evaluate analysis results and build finding
	 *
	 * @since  1.6028.1620
	 * @param  array $analysis Analysis results.
	 * @return array|null Finding array if issues detected, null otherwise.
	 */
	private static function evaluate_results( array $analysis ) {
		// No issues found.
		if ( empty( $analysis['issues'] ) ) {
			return null;
		}

		// Build finding.
		return self::build_finding( $analysis );
	}

	/**
	 * Build finding array
	 *
	 * @since  1.6028.1620
	 * @param  array $analysis Analysis results.
	 * @return array Finding array with full diagnostic information.
	 */
	private static function build_finding( array $analysis ): array {
		$issue_count  = count( $analysis['issues'] );
		$threat_level = self::calculate_threat_level( $analysis );

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: number of issues */
				_n(
					'Found %d PHP error logging configuration issue',
					'Found %d PHP error logging configuration issues',
					$issue_count,
					'wpshadow'
				),
				$issue_count
			),
			'severity'     => $analysis['has_critical_issues'] ? 'high' : 'medium',
			'threat_level' => $threat_level,
			'auto_fixable' => true,
			'kb_link'      => 'https://wpshadow.com/kb/php-error-logging',
			'family'       => self::$family,
			'meta'         => array(
				'issue_count'          => $issue_count,
				'config_issues'        => $analysis['config_issues'],
				'has_critical_issues'  => $analysis['has_critical_issues'],
				'is_production'        => $analysis['is_production'],
				'wp_debug'             => $analysis['wp_debug'],
				'wp_debug_log'         => $analysis['wp_debug_log'],
				'wp_debug_display'     => $analysis['wp_debug_display'],
				'debug_log_exists'     => $analysis['debug_log_exists'],
				'debug_log_size'       => $analysis['debug_log_size'],
				'debug_log_size_human' => size_format( $analysis['debug_log_size'] ),
				'recent_errors'        => $analysis['recent_errors'],
			),
			'details'      => self::build_finding_details( $analysis ),
		);
	}

	/**
	 * Calculate threat level based on configuration issues
	 *
	 * @since  1.6028.1620
	 * @param  array $analysis Analysis results.
	 * @return int Threat level (25-75).
	 */
	private static function calculate_threat_level( array $analysis ): int {
		$threat_level = 25; // Base threat level.

		// Critical issues add significant threat.
		if ( $analysis['has_critical_issues'] ) {
			$threat_level += 30;
		}

		// Production environment with issues is more severe.
		if ( $analysis['is_production'] ) {
			$threat_level += 10;
		}

		// High error count indicates active problems.
		if ( $analysis['recent_errors'] > 100 ) {
			$threat_level += 10;
		}

		// Oversized debug log.
		if ( $analysis['debug_log_size'] > self::MAX_DEBUG_LOG_SIZE ) {
			$threat_level += 5;
		}

		return min( $threat_level, 75 );
	}

	/**
	 * Build detailed information for finding
	 *
	 * @since  1.6028.1620
	 * @param  array $analysis Analysis results.
	 * @return array Detailed information array.
	 */
	private static function build_finding_details( array $analysis ): array {
		$details = array(
			'issues_found'       => $analysis['issues'],
			'current_config'     => array(
				'WP_DEBUG'         => $analysis['wp_debug'] ? 'enabled' : 'disabled',
				'WP_DEBUG_LOG'     => $analysis['wp_debug_log'] ? 'enabled' : 'disabled',
				'WP_DEBUG_DISPLAY' => $analysis['wp_debug_display'] ? 'enabled' : 'disabled',
				'SCRIPT_DEBUG'     => $analysis['script_debug'] ? 'enabled' : 'disabled',
				'Environment'      => $analysis['is_production'] ? 'production' : 'development',
			),
			'recommended_config' => array(),
			'why_this_matters'   => __( 'Proper error logging configuration is critical for security, debugging, and maintaining a professional site. Exposing debug information on production can reveal sensitive details about your site\'s structure, plugins, and potential vulnerabilities to attackers.', 'wpshadow' ),
			'next_steps'         => array(),
		);

		// Recommended configuration based on environment.
		if ( $analysis['is_production'] ) {
			$details['recommended_config'] = array(
				'WP_DEBUG'         => 'enabled (for logging only)',
				'WP_DEBUG_LOG'     => 'enabled (to track errors)',
				'WP_DEBUG_DISPLAY' => 'disabled (never show errors publicly)',
				'SCRIPT_DEBUG'     => 'disabled (use minified assets)',
			);
			$details['next_steps']         = array(
				__( 'Disable WP_DEBUG_DISPLAY immediately if enabled', 'wpshadow' ),
				__( 'Enable WP_DEBUG_LOG to track errors silently', 'wpshadow' ),
				__( 'Protect debug.log from public access via .htaccess', 'wpshadow' ),
				__( 'Set up log rotation to prevent oversized files', 'wpshadow' ),
				__( 'Monitor debug.log regularly for recurring issues', 'wpshadow' ),
			);
		} else {
			$details['recommended_config'] = array(
				'WP_DEBUG'         => 'enabled',
				'WP_DEBUG_LOG'     => 'enabled',
				'WP_DEBUG_DISPLAY' => 'enabled (safe on development)',
				'SCRIPT_DEBUG'     => 'enabled (for debugging assets)',
			);
			$details['next_steps']         = array(
				__( 'Enable WP_DEBUG to catch errors during development', 'wpshadow' ),
				__( 'Enable WP_DEBUG_LOG to log all errors', 'wpshadow' ),
				__( 'Review debug.log regularly for issues', 'wpshadow' ),
				__( 'Ensure proper configuration before deploying to production', 'wpshadow' ),
			);
		}

		// Add debug.log info if exists.
		if ( $analysis['debug_log_exists'] ) {
			$details['debug_log_info'] = array(
				'path'         => $analysis['debug_log_path'],
				'size'         => size_format( $analysis['debug_log_size'] ),
				'writable'     => $analysis['debug_log_writable'] ? 'yes' : 'no',
				'recent_errors' => $analysis['recent_errors'],
			);
		}

		return $details;
	}
}
