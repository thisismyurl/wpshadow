<?php
/**
 * Feature: Error Log Detective
 *
 * Displays WordPress error logs in a friendly, plain English format.
 * Translates technical error codes into understandable messages with
 * actionable suggestions for common issues.
 *
 * @package WPShadow\CoreSupport
 * @since 1.2601.75001
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Error Log Detective Feature
 *
 * Parses WordPress debug logs and presents them with:
 * - Plain English explanations
 * - Relative timestamps ("5 minutes ago")
 * - Common issue solutions
 * - Activity History-style interface
 */
final class WPSHADOW_Feature_Error_Log_Detective extends WPSHADOW_Abstract_Feature {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'error-log-detective',
				'name'               => __( 'Error Log Detective', 'wpshadow' ),
				'description_short'  => __( 'Understand WordPress errors in plain English', 'wpshadow' ),
				'description_long'   => __( 'Read and interpret your WordPress debug.log file in plain, non-technical English. Automatically translates PHP errors, warnings, and notices into understandable explanations with helpful suggestions for resolving the most common issues. Displays errors with relative timestamps (like "5 minutes ago") and helpful solutions organized chronologically.', 'wpshadow' ),
				'description_wizard' => __( 'Turn confusing WordPress error codes into simple English explanations with solutions.', 'wpshadow' ),
				'scope'              => 'core',
				'default_enabled'    => false,
				'version'            => '1.2.0',
				'widget_group'       => 'diagnostics',
				'license_level'      => 1,
				'minimum_capability' => 'manage_options',
				'icon'               => 'dashicons-editor-spellcheck',
				'category'           => 'diagnostics',
				'priority'           => 15,
				'aliases'            => array(
					'error logs',
					'debug logs',
					'error messages',
					'plain english',
					'error translation',
					'troubleshooting',
				),
			)
		);
	}

	/**
	 * Indicate this feature has a details page.
	 */
	public function has_details_page(): bool {
		return true;
	}

	/**
	 * Register feature hooks.
	 */
	public function register(): void {
		if ( ! $this->is_enabled() ) {
			return;
		}

		// AJAX handler for loading error logs
		add_action( 'wp_ajax_wpshadow_get_error_logs', array( $this, 'ajax_get_error_logs' ) );
		add_action( 'wp_ajax_nopriv_wpshadow_get_error_logs', array( $this, 'ajax_get_error_logs' ) );

		// AJAX handler for clearing logs
		add_action( 'wp_ajax_wpshadow_clear_error_logs', array( $this, 'ajax_clear_error_logs' ) );
	}

	/**
	 * Get the debug.log file path.
	 *
	 * @return string|false Path to debug.log or false if not found.
	 */
	private function get_debug_log_path(): string|false {
		// WordPress debug.log is typically in wp-content/
		$wp_content = defined( 'WP_CONTENT_DIR' ) ? WP_CONTENT_DIR : ABSPATH . 'wp-content';
		$debug_log = $wp_content . '/debug.log';

		if ( file_exists( $debug_log ) ) {
			return $debug_log;
		}

		return false;
	}

	/**
	 * Parse a single error line from debug.log.
	 *
	 * @param string $line Raw line from debug.log.
	 * @return array|false Error data or false if not parseable.
	 */
	private function parse_error_line( string $line ): array|false {
		// Format: [timestamp] Error Type: Error message in /path/to/file.php on line X
		if ( ! preg_match( '/\[(.*?)\]\s+(.*?Error|Warning|Notice|Deprecated|Parse error|Fatal error):\s*(.+)/i', $line, $matches ) ) {
			return false;
		}

		$timestamp_str = $matches[1];
		$error_type = trim( $matches[2] );
		$message = trim( $matches[3] );

		// Try to parse timestamp
		$timestamp = strtotime( $timestamp_str );
		if ( ! $timestamp ) {
			$timestamp = current_time( 'timestamp' );
		}

		// Extract file and line number if present
		$file = '';
		$line_number = 0;
		if ( preg_match( '/in\s+(.+?)\s+on\s+line\s+(\d+)/', $line, $file_matches ) ) {
			$file = $file_matches[1];
			$line_number = intval( $file_matches[2] );
		}

		return array(
			'timestamp'     => $timestamp,
			'timestamp_str' => $timestamp_str,
			'type'          => $this->normalize_error_type( $error_type ),
			'type_raw'      => $error_type,
			'message'       => $message,
			'file'          => $file,
			'line'          => $line_number,
			'explanation'   => $this->explain_error( $error_type, $message ),
			'suggestion'    => $this->suggest_solution( $error_type, $message ),
		);
	}

	/**
	 * Normalize error type to icon and category.
	 *
	 * @param string $error_type Raw error type from log.
	 * @return string Normalized type for UI.
	 */
	private function normalize_error_type( string $error_type ): string {
		$error_type = strtolower( $error_type );

		if ( strpos( $error_type, 'fatal' ) !== false ) {
			return 'fatal';
		}
		if ( strpos( $error_type, 'parse error' ) !== false ) {
			return 'parse-error';
		}
		if ( strpos( $error_type, 'warning' ) !== false ) {
			return 'warning';
		}
		if ( strpos( $error_type, 'notice' ) !== false ) {
			return 'notice';
		}
		if ( strpos( $error_type, 'deprecated' ) !== false ) {
			return 'deprecated';
		}

		return 'unknown';
	}

	/**
	 * Convert technical error message to plain English.
	 *
	 * @param string $error_type Error type.
	 * @param string $message Error message.
	 * @return string Plain English explanation.
	 */
	private function explain_error( string $error_type, string $message ): string {
		$explanations = array(
			'fatal'       => __( 'A fatal error stopped WordPress from working. This is the most serious type of error.', 'wpshadow' ),
			'parse error' => __( 'There\'s a syntax error in a PHP file. This usually means something is broken in a plugin or theme.', 'wpshadow' ),
			'warning'     => __( 'WordPress is warning you about something that might cause problems. Your site still works, but something needs attention.', 'wpshadow' ),
			'notice'      => __( 'This is informational. Something isn\'t working exactly as expected, but it\'s usually not serious.', 'wpshadow' ),
			'deprecated'  => __( 'An older feature is being used that WordPress no longer recommends. It still works, but you should update it.', 'wpshadow' ),
		);

		// Try to find specific patterns in the message
		if ( strpos( $message, 'undefined' ) !== false ) {
			return __( 'Something is being used that doesn\'t exist. This might be a missing file, function, or setting.', 'wpshadow' );
		}
		if ( strpos( $message, 'Call to undefined function' ) !== false ) {
			return __( 'A plugin or theme is trying to use a function that doesn\'t exist or isn\'t loaded yet.', 'wpshadow' );
		}
		if ( strpos( $message, 'Call to undefined method' ) !== false ) {
			return __( 'A plugin or theme is trying to use a method that doesn\'t exist on this object.', 'wpshadow' );
		}
		if ( strpos( $message, 'Cannot redeclare' ) !== false ) {
			return __( 'Two plugins are trying to use the same function name. They\'re conflicting with each other.', 'wpshadow' );
		}
		if ( strpos( $message, 'headers already sent' ) !== false ) {
			return __( 'Output was sent before it should have been. This usually happens when there\'s extra code before the opening PHP tag.', 'wpshadow' );
		}
		if ( strpos( $message, 'Maximum execution time' ) !== false ) {
			return __( 'A task took too long to complete. Usually a plugin doing something slow or an infinite loop.', 'wpshadow' );
		}
		if ( strpos( $message, 'Allowed memory size' ) !== false ) {
			return __( 'WordPress ran out of memory. A plugin or task is using too much memory.', 'wpshadow' );
		}
		if ( strpos( $message, 'Cannot modify header' ) !== false ) {
			return __( 'A plugin or theme is trying to modify headers after content was already sent.', 'wpshadow' );
		}

		// Return default for error type
		$type_lower = strtolower( $error_type );
		foreach ( $explanations as $key => $explanation ) {
			if ( strpos( $type_lower, $key ) !== false ) {
				return $explanation;
			}
		}

		return __( 'An error occurred that needs attention.', 'wpshadow' );
	}

	/**
	 * Suggest a solution for an error.
	 *
	 * @param string $error_type Error type.
	 * @param string $message Error message.
	 * @return string Suggestion for resolution.
	 */
	private function suggest_solution( string $error_type, string $message ): string {
		// Check for specific error patterns
		if ( strpos( $message, 'Call to undefined function' ) !== false ) {
			return __( '💡 Try: Disable recently activated plugins one at a time to find which one is causing this. Check that all required plugins are active.', 'wpshadow' );
		}
		if ( strpos( $message, 'Cannot redeclare' ) !== false ) {
			return __( '💡 Try: Check your active plugins and theme. Two are likely trying to use the same code. Disable one of them or update both to newer versions.', 'wpshadow' );
		}
		if ( strpos( $message, 'headers already sent' ) !== false ) {
			return __( '💡 Try: Check that your wp-config.php file doesn\'t have extra blank lines or spaces before the opening PHP tag. Also check your theme\'s functions.php file.', 'wpshadow' );
		}
		if ( strpos( $message, 'Maximum execution time' ) !== false ) {
			return __( '💡 Try: Disable plugins one at a time to find which one is slow. Contact your hosting provider about increasing PHP execution time limits if needed.', 'wpshadow' );
		}
		if ( strpos( $message, 'Allowed memory size' ) !== false ) {
			return __( '💡 Try: Increase PHP memory limit by adding `define( \'WP_MEMORY_LIMIT\', \'256M\' );` to wp-config.php. Or disable plugins that use too much memory.', 'wpshadow' );
		}
		if ( strpos( $message, 'Parse error' ) !== false ) {
			return __( '💡 Try: The file has a syntax error. Check for missing semicolons, unmatched brackets, or typos. The error message usually shows the file location.', 'wpshadow' );
		}
		if ( strpos( $message, 'Cannot modify header' ) !== false ) {
			return __( '💡 Try: Check your theme\'s header.php and functions.php files for extra spaces or code before the opening PHP tag.', 'wpshadow' );
		}

		// Generic suggestions by error type
		$type_lower = strtolower( $error_type );
		if ( strpos( $type_lower, 'fatal' ) !== false ) {
			return __( '💡 Try: This is serious. Disable all plugins and see if the error disappears. Then re-enable them one at a time to find the culprit.', 'wpshadow' );
		}
		if ( strpos( $type_lower, 'warning' ) !== false ) {
			return __( '💡 Try: This usually won\'t break your site. Make a note of it and look for plugin updates or themes that might fix it.', 'wpshadow' );
		}
		if ( strpos( $type_lower, 'deprecated' ) !== false ) {
			return __( '💡 Try: Update the plugin or theme causing this. These old features will stop working in future WordPress versions.', 'wpshadow' );
		}

		return __( '💡 Tip: Check your recently activated plugins and your theme. Try disabling them one at a time to isolate the issue.', 'wpshadow' );
	}

	/**
	 * Load and parse recent error logs.
	 *
	 * @param int $limit Maximum number of errors to return.
	 * @return array Array of parsed error log entries.
	 */
	private function load_recent_errors( int $limit = 50 ): array {
		$debug_log = $this->get_debug_log_path();

		if ( ! $debug_log ) {
			return array();
		}

		if ( ! is_readable( $debug_log ) ) {
			return array();
		}

		$file = fopen( $debug_log, 'r' );
		if ( ! $file ) {
			return array();
		}

		$errors = array();
		$line_num = 0;

		// Read file from the end to get recent errors
		fseek( $file, -8192, SEEK_END ); // Start from last 8KB

		while ( ( $line = fgets( $file ) ) !== false ) {
			$parsed = $this->parse_error_line( $line );
			if ( $parsed ) {
				$errors[] = $parsed;
			}

			$line_num++;
			if ( $line_num > 1000 ) {
				break; // Safety limit
			}
		}

		fclose( $file );

		// Sort by timestamp descending (most recent first)
		usort( $errors, function( $a, $b ) {
			return $b['timestamp'] - $a['timestamp'];
		} );

		// Return only the most recent N errors
		return array_slice( $errors, 0, $limit );
	}

	/**
	 * AJAX handler for getting error logs.
	 */
	public function ajax_get_error_logs(): void {
		check_ajax_referer( 'wpshadow_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'wpshadow' ) ) );
		}

		$limit = isset( $_POST['limit'] ) ? intval( $_POST['limit'] ) : 50;
		$errors = $this->load_recent_errors( $limit );

		// Format errors for JSON response
		$formatted_errors = array_map(
			function( $error ) {
				return array(
					'timestamp'      => $error['timestamp'],
					'timestamp_human' => $this->format_relative_time( $error['timestamp'] ),
					'timestamp_full' => $this->format_full_datetime( $error['timestamp'] ),
					'type'           => $error['type'],
					'type_label'     => ucfirst( str_replace( '-', ' ', $error['type'] ) ),
					'message'        => $error['message'],
					'file'           => $error['file'],
					'line'           => $error['line'],
					'explanation'    => $error['explanation'],
					'suggestion'     => $error['suggestion'],
				);
			},
			$errors
		);

		wp_send_json_success(
			array(
				'errors' => $formatted_errors,
				'count'  => count( $formatted_errors ),
				'has_debug_log' => $this->get_debug_log_path() !== false,
			)
		);
	}

	/**
	 * AJAX handler for clearing error logs.
	 */
	public function ajax_clear_error_logs(): void {
		check_ajax_referer( 'wpshadow_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'wpshadow' ) ) );
		}

		$debug_log = $this->get_debug_log_path();

		if ( ! $debug_log ) {
			wp_send_json_error( array( 'message' => __( 'Debug log not found.', 'wpshadow' ) ) );
		}

		if ( ! is_writable( $debug_log ) ) {
			wp_send_json_error( array( 'message' => __( 'Debug log is not writable.', 'wpshadow' ) ) );
		}

		// Clear the log file
		if ( file_put_contents( $debug_log, '' ) !== false ) {
			wp_send_json_success(
				array(
					'message' => __( 'Error log cleared successfully.', 'wpshadow' ),
				)
			);
		} else {
			wp_send_json_error(
				array(
					'message' => __( 'Failed to clear error log.', 'wpshadow' ),
				)
			);
		}
	}

	/**
	 * Format timestamp as relative time (e.g., "5 minutes ago").
	 *
	 * @param int $timestamp Unix timestamp.
	 * @return string Relative time string.
	 */
	private function format_relative_time( int $timestamp ): string {
		$now = current_time( 'timestamp' );
		$diff = $now - $timestamp;

		if ( $diff < 60 ) {
			return __( 'just now', 'wpshadow' );
		}
		if ( $diff < 3600 ) {
			$minutes = intdiv( $diff, 60 );
			return sprintf(
				/* translators: %d is the number of minutes */
				_n( '%d minute ago', '%d minutes ago', $minutes, 'wpshadow' ),
				$minutes
			);
		}
		if ( $diff < 86400 ) {
			$hours = intdiv( $diff, 3600 );
			return sprintf(
				/* translators: %d is the number of hours */
				_n( '%d hour ago', '%d hours ago', $hours, 'wpshadow' ),
				$hours
			);
		}
		if ( $diff < 604800 ) {
			$days = intdiv( $diff, 86400 );
			return sprintf(
				/* translators: %d is the number of days */
				_n( '%d day ago', '%d days ago', $days, 'wpshadow' ),
				$days
			);
		}

		return gmdate( 'M d, Y', $timestamp );
	}

	/**
	 * Format timestamp as full datetime.
	 *
	 * @param int $timestamp Unix timestamp.
	 * @return string Full datetime string.
	 */
	private function format_full_datetime( int $timestamp ): string {
		return gmdate( 'Y-m-d H:i:s', $timestamp );
	}
}
