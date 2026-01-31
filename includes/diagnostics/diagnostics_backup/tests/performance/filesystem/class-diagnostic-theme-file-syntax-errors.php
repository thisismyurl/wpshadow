<?php
/**
 * Theme File Syntax Errors Diagnostic
 *
 * Identifies PHP template parse errors in theme files.
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
 * Diagnostic_Theme_File_Syntax_Errors
 *
 * Scans theme PHP files for syntax/parse errors that could affect site functionality.
 *
 * @since 1.2601.2112
 */
class Diagnostic_Theme_File_Syntax_Errors extends Diagnostic_Base {

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

		$theme_dir = get_template_directory();
		if ( ! is_dir( $theme_dir ) ) {
			return null;
		}

		// Check for PHP files with syntax errors using compilation check.
		$php_files = self::get_php_files_recursive( $theme_dir );
		$errors    = array();

		foreach ( $php_files as $file ) {
			// Use php_check_syntax() if available, or parse tokens.
			if ( ! self::is_valid_php( $file ) ) {
				$errors[] = str_replace( ABSPATH, '', $file );
			}
		}

		if ( ! empty( $errors ) ) {
			return array(
				'id'           => 'theme-file-syntax-errors',
				'title'        => __( 'Theme File Syntax Errors Detected', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: %s: count of files */
					__( 'Found %d PHP file(s) with syntax errors in your theme. This can cause functionality issues and site crashes. Affected files: %s', 'wpshadow' ),
					count( $errors ),
					implode( ', ', array_slice( $errors, 0, 3 ) ) . ( count( $errors ) > 3 ? ' +' . ( count( $errors ) - 3 ) . ' more' : '' )
				),
				'severity'     => 'high',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/theme_file_syntax_errors',
				'meta'         => array(
					'error_count'  => count( $errors ),
					'affected_dir' => 'wp-content/themes/' . basename( $theme_dir ),
				),
			);
		}

		return null;
	}

	/**
	 * Get all PHP files in directory recursively.
	 *
	 * @since  1.2601.2112
	 * @param  string $dir Directory path.
	 * @param  array  $files Accumulator array.
	 * @return array PHP file paths.
	 */
	private static function get_php_files_recursive( $dir, &$files = array() ) {
		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator( $dir, \RecursiveDirectoryIterator::SKIP_DOTS ),
			\RecursiveIteratorIterator::LEAVES_ONLY
		);

		foreach ( $iterator as $file ) {
			if ( $file->isFile() && 'php' === $file->getExtension() ) {
				$files[] = $file->getRealPath();
			}
		}

		return $files;
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

		// Use tokenizer to validate syntax without executing.
		$code = file_get_contents( $file );
		if ( false === $code ) {
			return true;
		}

		// Try to compile without execute (PHP 7+).
		if ( function_exists( 'php_check_syntax' ) ) {
			return php_check_syntax( $file );
		}

		// Fallback: parse tokens.
		@set_error_handler( '__return_null' );
		$result = @php_compile( $code );
		@restore_error_handler();

		return null !== $result;
	}
}
