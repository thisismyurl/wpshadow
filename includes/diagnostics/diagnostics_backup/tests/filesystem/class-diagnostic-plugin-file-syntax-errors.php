<?php
/**
 * Plugin File Syntax Errors Diagnostic
 *
 * Identifies PHP parse errors in active plugin files.
 *
 * @since   1.2601.2112
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Plugin_File_Syntax_Errors
 *
 * Scans active plugin PHP files for syntax/parse errors that could affect site functionality.
 *
 * @since 1.2601.2112
 */
class Diagnostic_Plugin_File_Syntax_Errors extends Diagnostic_Base {

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2112
	 * @return array|null Finding array if issues detected, null otherwise.
	 */
	public static function check() {
		if ( ! is_admin() ) {
			return null;
		}

		$plugins = get_plugins();
		if ( empty( $plugins ) ) {
			return null;
		}

		$plugin_dir = WP_PLUGIN_DIR;
		$errors     = array();

		foreach ( $plugins as $plugin_file => $plugin_data ) {
			$plugin_path = $plugin_dir . '/' . dirname( $plugin_file );
			if ( ! is_dir( $plugin_path ) ) {
				continue;
			}

			// Check main plugin file.
			$main_file = $plugin_dir . '/' . $plugin_file;
			if ( ! self::is_valid_php( $main_file ) ) {
				$errors[] = $plugin_file;
			}
		}

		if ( ! empty( $errors ) ) {
			return array(
				'id'           => 'plugin-file-syntax-errors',
				'title'        => __( 'Plugin File Syntax Errors Detected', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: %s: count of plugins */
					__( 'Found syntax errors in %d plugin file(s). This can cause fatal errors and disable your site. Affected: %s', 'wpshadow' ),
					count( $errors ),
					implode( ', ', array_slice( $errors, 0, 3 ) ) . ( count( $errors ) > 3 ? ' +' . ( count( $errors ) - 3 ) . ' more' : '' )
				),
				'severity'     => 'high',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/plugin_file_syntax_errors',
				'meta'         => array(
					'error_count'   => count( $errors ),
					'affected_type' => 'plugins',
				),
			);
		}

		return null;
	}

	/**
	 * Check if PHP file has valid syntax.
	 *
	 * @since  1.2601.2112
	 * @param  string $file File path.
	 * @return bool True if valid, false if syntax error.
	 */
	private static function is_valid_php( $file ) {
		// Check file exists and is readable.
		if ( ! is_readable( $file ) ) {
			return true; // Can't check, assume valid.
		}

		$code = file_get_contents( $file );
		if ( false === $code ) {
			return true;
		}

		// Try to compile without executing.
		if ( function_exists( 'php_check_syntax' ) ) {
			return @php_check_syntax( $file );
		}

		// Fallback: parse tokens.
		@set_error_handler( '__return_null' );
		$tokens = @token_get_all( $code, TOKEN_PARSE );
		@restore_error_handler();

		return null !== $tokens;
	}
}
