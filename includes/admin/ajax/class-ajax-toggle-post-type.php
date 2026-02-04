<?php
/**
 * AJAX Handler - Toggle Post Type
 *
 * Handles activation/deactivation of custom post types.
 *
 * @package    WPShadow
 * @subpackage Admin\AJAX
 * @since      1.6033.1530
 */

declare(strict_types=1);

namespace WPShadow\Admin\AJAX;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Content\Post_Types_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Toggle Post Type AJAX Handler
 *
 * @since 1.6033.1530
 */
class AJAX_Toggle_Post_Type extends AJAX_Handler_Base {

	/**
	 * Handle the AJAX request.
	 *
	 * @since  1.6033.1530
	 * @return void Dies after sending JSON response.
	 */
	public static function handle() {
		self::verify_request( 'wpshadow_post_types', 'manage_options' );

		$post_type = self::get_post_param( 'post_type', 'text', '', true );
		$action    = self::get_post_param( 'action_type', 'text', 'activate', true );

		// Validate post type exists
		$available = Post_Types_Manager::get_available_post_types();
		if ( ! isset( $available[ $post_type ] ) ) {
			self::send_error( __( 'Invalid post type', 'wpshadow' ) );
		}

		if ( 'activate' === $action ) {
			$result = Post_Types_Manager::activate_post_type( $post_type );
			if ( $result ) {
				self::send_success( array(
					'message' => sprintf(
						/* translators: %s: post type name */
						__( '%s activated successfully', 'wpshadow' ),
						$available[ $post_type ]['plural']
					),
				) );
			}
		} elseif ( 'deactivate' === $action ) {
			$result = Post_Types_Manager::deactivate_post_type( $post_type );
			if ( $result ) {
				self::send_success( array(
					'message' => sprintf(
						/* translators: %s: post type name */
						__( '%s deactivated successfully', 'wpshadow' ),
						$available[ $post_type ]['plural']
					),
				) );
			}
		}

		self::send_error( __( 'Failed to update post type', 'wpshadow' ) );
	}
}

// Register AJAX handler
add_action( 'wp_ajax_wpshadow_toggle_post_type', array( 'WPShadow\Admin\AJAX\AJAX_Toggle_Post_Type', 'handle' ) );
