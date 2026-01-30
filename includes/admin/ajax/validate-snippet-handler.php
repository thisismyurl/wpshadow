<?php
/**
 * AJAX: Validate Code Snippet
 *
 * @since   1.2601.2200
 * @package WPShadow\Admin
 */

declare(strict_types=1);

namespace WPShadow\Admin;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Core\Error_Handler;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Validate Snippet Handler
 */
class AJAX_Validate_Snippet extends AJAX_Handler_Base {
	/**
	 * Handle the AJAX request.
	 *
	 * @since 1.2601.2200
	 * @return void
	 */
	public static function handle() {
		self::verify_request( 'wpshadow_code_snippets', 'manage_options' );

		$code = self::get_post_param( 'code', 'textarea', '', true );
		$type = self::get_post_param( 'type', 'text', 'php', true );

		if ( empty( $code ) ) {
			self::send_error( __( 'Code is required', 'wpshadow' ) );
			return;
		}

		$validation_result = self::validate_code( $code, $type );

		if ( $validation_result['valid'] ) {
			self::send_success(
				array(
					'message' => __( 'Code is valid', 'wpshadow' ),
					'valid'   => true,
				)
			);
		} else {
			self::send_error(
				$validation_result['error'],
				array( 'valid' => false )
			);
		}
	}

	/**
	 * Validate code syntax.
	 *
	 * @since  1.2601.2200
	 * @param  string $code Code to validate.
	 * @param  string $type Code type (php/js/css).
	 * @return array Validation result.
	 */
	private static function validate_code( $code, $type ) {
		switch ( $type ) {
			case 'php':
				return self::validate_php( $code );
			case 'js':
				return self::validate_javascript( $code );
			case 'css':
				return self::validate_css( $code );
			default:
				return array(
					'valid' => false,
					'error' => __( 'Invalid code type', 'wpshadow' ),
				);
		}
	}

	/**
	 * Validate PHP code.
	 *
	 * @since  1.2601.2200
	 * @param  string $code PHP code to validate.
	 * @return array Validation result.
	 */
	private static function validate_php( $code ) {
		// Wrap code in PHP tags if not present
		if ( strpos( $code, '<?php' ) === false ) {
			$code = '<?php ' . $code;
		}

		// Create temporary file for syntax check
		$temp_file = wp_tempnam( 'snippet-' );
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		file_put_contents( $temp_file, $code );

		// Use php -l to check syntax
		$output = array();
		$return_var = 0;
		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.system_calls_exec
		exec( 'php -l ' . escapeshellarg( $temp_file ) . ' 2>&1', $output, $return_var );

		// Clean up
		// phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
		unlink( $temp_file );

		if ( 0 !== $return_var ) {
			$error_message = implode( "\n", $output );
			// Remove file path from error message
			$error_message = str_replace( $temp_file, 'snippet', $error_message );

			return array(
				'valid' => false,
				'error' => $error_message,
			);
		}

		// Check for dangerous functions
		$dangerous_functions = array(
			'eval',
			'exec',
			'system',
			'shell_exec',
			'passthru',
			'popen',
			'proc_open',
			'pcntl_exec',
		);

		foreach ( $dangerous_functions as $func ) {
			if ( preg_match( '/\b' . preg_quote( $func, '/' ) . '\s*\(/i', $code ) ) {
				return array(
					'valid' => false,
					'error' => sprintf(
						/* translators: %s: function name */
						__( 'Dangerous function detected: %s', 'wpshadow' ),
						$func
					),
				);
			}
		}

		return array( 'valid' => true );
	}

	/**
	 * Validate JavaScript code.
	 *
	 * @since  1.2601.2200
	 * @param  string $code JavaScript code to validate.
	 * @return array Validation result.
	 */
	private static function validate_javascript( $code ) {
		// Basic syntax checks for common errors
		$errors = array();

		// Check for unmatched braces
		$open_braces  = substr_count( $code, '{' );
		$close_braces = substr_count( $code, '}' );
		if ( $open_braces !== $close_braces ) {
			$errors[] = __( 'Unmatched braces detected', 'wpshadow' );
		}

		// Check for unmatched parentheses
		$open_parens  = substr_count( $code, '(' );
		$close_parens = substr_count( $code, ')' );
		if ( $open_parens !== $close_parens ) {
			$errors[] = __( 'Unmatched parentheses detected', 'wpshadow' );
		}

		// Check for unmatched brackets
		$open_brackets  = substr_count( $code, '[' );
		$close_brackets = substr_count( $code, ']' );
		if ( $open_brackets !== $close_brackets ) {
			$errors[] = __( 'Unmatched brackets detected', 'wpshadow' );
		}

		if ( ! empty( $errors ) ) {
			return array(
				'valid' => false,
				'error' => implode( ', ', $errors ),
			);
		}

		return array( 'valid' => true );
	}

	/**
	 * Validate CSS code.
	 *
	 * @since  1.2601.2200
	 * @param  string $code CSS code to validate.
	 * @return array Validation result.
	 */
	private static function validate_css( $code ) {
		// Basic syntax checks
		$errors = array();

		// Check for unmatched braces
		$open_braces  = substr_count( $code, '{' );
		$close_braces = substr_count( $code, '}' );
		if ( $open_braces !== $close_braces ) {
			$errors[] = __( 'Unmatched braces detected', 'wpshadow' );
		}

		// Check for basic CSS structure
		if ( ! preg_match( '/[^{}]+\s*\{[^{}]*\}/', $code ) ) {
			$errors[] = __( 'Invalid CSS structure', 'wpshadow' );
		}

		if ( ! empty( $errors ) ) {
			return array(
				'valid' => false,
				'error' => implode( ', ', $errors ),
			);
		}

		return array( 'valid' => true );
	}
}

// Register AJAX action
\add_action( 'wp_ajax_wpshadow_validate_snippet', array( '\WPShadow\\Admin\\AJAX_Validate_Snippet', 'handle' ) );
