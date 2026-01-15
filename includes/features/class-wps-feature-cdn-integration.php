<?php
/**
 * Feature: CDN Integration
 *
 * CDN URL rewriting and provider integration.
 *
 * @package WPS\CoreSupport
 * @since 1.2601.75000
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPS_Feature_CDN_Integration
 *
 * CDN URL rewriting for images, CSS, JS with CloudFlare support.
 */
final class WPS_Feature_CDN_Integration extends WPS_Abstract_Feature {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'cdn-integration',
				'name'               => __( 'CDN Integration', 'plugin-wp-support-thisismyurl' ),
				'description'        => __( 'Automatic CDN URL rewriting for images, CSS, JS with support for CloudFlare, BunnyCDN, and custom CDN providers', 'plugin-wp-support-thisismyurl' ),
				'scope'              => 'core',
				'default_enabled'    => false,
				'version'            => '1.0.0',
				'widget_group'       => 'performance',
				'widget_label'       => __( 'Performance', 'plugin-wp-support-thisismyurl' ),
				'widget_description' => __( 'CDN and delivery optimization', 'plugin-wp-support-thisismyurl' ),
				'license_level'      => 3,
				'minimum_capability' => 'manage_options',
				'icon'               => 'dashicons-networking',
				'category'           => 'performance',
				'priority'           => 20,
				'dashboard'          => 'overview',
				'widget_column'      => 'right',
				'widget_priority'    => 20,
			)
		);
	}

	/**
	 * Register hooks when feature is enabled.
	 *
	 * @return void
	 */
	public function register(): void {
		if ( ! $this->is_enabled() ) {
			return;
		}

		// Output buffering for URL rewriting.
		add_action( 'template_redirect', array( $this, 'start_output_buffering' ), 1 );

		// AJAX handlers.
		add_action( 'wp_ajax_wps_test_cdn', array( $this, 'ajax_test_cdn_connection' ) );
		add_action( 'wp_ajax_wps_purge_cdn', array( $this, 'ajax_purge_cdn_cache' ) );
	}

	/**
	 * Start output buffering for URL rewriting.
	 *
	 * @return void
	 */
	public function start_output_buffering(): void {
		// Don't buffer admin pages.
		if ( is_admin() ) {
			return;
		}

		ob_start( array( $this, 'rewrite_urls' ) );
	}

	/**
	 * Rewrite URLs to CDN in HTML output.
	 *
	 * @param string $html Page HTML.
	 * @return string HTML with rewritten URLs.
	 */
	public function rewrite_urls( string $html ): string {
		$cdn_hostname = get_option( 'wps_cdn_hostname', '' );

		if ( empty( $cdn_hostname ) ) {
			return $html;
		}

		$site_url = site_url();
		$home_url = home_url();

		// Build patterns for assets.
		$extensions = array( 'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'css', 'js', 'woff', 'woff2', 'ttf', 'eot' );
		$pattern    = '#(' . preg_quote( $site_url, '#' ) . '|' . preg_quote( $home_url, '#' ) . ')([^\'"]+\.(' . implode( '|', $extensions ) . '))#i';

		// Replace with CDN URL.
		$html = preg_replace_callback( $pattern, function( $matches ) use ( $cdn_hostname ) {
			if ( $this->should_rewrite_url( $matches[0] ) ) {
				return 'https://' . $cdn_hostname . $matches[2];
			}
			return $matches[0];
		}, $html );

		return $html;
	}

	/**
	 * Check if URL should be rewritten to CDN.
	 *
	 * @param string $url URL to check.
	 * @return bool True if should rewrite.
	 */
	private function should_rewrite_url( string $url ): bool {
		// Don't rewrite admin URLs.
		if ( strpos( $url, '/wp-admin/' ) !== false ) {
			return false;
		}

		// Don't rewrite login URLs.
		if ( strpos( $url, '/wp-login.php' ) !== false ) {
			return false;
		}

		// Check exclusion list.
		$exclusions = get_option( 'wps_cdn_exclusions', array() );
		foreach ( $exclusions as $exclusion ) {
			if ( strpos( $url, $exclusion ) !== false ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Test CDN connection.
	 *
	 * @return void
	 */
	public function ajax_test_cdn_connection(): void {
		check_ajax_referer( 'wps-cdn', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'plugin-wp-support-thisismyurl' ) ) );
		}

		$cdn_url = isset( $_POST['cdn_url'] ) ? esc_url_raw( wp_unslash( $_POST['cdn_url'] ) ) : '';

		if ( empty( $cdn_url ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid CDN URL', 'plugin-wp-support-thisismyurl' ) ) );
		}

		// Test connection.
		$test_url  = trailingslashit( $cdn_url ) . 'test.txt';
		$start     = microtime( true );
		$response  = wp_remote_get( $test_url, array( 'timeout' => 10 ) );
		$end       = microtime( true );
		$resp_time = round( ( $end - $start ) * 1000, 2 );

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( array(
				'message'       => __( 'CDN connection failed', 'plugin-wp-support-thisismyurl' ),
				'error'         => $response->get_error_message(),
				'response_time' => $resp_time,
			) );
		}

		$code = wp_remote_retrieve_response_code( $response );

		wp_send_json_success( array(
			'message'       => __( 'CDN connection successful', 'plugin-wp-support-thisismyurl' ),
			'response_code' => $code,
			'response_time' => $resp_time,
		) );
	}

	/**
	 * Purge CDN cache via API.
	 *
	 * @return void
	 */
	public function ajax_purge_cdn_cache(): void {
		check_ajax_referer( 'wps-cdn', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'plugin-wp-support-thisismyurl' ) ) );
		}

		$provider = get_option( 'wps_cdn_provider', 'custom' );

		if ( 'cloudflare' === $provider ) {
			$result = $this->cloudflare_purge_cache();
		} else {
			wp_send_json_error( array( 'message' => __( 'CDN provider not configured', 'plugin-wp-support-thisismyurl' ) ) );
		}

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		wp_send_json_success( array( 'message' => __( 'CDN cache purged successfully', 'plugin-wp-support-thisismyurl' ) ) );
	}

	/**
	 * CloudFlare API: Purge cache.
	 *
	 * @return bool|\WP_Error True on success, WP_Error on failure.
	 */
	private function cloudflare_purge_cache(): bool|\WP_Error {
		$zone_id = get_option( 'wps_cloudflare_zone_id', '' );
		$api_key = get_option( 'wps_cloudflare_api_key', '' );

		if ( empty( $zone_id ) || empty( $api_key ) ) {
			return new \WP_Error( 'missing_credentials', __( 'CloudFlare credentials not configured', 'plugin-wp-support-thisismyurl' ) );
		}

		$url = sprintf( 'https://api.cloudflare.com/client/v4/zones/%s/purge_cache', $zone_id );

		$response = wp_remote_post( $url, array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $api_key,
				'Content-Type'  => 'application/json',
			),
			'body'    => wp_json_encode( array( 'purge_everything' => true ) ),
			'timeout' => 30,
		) );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( ! isset( $data['success'] ) || ! $data['success'] ) {
			return new \WP_Error( 'api_error', __( 'CloudFlare API error', 'plugin-wp-support-thisismyurl' ) );
		}

		return true;
	}
}
