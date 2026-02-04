<?php

/**
 * Error Report AJAX Handler
 *
 * @package WPShadow
 * @subpackage Admin\Ajax
 */

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Core\Options_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handle error report submissions
 */
class Error_Report_Handler extends AJAX_Handler_Base {


	/**
	 * Register AJAX hooks
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_send_error_report', array( __CLASS__, 'handle' ) );
		// Allow non-logged-in users for severe error scenarios.
		add_action( 'wp_ajax_nopriv_wpshadow_send_error_report', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Handle the error report submission
	 */
	public static function handle(): void {
		// Verify nonce
		self::verify_request( 'wpshadow_error_report', 'read' );

		// Get error data
		$error_hash      = self::get_post_param( 'error_hash', 'text', '', true );
		$error_data_json = self::get_post_param( 'error_data', 'text', '{}', false );
		$error_data      = json_decode( stripslashes( $error_data_json ), true );

		if ( empty( $error_data ) ) {
			self::send_error( __( 'No error data provided', 'wpshadow' ) );
		}

		// Store error report for future reference
		$reports                = Options_Manager::get_array( 'wpshadow_error_reports', array() );
		$reports[ $error_hash ] = array(
			'timestamp'   => time(),
			'error'       => $error_data,
			'user_agent'  => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '',
			'php_version' => PHP_VERSION,
			'wp_version'  => get_bloginfo( 'version' ),
		);

		// Keep only last 20 reports
		$reports = array_slice( $reports, -20, 20, true );
		update_option( 'wpshadow_error_reports', $reports, false );

		// Generate AI suggestions based on error type
		$suggestions = self::generate_suggestions( $error_data );

		// Send success response with suggestions
		self::send_success(
			array(
				'message'     => __( 'Error report sent successfully', 'wpshadow' ),
				'suggestions' => $suggestions,
			)
		);
	}

	/**
	 * Generate AI-like suggestions based on error data
	 *
	 * @param array $error_data Error information
	 * @return string HTML suggestions
	 */
	private static function generate_suggestions( array $error_data ): string {
		$message = $error_data['message'] ?? '';
		$file    = $error_data['file'] ?? '';
		$line    = $error_data['line'] ?? '';

		$suggestions = '<ul class="wps-error-suggestions">';

		// Parse error type and provide specific suggestions
		if ( stripos( $message, 'fatal error' ) !== false || stripos( $message, 'uncaught error' ) !== false ) {
			$suggestions .= '<li><strong>' . __( 'Check Recent Changes:', 'wpshadow' ) . '</strong> ' .
				__( 'This error often occurs after updating plugins or themes. Try disabling recent changes.', 'wpshadow' ) . '</li>';
		}

		if ( stripos( $message, 'memory' ) !== false || stripos( $message, 'allowed memory size' ) !== false ) {
			$suggestions .= '<li><strong>' . __( 'Increase Memory:', 'wpshadow' ) . '</strong> ' .
				sprintf(
					__( 'Your site needs more memory. <a href="%s" target="_blank">Learn how to increase PHP memory limit</a>', 'wpshadow' ),
					\WPShadow\Core\UTM_Link_Manager::kb_link( 'increase-memory-limit', 'error-report' )
				) . '</li>';
		}

		if ( stripos( $message, 'undefined' ) !== false || stripos( $message, 'call to undefined' ) !== false ) {
			$suggestions .= '<li><strong>' . __( 'Missing Function:', 'wpshadow' ) . '</strong> ' .
				__( 'A required function is missing. This usually means a plugin or theme is incompatible with your PHP version.', 'wpshadow' ) . '</li>';
		}

		if ( stripos( $message, 'syntax error' ) !== false || stripos( $message, 'parse error' ) !== false ) {
			$suggestions .= '<li><strong>' . __( 'Code Syntax Issue:', 'wpshadow' ) . '</strong> ' .
				__( 'There\'s a syntax error in your code. Check the file mentioned for typos or incomplete code.', 'wpshadow' ) . '</li>';
		}

		if ( ! empty( $file ) && stripos( $file, 'plugins' ) !== false ) {
			$plugin_name = self::extract_plugin_name( $file );
			if ( $plugin_name ) {
				$suggestions .= '<li><strong>' . __( 'Plugin Issue:', 'wpshadow' ) . '</strong> ' .
					sprintf(
						__( 'The error originates from the "%s" plugin. Try deactivating it via FTP or database.', 'wpshadow' ),
						esc_html( $plugin_name )
					) . '</li>';
			}
		}

		if ( ! empty( $file ) && stripos( $file, 'themes' ) !== false ) {
			$suggestions .= '<li><strong>' . __( 'Theme Issue:', 'wpshadow' ) . '</strong> ' .
				__( 'The error is in your theme. Switch to a default theme (Twenty Twenty-Four) via FTP or database.', 'wpshadow' ) . '</li>';
		}

		// Always add emergency recovery link
		$suggestions .= '<li><strong>' . __( 'Emergency Recovery:', 'wpshadow' ) . '</strong> ' .
			sprintf(
				__( '<a href="%s" target="_blank">Access WPShadow Emergency Recovery Mode</a>', 'wpshadow' ),
				admin_url( 'admin.php?page=wpshadow-help&tab=emergency' )
			) . '</li>';

		// Link to full KB article
		$suggestions .= '<li><strong>' . __( 'More Help:', 'wpshadow' ) . '</strong> ' .
			'<a href="' . esc_url( \WPShadow\Core\UTM_Link_Manager::kb_link( 'fatal-errors', 'error-report' ) ) . '" target="_blank">' .
			__( 'Read our complete guide to fixing fatal errors', 'wpshadow' ) .
			'</a></li>';

		$suggestions .= '</ul>';

		return $suggestions;
	}

	/**
	 * Extract plugin name from file path
	 *
	 * @param string $file File path
	 * @return string|null Plugin name or null
	 */
	private static function extract_plugin_name( string $file ): ?string {
		if ( preg_match( '#/plugins/([^/]+)/#', $file, $matches ) ) {
			return str_replace( array( '-', '_' ), ' ', $matches[1] );
		}
		return null;
	}
}
