<?php
/**
 * Workflow AJAX Command Base
 *
 * Base class for AJAX command handlers with common validation, nonce, and response utilities.
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Workflow;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Abstract AJAX Command
 */
abstract class Command {
	/**
	 * Command nonce action.
	 *
	 * @var string
	 */
	protected static $nonce_action = 'wpshadow_workflow_nonce';

	/**
	 * Execute the command.
	 *
	 * @return void Issues JSON response.
	 */
	abstract public function execute();

	/**
	 * Get command name/slug.
	 *
	 * @return string
	 */
	abstract public static function get_name();

	/**
	 * Verify nonce and capability.
	 *
	 * @param string $required_cap Required capability (default 'manage_options').
	 * @return bool True if verified, false otherwise.
	 */
	protected function verify_request( $required_cap = 'manage_options' ) {
		// Check user capability
		if ( ! current_user_can( $required_cap ) ) {
			$this->error( __( 'Insufficient permissions.', 'wpshadow' ) );
			return false;
		}

		// Check nonce
		$nonce = $this->get_post_var( 'nonce' );
		if ( ! $nonce || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $nonce ) ), static::$nonce_action ) ) {
			$this->error( __( 'Security check failed.', 'wpshadow' ) );
			return false;
		}

		return true;
	}

	/**
	 * Get POST variable safely.
	 *
	 * @param string $key     Key to retrieve.
	 * @param string $default Default value.
	 * @return mixed Sanitized value or default.
	 */
	protected function get_post_var( $key, $default = '' ) {
		if ( ! isset( $_POST[ $key ] ) ) {
			return $default;
		}

		$value = sanitize_text_field( wp_unslash( $_POST[ $key ] ) );
		return ( '' === $value ) ? $default : $value;
	}

	/**
	 * Send success response.
	 *
	 * @param mixed $data Additional data to send.
	 * @return void
	 */
	protected function success( $data = null ) {
		wp_send_json_success( $data );
	}

	/**
	 * Send error response.
	 *
	 * @param string $message Error message.
	 * @param mixed  $data    Additional data.
	 * @return void
	 */
	protected function error( $message = '', $data = null ) {
		wp_send_json_error(
			array_filter(
				array(
					'message' => $message,
					'data'    => $data,
				)
			)
		);
	}

	/**
	 * Register command AJAX hook.
	 *
	 * @return void
	 */
	public static function register() {
		$hook = 'wp_ajax_wpshadow_' . static::get_name();
		add_action( $hook, array( new static(), 'execute' ) );
	}
}
