<?php
/**
 * Cryptic Tool Error Messages Treatment
 *
 * Detects whether tool error messages explain what went wrong and how to fix it.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6030.2148
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cryptic Tool Error Messages Treatment Class
 *
 * Checks error message clarity and actionability.
 *
 * @since 1.6030.2148
 */
class Treatment_Cryptic_Tool_Error_Messages extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'cryptic-tool-error-messages';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Cryptic Tool Error Messages';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Detects unclear error messages without fix guidance';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'tools';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6030.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;
		
		$issues = array();

		// Check error log for cryptic messages.
		$debug_file = WP_CONTENT_DIR . '/debug.log';
		
		if ( file_exists( $debug_file ) && is_readable( $debug_file ) ) {
			$recent_errors = array();
			$handle = @fopen( $debug_file, 'r' );
			
			if ( $handle ) {
				// Read last 100 lines.
				$lines = array();
				while ( ( $line = fgets( $handle ) ) !== false ) {
					$lines[] = $line;
					if ( count( $lines ) > 100 ) {
						array_shift( $lines );
					}
				}
				fclose( $handle );

				// Check for cryptic error patterns.
				$cryptic_patterns = array(
					'Fatal error',
					'Parse error',
					'Warning:',
					'Notice:',
					'Deprecated:',
					'Call to undefined',
					'failed to open stream',
					'out of memory',
				);

				foreach ( $lines as $line ) {
					foreach ( $cryptic_patterns as $pattern ) {
						if ( stripos( $line, $pattern ) !== false ) {
							// Check if line has helpful context.
							if ( ! preg_match( '/(fix|solution|try|check|ensure|verify)/i', $line ) ) {
								++$recent_errors;
								break;
							}
						}
					}
				}

				if ( $recent_errors > 0 ) {
					$issues[] = sprintf(
						/* translators: %d: number of cryptic errors */
						__( '%d cryptic error messages in debug.log (no fix guidance)', 'wpshadow' ),
						$recent_errors
					);
				}
			}
		}

		// Check admin notices for clarity.
		$admin_notices = $GLOBALS['wp_filter']['admin_notices'] ?? null;
		
		if ( $admin_notices && count( $admin_notices->callbacks ) > 10 ) {
			$issues[] = sprintf(
				/* translators: %d: number of notices */
				__( '%d admin notices (may contain unclear messages)', 'wpshadow' ),
				count( $admin_notices->callbacks )
			);
		}

		// Check for error message translation.
		$error_messages = array(
			'wpshadow_import_error',
			'wpshadow_export_error',
			'wpshadow_operation_failed',
		);

		$untranslated = 0;
		foreach ( $error_messages as $message ) {
			$translated = __( $message, 'wpshadow' );
			if ( $translated === $message ) {
				++$untranslated;
			}
		}

		if ( $untranslated > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of untranslated messages */
				__( '%d error messages not translatable', 'wpshadow' ),
				$untranslated
			);
		}

		// Check for error code documentation.
		$error_codes = get_option( 'wpshadow_error_codes' );
		
		if ( false === $error_codes ) {
			$issues[] = __( 'No error code documentation configured', 'wpshadow' );
		}

		// Check for contextual help.
		$help_tabs = $GLOBALS['wp_filter']['contextual_help'] ?? null;
		
		if ( ! $help_tabs || count( $help_tabs->callbacks ) === 0 ) {
			$issues[] = __( 'No contextual help registered for error scenarios', 'wpshadow' );
		}

		// Check for error reporting level.
		$error_reporting = ini_get( 'error_reporting' );
		
		if ( (int) $error_reporting === 0 ) {
			$issues[] = __( 'error_reporting disabled (errors silently hidden)', 'wpshadow' );
		}

		// Check for WP_DEBUG configuration.
		if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
			$issues[] = __( 'WP_DEBUG disabled (detailed error info unavailable)', 'wpshadow' );
		}

		// Check for custom error handlers.
		$error_handler = set_error_handler( 'var_dump' );
		restore_error_handler();
		
		if ( ! $error_handler ) {
			$issues[] = __( 'No custom error handler (using PHP defaults only)', 'wpshadow' );
		}

		// Check for exception handling.
		$exception_handlers = $GLOBALS['wp_filter']['wp_die_handler'] ?? null;
		
		if ( ! $exception_handlers || count( $exception_handlers->callbacks ) === 0 ) {
			$issues[] = __( 'No custom exception handlers registered', 'wpshadow' );
		}

		// Check for error notification system.
		$error_notifications = get_option( 'wpshadow_error_notifications' );
		
		if ( false === $error_notifications ) {
			$issues[] = __( 'No error notification system configured', 'wpshadow' );
		}

		// Check for user-friendly error display.
		$friendly_errors = $GLOBALS['wp_filter']['wpshadow_format_error'] ?? null;
		
		if ( ! $friendly_errors ) {
			$issues[] = __( 'No user-friendly error formatting filter', 'wpshadow' );
		}

		// Check for error logging configuration.
		if ( ! defined( 'WP_DEBUG_LOG' ) || ! WP_DEBUG_LOG ) {
			$issues[] = __( 'WP_DEBUG_LOG disabled (errors not logged to file)', 'wpshadow' );
		}

		// Check for display_errors setting.
		$display_errors = ini_get( 'display_errors' );
		
		if ( '1' === $display_errors || 'On' === $display_errors ) {
			$issues[] = __( 'display_errors enabled (shows technical errors to users)', 'wpshadow' );
		}

		// Check for error message templates.
		$error_templates = locate_template( array( 'error-wpshadow.php', 'error.php' ) );
		
		if ( empty( $error_templates ) ) {
			$issues[] = __( 'No custom error templates (using default WordPress errors)', 'wpshadow' );
		}

		// Check for KB article links in errors.
		$kb_link_filter = has_filter( 'wpshadow_error_kb_link' );
		
		if ( ! $kb_link_filter ) {
			$issues[] = __( 'Error messages do not link to knowledge base', 'wpshadow' );
		}

		// Check for error categorization.
		$error_categories = get_option( 'wpshadow_error_categories' );
		
		if ( false === $error_categories ) {
			$issues[] = __( 'Errors not categorized (all treated equally)', 'wpshadow' );
		}

		// Check for error search functionality.
		$error_search = has_filter( 'wpshadow_search_errors' );
		
		if ( ! $error_search ) {
			$issues[] = __( 'No error search/filter capability', 'wpshadow' );
		}

		// Check for error history.
		$error_history = get_option( 'wpshadow_error_history' );
		
		if ( false === $error_history ) {
			$issues[] = __( 'No error history tracking', 'wpshadow' );
		}

		// Check for error severity levels.
		$severity_config = get_option( 'wpshadow_error_severity' );
		
		if ( false === $severity_config ) {
			$issues[] = __( 'No error severity classification', 'wpshadow' );
		}

		// Check for actionable error suggestions.
		$suggestion_filter = has_filter( 'wpshadow_error_suggestions' );
		
		if ( ! $suggestion_filter ) {
			$issues[] = __( 'Errors lack actionable fix suggestions', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( '. ', $issues ),
				'severity'    => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/cryptic-tool-error-messages',
			);
		}

		return null;
	}
}
