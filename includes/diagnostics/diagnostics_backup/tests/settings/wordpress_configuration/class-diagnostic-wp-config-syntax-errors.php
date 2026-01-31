<?php
/**
 * WP Config Syntax Errors Diagnostic
 *
 * Detects PHP syntax errors in wp-config.php file.
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
 * Diagnostic_Wp_Config_Syntax_Errors
 *
 * Checks wp-config.php file for PHP syntax/parse errors that would prevent site loading.
 *
 * @since 1.2601.2112
 */
class Diagnostic_Wp_Config_Syntax_Errors extends Diagnostic_Base {

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

		$config_file = ABSPATH . 'wp-config.php';

		// Verify wp-config.php is readable.
		if ( ! is_readable( $config_file ) ) {
			return array(
				'id'           => 'wp-config-syntax-errors',
				'title'        => __( 'wp-config.php Not Readable', 'wpshadow' ),
				'description'  => __( 'wp-config.php exists but is not readable. Check file permissions.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/wp_config_syntax_errors',
				'meta'         => array(
					'file'       => 'wp-config.php',
					'permission' => 'not-readable',
				),
			);
		}

		// Check syntax without executing.
		if ( ! self::is_valid_php( $config_file ) ) {
			return array(
				'id'           => 'wp-config-syntax-errors',
				'title'        => __( 'wp-config.php Has Syntax Errors', 'wpshadow' ),
				'description'  => __( 'Your wp-config.php file contains PHP syntax errors. This prevents your site from loading. Review recent changes and check for missing quotes, commas, or semicolons.', 'wpshadow' ),
				'severity'     => 'critical',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/wp_config_syntax_errors',
				'meta'         => array(
					'file'  => 'wp-config.php',
					'error' => 'syntax-error',
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
	 * @return bool True if valid syntax, false if errors detected.
	 */
	private static function is_valid_php( $file ) {
		if ( ! is_readable( $file ) ) {
			return false;
		}

		$code = file_get_contents( $file );
		if ( false === $code ) {
			return false;
		}

		// Use php_check_syntax if available.
		if ( function_exists( 'php_check_syntax' ) ) {
			return @php_check_syntax( $file );
		}

		// Fallback: tokenize to check syntax.
		@set_error_handler( '__return_null' );
		$tokens = @token_get_all( $code, TOKEN_PARSE );
		@restore_error_handler();

		return null !== $tokens;
	}
}
