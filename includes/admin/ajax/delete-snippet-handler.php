<?php
/**
 * AJAX: Delete Code Snippet
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
 * Delete Snippet Handler
 */
class AJAX_Delete_Snippet extends AJAX_Handler_Base {
	/**
	 * Handle the AJAX request.
	 *
	 * @since 1.6030.2200
	 * @return void
	 */
	public static function handle() {
		self::verify_request( 'wpshadow_code_snippets', 'manage_options' );

		$snippet_id = self::get_post_param( 'snippet_id', 'int', 0, true );

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

		$snippet_title = $snippets[ $snippet_id ]['title'];

		// Remove snippet
		unset( $snippets[ $snippet_id ] );
		update_option( 'wpshadow_code_snippets', $snippets );

		// Log activity
		Activity_Logger::log(
			'snippet_deleted',
			array(
				'snippet_id'    => $snippet_id,
				'snippet_title' => $snippet_title,
			)
		);

		self::send_success(
			array(
				'message'    => __( 'Snippet deleted successfully', 'wpshadow' ),
				'snippet_id' => $snippet_id,
			)
		);
	}
}

// Register AJAX action
\add_action( 'wp_ajax_wpshadow_delete_snippet', array( '\WPShadow\\Admin\\AJAX_Delete_Snippet', 'handle' ) );
