<?php
/**
 * AJAX: Save Code Snippet
 *
 * @since   1.6030.2200
 * @package WPShadow\Admin
 */

declare(strict_types=1);

namespace WPShadow\Admin;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Core\Activity_Logger;
use WPShadow\Core\Error_Handler;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Save Snippet Handler
 */
class AJAX_Save_Snippet extends AJAX_Handler_Base {
	/**
	 * Handle the AJAX request.
	 *
	 * @since 1.6030.2200
	 * @return void
	 */
	public static function handle() {
		self::verify_request( 'wpshadow_code_snippets', 'manage_options' );

		$snippet_id  = self::get_post_param( 'snippet_id', 'int', 0 );
		$title       = self::get_post_param( 'title', 'text', '', true );
		$code        = self::get_post_param( 'code', 'textarea', '', true );
		$type        = self::get_post_param( 'type', 'text', 'php', true );
		$scope       = self::get_post_param( 'scope', 'text', 'global', true );
		$description = self::get_post_param( 'description', 'textarea', '' );

		// Validate required fields
		if ( empty( $title ) || empty( $code ) ) {
			self::send_error( __( 'Title and code are required', 'wpshadow' ) );
			return;
		}

		// Validate type
		if ( ! in_array( $type, array( 'php', 'js', 'css' ), true ) ) {
			self::send_error( __( 'Invalid snippet type', 'wpshadow' ) );
			return;
		}

		// Validate scope
		if ( ! in_array( $scope, array( 'global', 'admin', 'frontend', 'logged_in' ), true ) ) {
			self::send_error( __( 'Invalid snippet scope', 'wpshadow' ) );
			return;
		}

		// Get existing snippets
		$snippets = get_option( 'wpshadow_code_snippets', array() );
		if ( ! is_array( $snippets ) ) {
			$snippets = array();
		}

		// Check free tier limit (10 snippets)
		$is_pro = apply_filters( 'wpshadow_is_pro', false );
		if ( ! $is_pro && 0 === $snippet_id && count( $snippets ) >= 10 ) {
			self::send_error( __( 'Free tier limit reached. Upgrade to Pro for unlimited snippets.', 'wpshadow' ) );
			return;
		}

		// Create or update snippet
		if ( 0 === $snippet_id ) {
			// New snippet - generate ID
			$snippet_id = ! empty( $snippets ) ? max( array_keys( $snippets ) ) + 1 : 1;
			$is_new     = true;
		} else {
			// Update existing
			if ( ! isset( $snippets[ $snippet_id ] ) ) {
				self::send_error( __( 'Snippet not found', 'wpshadow' ) );
				return;
			}
			$is_new = false;
		}

		// Build snippet data
		$snippet_data = array(
			'title'       => sanitize_text_field( $title ),
			'code'        => $code, // Keep raw, will be sanitized on output
			'type'        => $type,
			'scope'       => $scope,
			'description' => sanitize_textarea_field( $description ),
			'active'      => false, // New snippets start inactive
			'created_at'  => $is_new ? time() : ( isset( $snippets[ $snippet_id ]['created_at'] ) ? $snippets[ $snippet_id ]['created_at'] : time() ),
			'updated_at'  => time(),
		);

		// Save snippet
		$snippets[ $snippet_id ] = $snippet_data;
		update_option( 'wpshadow_code_snippets', $snippets );

		// Log activity
		Activity_Logger::log(
			$is_new ? 'snippet_created' : 'snippet_updated',
			array(
				'snippet_id'    => $snippet_id,
				'snippet_title' => $title,
				'snippet_type'  => $type,
			)
		);

		self::send_success(
			array(
				'message'    => $is_new ? __( 'Snippet created successfully', 'wpshadow' ) : __( 'Snippet updated successfully', 'wpshadow' ),
				'snippet_id' => $snippet_id,
				'snippet'    => $snippet_data,
			)
		);
	}
}

// Register AJAX action
\add_action( 'wp_ajax_wpshadow_save_snippet', array( '\WPShadow\\Admin\\AJAX_Save_Snippet', 'handle' ) );
