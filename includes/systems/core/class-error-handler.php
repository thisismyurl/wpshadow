<?php
/**
 * Error Handler - Enhances WordPress fatal error pages
 *
 * @package WPShadow
 * @subpackage Core
 */

namespace WPShadow\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable Squiz.PHP.DiscouragedFunctions.Discouraged,WordPress.PHP.DevelopmentFunctions.prevent_path_disclosure_error_reporting,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

/**
 * Enhances WordPress fatal error display
 */
class Error_Handler {

	/**
	 * Initialize error handling
	 *
	 * Why do programmers enjoy clean logs? Easier bugs, fewer shrugs.
	 */
	public static function init(): void {
		self::configure_silent_error_capture();

		if ( self::should_skip_enhanced_error_output() ) {
			return;
		}

		// Hook into WordPress PHP error handler
		add_filter( 'wp_php_error_message', array( __CLASS__, 'enhance_error_message' ), 10, 2 );
		add_filter( 'wp_php_error_args', array( __CLASS__, 'enhance_error_args' ), 10, 2 );
		// Only add error modal script when there's an actual error
		// (removed from wp_footer/admin_footer as it was showing on ALL pages)
	}

	/**
	 * Capture PHP errors without exposing them in HTML output.
	 *
	 * @since 0.6095
	 * @return void
	 */
	private static function configure_silent_error_capture(): void {
		if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
			return;
		}

		@ini_set( 'display_errors', '0' );
		@ini_set( 'display_startup_errors', '0' );
		@ini_set( 'log_errors', '1' );
		@ini_set( 'html_errors', '0' );
		error_reporting( E_ALL );

		if ( defined( 'WP_DEBUG_LOG' ) && true === WP_DEBUG_LOG ) {
			if ( defined( 'WP_CONTENT_DIR' ) ) {
				@ini_set( 'error_log', trailingslashit( WP_CONTENT_DIR ) . 'debug.log' );
			}
		}
	}

	/**
	 * Store internal runtime errors without writing directly to PHP output.
	 *
	 * @since 0.6098
	 * @param string                 $message Human-readable error summary.
	 * @param array|\Throwable|mixed $context Additional context or exception.
	 * @return void
	 */
	public static function log_error( string $message, $context = array() ): void {
		$metadata = array();

		if ( $context instanceof \Throwable ) {
			$metadata = array(
				'error_class'   => get_class( $context ),
				'error_message' => $context->getMessage(),
				'error_file'    => basename( $context->getFile() ),
				'error_line'    => $context->getLine(),
				'error_code'    => $context->getCode(),
			);
		} elseif ( is_array( $context ) ) {
			$metadata = $context;
		} elseif ( null !== $context && '' !== (string) $context ) {
			$metadata = array(
				'context' => (string) $context,
			);
		}

		if ( class_exists( 'WPShadow\\Core\\Activity_Logger' ) ) {
			Activity_Logger::log(
				'internal_error',
				$message,
				'system',
				$metadata
			);
		}

		do_action( 'wpshadow_internal_error_logged', $message, $metadata );
	}

	/**
	 * Skip enhanced fatal-error UI for non-HTML request types.
	 *
	 * @since 0.6098
	 * @return bool
	 */
	private static function should_skip_enhanced_error_output(): bool {
		if ( function_exists( 'wp_doing_ajax' ) && wp_doing_ajax() ) {
			return true;
		}

		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return true;
		}

		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			return true;
		}

		$accept = '';
		if ( isset( $_SERVER['HTTP_ACCEPT'] ) ) {
			$accept = sanitize_text_field( wp_unslash( (string) $_SERVER['HTTP_ACCEPT'] ) );
			$accept = strtolower( trim( $accept ) );
		}
		return '' !== $accept && false === strpos( $accept, 'text/html' );
	}

	/**
	 * Reduce raw fatal messages to a safe summary for any optional UI payload.
	 *
	 * @since 0.6098
	 * @param string $message Raw fatal error message.
	 * @return string
	 */
	private static function summarize_error_message( string $message ): string {
		$message = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( $message ) ) );

		if ( '' === $message ) {
			return __( 'A critical error interrupted this request.', 'wpshadow' );
		}

		$patterns = array(
			'/ in \/[^ ]+/i',
			'/Stack trace\:.*/i',
			'/#\d+\s+.+/i',
			'/\s+thrown$/i',
		);

		$summary = preg_replace( $patterns, '', $message );
		$summary = trim( preg_replace( '/\s+/', ' ', (string) $summary ) );

		if ( '' === $summary ) {
			return __( 'A critical error interrupted this request.', 'wpshadow' );
		}

		return $summary;
	}

	/**
	 * Add the modal script and HTML early so functions are available
	 */
	public static function add_error_modal_script(): void {
		return;
	}

	/**
	 * Enhance the error message with WPShadow help options
	 *
	 * @param string $message Error message
	 * @param array $error Error details
	 * @return string Enhanced message
	 */
	public static function enhance_error_message( string $message, array $error ): string {
		$dashboard_url = admin_url( 'admin.php?page=wpshadow' );

		$help_section = '<div class="wps-p-15-rounded-4">' .
			'<p class="wps-m-0">' .
				esc_html__( 'For help resolving this issue, WPShadow can assist:', 'wpshadow' ) .
			'</p>' .
			'<p class="wps-m-10"><a class="button" href="' . esc_url( $dashboard_url ) . '">' . esc_html__( 'Open WPShadow Dashboard', 'wpshadow' ) . '</a></p>' .
			'</div>';

		$message .= $help_section;

		return $message;
	}

	/**
	 * Enhance error args to add WPShadow AI report button
	 *
	 * @param array $args wp_die arguments
	 * @param array $error Error details
	 * @return array Enhanced args
	 */
	public static function enhance_error_args( array $args, array $error ): array {
		// Modal is now always available via add_error_modal_script hook
		return $args;
	}
}
