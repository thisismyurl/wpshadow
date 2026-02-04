<?php
/**
 * AJAX: Toggle Code Snippet (Enable/Disable)
 *
 * @since   1.6030.2200
 * @package WPShadow\Admin
 */

declare(strict_types=1);

namespace WPShadow\Admin;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Core\Activity_Logger;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Toggle Snippet Handler
 */
class AJAX_Toggle_Snippet extends AJAX_Handler_Base {
	/**
	 * Handle the AJAX request.
	 *
	 * @since 1.6030.2200
	 * @return void
	 */
	public static function handle() {
		self::verify_request( 'wpshadow_code_snippets', 'manage_options' );

		$snippet_id = self::get_post_param( 'snippet_id', 'int', 0, true );
		$active     = rest_sanitize_boolean( self::get_post_param( 'active', 'bool', false ) );

		// Get existing snippets
		$snippets = get_option( 'wpshadow_code_snippets', array() );
		if ( ! is_array( $snippets ) ) {
			$snippets = array();
		}

		// Check if snippet exists
		if ( ! isset( $snippets[ $snippet_id ] ) ) {
			self::send_error( __( 'Snippet not found', 'wpshadow' ) );
			return;
		}

		// Validate snippet before activating
		if ( $active && 'php' === $snippets[ $snippet_id ]['type'] ) {
			$validation = self::validate_php_snippet( $snippets[ $snippet_id ]['code'] );
			if ( ! $validation['valid'] ) {
				self::send_error(
					sprintf(
						/* translators: %s: validation error */
						__( 'Cannot activate snippet: %s', 'wpshadow' ),
						$validation['error']
					)
				);
				return;
			}
		}

		// Update snippet status
		$snippets[ $snippet_id ]['active'] = $active;
		update_option( 'wpshadow_code_snippets', $snippets );

		// Log activity
		Activity_Logger::log(
			$active ? 'snippet_activated' : 'snippet_deactivated',
			array(
				'snippet_id'    => $snippet_id,
				'snippet_title' => $snippets[ $snippet_id ]['title'],
			)
		);

		self::send_success(
			array(
				'message'    => $active ? __( 'Snippet activated', 'wpshadow' ) : __( 'Snippet deactivated', 'wpshadow' ),
				'snippet_id' => $snippet_id,
				'active'     => $active,
			)
		);
	}

	/**
	 * Validate PHP snippet.
	 *
	 * @since  1.6030.2200
	 * @param  string $code PHP code.
	 * @return array Validation result.
	 */
	private static function validate_php_snippet( $code ) {
		// Wrap code if needed.
		if ( strpos( $code, '<?php' ) === false ) {
			$code = '<?php ' . $code;
		}

		// Use token_get_all() for safe syntax checking (no command execution).
		$tokens = @token_get_all( $code );

		// Check for parse errors.
		if ( false === $tokens ) {
			return array(
				'valid' => false,
				'error' => __( 'PHP syntax error detected', 'wpshadow' ),
			);
		}

		// Check for dangerous functions.
		$dangerous_functions = array( 'eval', 'exec', 'system', 'shell_exec', 'passthru', 'popen', 'proc_open' );
		foreach ( $dangerous_functions as $func ) {
			if ( preg_match( '/\\b' . preg_quote( $func, '/' ) . '\\s*\\(/i', $code ) ) {
				return array(
					'valid' => false,
					'error' => sprintf(
						/* translators: %s: function name */
						__( 'Dangerous function not allowed: %s', 'wpshadow' ),
						$func
					),
				);
			}
		}

		return array( 'valid' => true );
	}
}

// Register AJAX action
\add_action( 'wp_ajax_wpshadow_toggle_snippet', array( '\WPShadow\\Admin\\AJAX_Toggle_Snippet', 'handle' ) );
