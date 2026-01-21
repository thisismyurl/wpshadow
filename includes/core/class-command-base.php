<?php
/**
 * Command Base
 *
 * Shared utilities for command-style AJAX handlers (Guardian/Cloud commands).
 * Provides centralized nonce/capability checks, parameter access, and
 * normalized success/error responses.
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Abstract Command Base
 */
abstract class Command_Base {
	/**
	 * Default nonce action.
	 *
	 * @var string
	 */
	protected static $nonce_action = 'wpshadow_guardian_nonce';

	/**
	 * Default capability required.
	 *
	 * @var string
	 */
	protected static $capability = 'manage_options';

	/**
	 * Cached request parameters.
	 *
	 * @var array
	 */
	protected $params = array();

	/**
	 * Constructor hydrates params from $_POST.
	 */
	public function __construct() {
		$this->params = isset( $_POST ) ? wp_unslash( $_POST ) : array();
	}

	/**
	 * Register AJAX hook for this command.
	 */
	public static function register(): void {
		$instance = new static();
		add_action( 'wp_ajax_wpshadow_' . $instance->get_name(), array( $instance, 'handle_request' ) );
	}

	/**
	 * Process request flow (instance-level).
	 */
	public function handle_request(): void {
		// Verify security
		static::verify_request( static::$nonce_action, static::$capability );

		$result = $this->execute();

		$this->respond( $result );
	}

	/**
	 * Execute the command and return a result array.
	 *
	 * @return array
	 */
	abstract protected function execute(): array;

	/**
	 * Get the command name (used for AJAX action).
	 *
	 * @return string
	 */
	abstract public function get_name(): string;

	/**
	 * Return parameter from request (unsanitized; callers sanitize as needed).
	 *
	 * @param string $key     Key to retrieve.
	 * @param mixed  $default Default value when not set.
	 * @return mixed
	 */
	protected function get_param( string $key, $default = null ) {
		return $this->params[ $key ] ?? $default;
	}

	/**
	 * Build success payload.
	 *
	 * @param array $data Data to include.
	 * @return array
	 */
	protected function success( array $data = array() ): array {
		return array_merge( array( 'success' => true ), $data );
	}

	/**
	 * Build error payload.
	 *
	 * @param string $message Error message.
	 * @param array  $data    Extra data.
	 * @return array
	 */
	protected function error( string $message = '', array $data = array() ): array {
		return array_merge( array( 'success' => false, 'message' => $message ), $data );
	}

	/**
	 * Send JSON response based on result structure.
	 *
	 * @param array $result Result from execute().
	 * @return void Dies after sending response.
	 */
	protected function respond( array $result ): void {
		$success = $result['success'] ?? false;
		if ( $success ) {
			// Drop the explicit success flag to avoid duplication in payload
			unset( $result['success'] );
			self::send_success( $result );
		}

		$message = $result['message'] ?? __( 'Operation failed.', 'wpshadow' );
		unset( $result['success'] );

		self::send_error( $message, $result );
	}

	/**
	 * Verify nonce and capability for AJAX request.
	 *
	 * @param string $nonce_action Nonce action.
	 * @param string $capability   Required capability.
	 * @param string $nonce_field  Nonce field key.
	 * @return void Dies on failure.
	 */
	protected static function verify_request( string $nonce_action, string $capability = 'manage_options', string $nonce_field = 'nonce' ): void {
		check_ajax_referer( $nonce_action, $nonce_field );

		// Multisite-aware capability check
		if ( is_multisite() && is_network_admin() ) {
			if ( ! current_user_can( 'manage_network_options' ) ) {
				wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'wpshadow' ) ) );
			}
			return;
		}

		if ( ! current_user_can( $capability ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'wpshadow' ) ) );
		}
	}

	/**
	 * Sanitize and validate a POST parameter (static helper for static handlers).
	 *
	 * @param string $key      POST key.
	 * @param string $type     Sanitization type.
	 * @param mixed  $default  Default value.
	 * @param bool   $required Whether param is required.
	 * @return mixed
	 */
	protected static function get_post_param( string $key, string $type = 'text', $default = '', bool $required = false ) {
		if ( ! isset( $_POST[ $key ] ) ) {
			if ( $required ) {
				wp_send_json_error( array( 'message' => sprintf( __( 'Required parameter "%s" is missing.', 'wpshadow' ), $key ) ) );
			}
			return $default;
		}

		$value = wp_unslash( $_POST[ $key ] );

		switch ( $type ) {
			case 'email':
				return sanitize_email( $value );
			case 'key':
				return sanitize_key( $value );
			case 'textarea':
				return sanitize_textarea_field( $value );
			case 'int':
				return intval( $value );
			case 'bool':
				return rest_sanitize_boolean( $value );
			case 'url':
				return esc_url_raw( $value );
			case 'text':
			default:
				return sanitize_text_field( $value );
		}
	}

	/**
	 * Send JSON success response.
	 *
	 * @param array $data Payload.
	 * @return void
	 */
	protected static function send_success( array $data = array() ): void {
		wp_send_json_success( $data );
	}

	/**
	 * Send JSON error response.
	 *
	 * @param string $message Error message.
	 * @param array  $data    Payload.
	 * @return void
	 */
	protected static function send_error( string $message, array $data = array() ): void {
		$data['message'] = $message;
		wp_send_json_error( $data );
	}
}
