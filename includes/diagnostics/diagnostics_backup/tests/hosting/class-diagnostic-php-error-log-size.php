<?php
/**
 * PHP Error Log Size Diagnostic
 *
 * Detects oversized PHP error logs indicating recurring errors that should be fixed.
 * Large error logs (>100MB) indicate systemic issues that degrade performance.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_PHP_Error_Log_Size Class
 *
 * Monitors PHP error log file size and parses for common error patterns.
 *
 * @since 1.2601.2148
 */
class Diagnostic_PHP_Error_Log_Size extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'php-error-log-size';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'PHP Error Log Size';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects oversized PHP error logs indicating recurring errors';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'hosting';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$log_data = self::analyze_error_log();

		if ( ! $log_data ) {
			return null;
		}

		$log_size_mb   = $log_data['log_size_mb'];
		$log_path      = $log_data['log_path'];
		$error_count   = $log_data['error_count'];
		$top_errors    = $log_data['top_errors'];

		// Thresholds: <10MB good, 10-100MB warning, >100MB critical.
		if ( $log_size_mb < 10 ) {
			return null; // Healthy log size.
		}

		$severity     = 'medium';
		$threat_level = 55;

		if ( $log_size_mb > 100 ) {
			$severity     = 'high';
			$threat_level = 75;
		} elseif ( $log_size_mb > 50 ) {
			$severity     = 'medium';
			$threat_level = 65;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: 1: Log size in MB, 2: Error count */
				__( 'PHP error log is %1$.1fMB (contains approximately %2$s errors). Large error logs indicate recurring issues that degrade performance and should be investigated.', 'wpshadow' ),
				$log_size_mb,
				number_format_i18n( $error_count )
			),
			'severity'    => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/php-error-log-size',
			'details'     => self::get_details( $log_data ),
		);
	}

	/**
	 * Analyze PHP error log.
	 *
	 * Locates error log, measures size, and parses for common error patterns.
	 *
	 * @since  1.2601.2148
	 * @return array|null {
	 *     Error log analysis data.
	 *
	 *     @type float  $log_size_mb  Log file size in MB.
	 *     @type string $log_path     Path to error log.
	 *     @type int    $error_count  Estimated error count.
	 *     @type array  $top_errors   Most common error types.
	 * }
	 */
	private static function analyze_error_log() {
		// Locate PHP error log.
		$log_path = ini_get( 'error_log' );

		// Try common locations if ini_get returns empty.
		if ( empty( $log_path ) || ! file_exists( $log_path ) ) {
			$common_paths = array(
				ABSPATH . 'error_log',
				ABSPATH . 'php_errors.log',
				WP_CONTENT_DIR . '/debug.log',
				'/var/log/php_errors.log',
				'/tmp/php_errors.log',
			);

			foreach ( $common_paths as $path ) {
				if ( file_exists( $path ) ) {
					$log_path = $path;
					break;
				}
			}
		}

		if ( empty( $log_path ) || ! file_exists( $log_path ) ) {
			return null; // No error log found.
		}

		// Get file size.
		$log_size_bytes = filesize( $log_path );
		$log_size_mb    = $log_size_bytes / ( 1024 * 1024 );

		// Estimate error count (average error line ~200 bytes).
		$error_count = round( $log_size_bytes / 200 );

		// Parse last 100KB for error patterns.
		$top_errors = self::parse_error_patterns( $log_path );

		return array(
			'log_size_mb'  => $log_size_mb,
			'log_path'     => $log_path,
			'error_count'  => $error_count,
			'top_errors'   => $top_errors,
		);
	}

	/**
	 * Parse error log for common error patterns.
	 *
	 * Reads last portion of log file to identify frequent error types.
	 *
	 * @since  1.2601.2148
	 * @param  string $log_path Path to error log.
	 * @return array Array of error types with counts.
	 */
	private static function parse_error_patterns( $log_path ) {
		$errors = array();

		try {
			// Read last 100KB of file (most recent errors).
			$file_handle = fopen( $log_path, 'r' );
			if ( ! $file_handle ) {
				return array();
			}

			$file_size = filesize( $log_path );
			$read_size = min( 100 * 1024, $file_size ); // 100KB or file size.

			fseek( $file_handle, max( 0, $file_size - $read_size ) );
			$content = fread( $file_handle, $read_size );
			fclose( $file_handle );

			// Parse for common error types.
			$patterns = array(
				'PHP Warning'       => 0,
				'PHP Notice'        => 0,
				'PHP Fatal error'   => 0,
				'PHP Parse error'   => 0,
				'Undefined variable' => 0,
				'Call to undefined' => 0,
				'Out of memory'     => 0,
			);

			foreach ( $patterns as $pattern => $count ) {
				$patterns[ $pattern ] = substr_count( $content, $pattern );
			}

			// Sort by frequency.
			arsort( $patterns );

			// Return top 5.
			$errors = array_slice( $patterns, 0, 5, true );

		} catch ( \Exception $e ) {
			return array();
		}

		return $errors;
	}

	/**
	 * Get detailed information about the finding.
	 *
	 * @since  1.2601.2148
	 * @param  array $log_data Error log analysis data.
	 * @return array Details array with explanation and solutions.
	 */
	private static function get_details( $log_data ) {
		$log_size_mb = $log_data['log_size_mb'];
		$log_path    = $log_data['log_path'];
		$error_count = $log_data['error_count'];
		$top_errors  = $log_data['top_errors'];

		$error_summary = '';
		if ( ! empty( $top_errors ) ) {
			$error_lines = array();
			foreach ( $top_errors as $type => $count ) {
				if ( $count > 0 ) {
					$error_lines[] = sprintf( '%s (%s occurrences)', $type, number_format_i18n( $count ) );
				}
			}
			$error_summary = implode( ', ', $error_lines );
		}

		$explanation = sprintf(
			/* translators: 1: Log size, 2: Error count, 3: Log path, 4: Error summary */
			__( 'Your PHP error log is %1$.1fMB, containing approximately %2$s errors. Log location: %3$s. Most common errors: %4$s. Large error logs indicate recurring issues that waste disk space, slow down logging, and suggest underlying code problems that need attention.', 'wpshadow' ),
			$log_size_mb,
			number_format_i18n( $error_count ),
			$log_path,
			$error_summary ? $error_summary : __( 'Unable to parse error types', 'wpshadow' )
		);

		$solutions = array(
			'free' => array(
				__( 'Clear error log: Backup and truncate the error log file', 'wpshadow' ),
				__( 'Enable log rotation: Configure PHP log rotation to prevent growth', 'wpshadow' ),
				__( 'Fix recurring errors: Address the most common error types', 'wpshadow' ),
				__( 'Disable error logging: Once issues are fixed, reduce error_reporting level', 'wpshadow' ),
			),
			'premium' => array(
				__( 'Use error monitoring service: Sentry, Rollbar, or New Relic for real-time alerts', 'wpshadow' ),
				__( 'Implement log aggregation: Send logs to external service (Papertrail, Loggly)', 'wpshadow' ),
				__( 'Set up log rotation policy: Automatic weekly/monthly rotation', 'wpshadow' ),
			),
			'advanced' => array(
				__( 'Debug mode analysis: Enable WP_DEBUG temporarily to identify plugin/theme issues', 'wpshadow' ),
				__( 'Audit plugin code: Review plugins generating the most errors', 'wpshadow' ),
				__( 'Configure syslog: Use system-level logging instead of file-based', 'wpshadow' ),
			),
		);

		$additional_info = sprintf(
			/* translators: 1: Recommended size */
			__( 'Recommended: Error log should be <10MB. Logs >100MB indicate systemic issues. Configure log rotation to prevent unlimited growth. Error logs can consume significant disk space and slow server performance.', 'wpshadow' )
		);

		return array(
			'explanation'     => $explanation,
			'solutions'       => $solutions,
			'additional_info' => $additional_info,
			'technical_data'  => array(
				'log_size'         => size_format( $log_size_mb * 1024 * 1024, 2 ),
				'log_path'         => $log_path,
				'estimated_errors' => number_format_i18n( $error_count ),
				'top_errors'       => $top_errors,
				'threshold_warning' => '10-100MB',
				'threshold_critical' => '>100MB',
			),
			'resources'       => array(
				array(
					'label' => __( 'PHP Error Logging', 'wpshadow' ),
					'url'   => 'https://www.php.net/manual/en/errorfunc.configuration.php',
				),
				array(
					'label' => __( 'WordPress Debugging', 'wpshadow' ),
					'url'   => 'https://wordpress.org/support/article/debugging-in-wordpress/',
				),
			),
		);
	}
}
