<?php
/**
 * Links Redirect Handler
 *
 * Handles clicks on managed links and provides analytics/cloaking.
 *
 * @package WPShadow
 * @subpackage Links
 */

declare(strict_types=1);

namespace WPShadow\Links;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Links_Redirect_Handler class.
 */
class Links_Redirect_Handler {
	/**
	 * Initialize redirect handler.
	 */
	public static function init(): void {
		add_action( 'wp_ajax_wpshadow_link_click', [ __CLASS__, 'handle_redirect' ] );
		add_action( 'wp_ajax_nopriv_wpshadow_link_click', [ __CLASS__, 'handle_redirect' ] );
	}

	/**
	 * Handle link click redirect (used for ad-blocker resistance).
	 */
	public static function handle_redirect(): void {
		check_ajax_referer( 'wpshadow_links_nonce' );

		if ( ! isset( $_POST['link_id'] ) ) {
			wp_send_json_error( __( 'Missing link ID', 'wpshadow' ) );
		}

		$link_id = intval( $_POST['link_id'] );
		$url     = get_post_meta( $link_id, 'wpshadow_link_url', true );

		if ( empty( $url ) ) {
			wp_send_json_error( __( 'Link not found', 'wpshadow' ) );
		}

		// Record click if analytics enabled
		self::record_link_click( $link_id );

		wp_send_json_success( [
			'url' => $url,
		] );
	}

	/**
	 * Record link click for analytics.
	 *
	 * @param int $link_id The link post ID.
	 */
	private static function record_link_click( $link_id ): void {
		$clicks = intval( get_post_meta( $link_id, 'wpshadow_link_clicks', true ) );
		update_post_meta( $link_id, 'wpshadow_link_clicks', $clicks + 1 );

		// Record last click date
		update_post_meta( $link_id, 'wpshadow_link_last_click', current_time( 'mysql' ) );
	}
}
