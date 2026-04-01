<?php
/**
 * AJAX Handler: User Search
 *
 * Provides a lightweight user search endpoint for the user privacy report.
 *
 * @package WPShadow
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * User_Search_Handler Class
 *
 * @since 0.6093.1200
 */
class User_Search_Handler extends AJAX_Handler_Base {

	/**
	 * Register the AJAX hook.
	 *
	 * @since 0.6093.1200
	 * @return void
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_user_search', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Handle user search requests.
	 *
	 * @since 0.6093.1200
	 * @return void
	 */
	public static function handle(): void {
		self::verify_request( 'wpshadow_user_search', 'list_users', 'nonce' );

		$term = trim( (string) self::get_post_param( 'term', 'text', '' ) );

		if ( strlen( $term ) < 2 ) {
			self::send_success( array( 'users' => array() ) );
		}

		$query = new \WP_User_Query(
			array(
				'search'         => '*' . $term . '*',
				'search_columns' => array( 'user_login', 'display_name', 'user_email' ),
				'number'         => 20,
				'orderby'        => 'display_name',
				'order'          => 'ASC',
				'fields'         => array( 'ID', 'display_name', 'user_email' ),
			)
		);

		$results = array();
		foreach ( $query->get_results() as $user ) {
			$results[] = array(
				'id'    => $user->ID,
				'label' => sprintf( '%1$s (%2$s)', $user->display_name, $user->user_email ),
			);
		}

		self::send_success( array( 'users' => $results ) );
	}
}

User_Search_Handler::register();
