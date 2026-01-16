<?php
/**
 * Feature: CDN Integration
 *
 * CDN URL rewriting and provider integration.
 *
 * @package WPShadow\CoreSupport
 * @since 1.2601.75000
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPSHADOW_Feature_CDN_Integration
 *
 * CDN URL rewriting for images, CSS, JS with CloudFlare support.
 */
final class WPSHADOW_Feature_CDN_Integration extends WPSHADOW_Abstract_Feature {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'cdn-integration',
				'name'               => __( 'CDN Integration', 'plugin-wpshadow' ),
				'description'        => __( 'Deliver your images and files faster to visitors around the world, stylesheets, and scripts from your CDN, with built-in support for CloudFlare, BunnyCDN, and custom providers. Reduces latency for global visitors by distributing files geographically, lowers origin server load, and works transparently without changing file storage, keeping your workflow unchanged while delivering faster downloads worldwide.', 'plugin-wpshadow' ),
				'scope'              => 'core',
				'default_enabled'    => false,
				'version'            => '1.0.0',
				'widget_group'       => 'advanced',
				'license_level'      => 3,
				'minimum_capability' => 'manage_options',
				'icon'               => 'dashicons-networking',
				'category'           => 'performance',
				'priority'           => 20,

			)
		);
		
		if ( method_exists( $this, 'register_sub_features' ) ) {
			$this->register_sub_features(
				array(
					'rewrite_images'   => __( 'CDN for Images', 'plugin-wpshadow' ),
					'rewrite_css'      => __( 'CDN for CSS Files', 'plugin-wpshadow' ),
					'rewrite_js'       => __( 'CDN for JavaScript', 'plugin-wpshadow' ),
					'rewrite_fonts'    => __( 'CDN for Web Fonts', 'plugin-wpshadow' ),
					'cloudflare_api'   => __( 'CloudFlare API Integration', 'plugin-wpshadow' ),
					'auto_purge'       => __( 'Auto-Purge on Updates', 'plugin-wpshadow' ),
				)
			);
			if ( method_exists( $this, 'set_default_sub_features' ) ) {
				$this->set_default_sub_features(
					array(
						'rewrite_images'   => true,
						'rewrite_css'      => true,
						'rewrite_js'       => true,
						'rewrite_fonts'    => true,
						'cloudflare_api'   => false,
						'auto_purge'       => false,
					)
				);
			}
		}
		
		$this->log_activity( 'feature_initialized', 'CDN Integration feature initialized', 'info' );
	}

	/**
	 * Indicate this feature has a details page.
	 *
	 * @return bool
	 */
	public function has_details_page(): bool {
		return true;
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

		// Check if any rewriting is enabled.
		$has_rewriting = get_option( 'wpshadow_cdn-integration_rewrite_images', true )
			|| get_option( 'wpshadow_cdn-integration_rewrite_css', true )
			|| get_option( 'wpshadow_cdn-integration_rewrite_js', true )
			|| get_option( 'wpshadow_cdn-integration_rewrite_fonts', true );

		// Output buffering for URL rewriting.
		if ( $has_rewriting ) {
			add_action( 'template_redirect', array( $this, 'start_output_buffering' ), 1 );
		}

		// Auto-purge on post update.
		if ( get_option( 'wpshadow_cdn-integration_auto_purge', false ) ) {
			add_action( 'save_post', array( $this, 'handle_auto_purge' ), 10, 3 );
		}

		// AJAX handlers.
		add_action( 'wp_ajax_WPSHADOW_test_cdn', array( $this, 'ajax_test_cdn_connection' ) );
		add_action( 'wp_ajax_WPSHADOW_purge_cdn', array( $this, 'ajax_purge_cdn_cache' ) );
		
		// Add Site Health tests.
		add_filter( 'site_status_tests', array( $this, 'register_site_health_test' ) );
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
		$cdn_hostname = get_option( 'wpshadow_cdn_hostname', '' );

		if ( empty( $cdn_hostname ) ) {
			return $html;
		}

		$site_url = site_url();
		$home_url = home_url();

		// Build file type arrays based on sub-feature settings.
		$extensions = array();
		
		if ( get_option( 'wpshadow_cdn-integration_rewrite_images', true ) ) {
			$extensions = array_merge( $extensions, array( 'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'ico', 'avif' ) );
		}
		
		if ( get_option( 'wpshadow_cdn-integration_rewrite_css', true ) ) {
			$extensions[] = 'css';
		}
		
		if ( get_option( 'wpshadow_cdn-integration_rewrite_js', true ) ) {
			$extensions[] = 'js';
		}
		
		if ( get_option( 'wpshadow_cdn-integration_rewrite_fonts', true ) ) {
			$extensions = array_merge( $extensions, array( 'woff', 'woff2', 'ttf', 'eot', 'otf' ) );
		}
		
		if ( empty( $extensions ) ) {
			return $html;
		}

		// Build pattern for assets.
		$pattern = '#(' . preg_quote( $site_url, '#' ) . '|' . preg_quote( $home_url, '#' ) . ')([^\'"]+(\.' . implode( '|\\.',  $extensions ) . '))#i';

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
		$exclusions = get_option( 'wpshadow_cdn_exclusions', array() );
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
		\WPShadow\WPSHADOW_verify_ajax_request( 'wps-cdn' );

		$cdn_url = isset( $_POST['cdn_url'] ) ? esc_url_raw( wp_unslash( $_POST['cdn_url'] ) ) : '';

		if ( empty( $cdn_url ) ) {
			wp_send_json_error( array( 'message' => __( 'That CDN URL doesn\'t look right', 'plugin-wpshadow' ) ) );
		}

		// Test connection.
		$test_url  = trailingslashit( $cdn_url ) . 'test.txt';
		$start     = microtime( true );
		$response  = wp_remote_get( $test_url, array( 'timeout' => 10 ) );
		$end       = microtime( true );
		$resp_time = round( ( $end - $start ) * 1000, 2 );

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( array(
				'message'       => __( 'CDN connection failed', 'plugin-wpshadow' ),
				'error'         => $response->get_error_message(),
				'response_time' => $resp_time,
			) );
		}

		$code = wp_remote_retrieve_response_code( $response );

		wp_send_json_success( array(
			'message'       => __( 'CDN connection successful', 'plugin-wpshadow' ),
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
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'plugin-wpshadow' ) ) );
		}

		$provider = get_option( 'wpshadow_cdn_provider', 'custom' );

		if ( 'cloudflare' === $provider ) {
			$result = $this->cloudflare_purge_cache();
		} else {
			wp_send_json_error( array( 'message' => __( 'CDN provider not configured', 'plugin-wpshadow' ) ) );
		}

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		wp_send_json_success( array( 'message' => __( 'CDN cache purged', 'plugin-wpshadow' ) ) );
	}

	/**
	 * CloudFlare API: Purge cache.
	 *
	 * @return bool|\WP_Error True on success, WP_Error on failure.
	 */
	private function cloudflare_purge_cache(): bool|\WP_Error {
		$zone_id = get_option( 'wpshadow_cloudflare_zone_id', '' );
		$api_key = get_option( 'wpshadow_cloudflare_api_key', '' );

		if ( empty( $zone_id ) || empty( $api_key ) ) {
			return new \WP_Error( 'missing_credentials', __( 'CloudFlare credentials not configured', 'plugin-wpshadow' ) );
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
			return new \WP_Error( 'api_error', __( 'CloudFlare API error', 'plugin-wpshadow' ) );
		}

		$this->log_activity( 'cloudflare_purge', 'CloudFlare cache purged successfully', 'success' );

		return true;
	}

	/**
	 * Handle auto-purge on post update.
	 *
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $post Post object.
	 * @param bool     $update Whether this is an update.
	 * @return void
	 */
	public function handle_auto_purge( int $post_id, \WP_Post $post, bool $update ): void {
		// Only purge on published post updates.
		if ( ! $update || 'publish' !== $post->post_status ) {
			return;
		}

		// Only if CloudFlare API is enabled.
		if ( ! get_option( 'wpshadow_cdn-integration_cloudflare_api', false ) ) {
			return;
		}

		// Purge cache.
		$result = $this->cloudflare_purge_cache();
		
		if ( ! is_wp_error( $result ) ) {
			$this->log_activity(
				'auto_purge',
				sprintf( __( 'CDN cache auto-purged for post #%d', 'plugin-wpshadow' ), $post_id ),
				'info'
			);
		}
	}

	/**
	 * Register CDN Integration Site Health test.
	 *
	 * @param array $tests Site Health tests.
	 * @return array Modified tests.
	 */
	public function register_site_health_test( array $tests ): array {
		$tests['direct']['wpshadow_cdn_integration'] = array(
			'label' => __( 'CDN Integration', 'plugin-wpshadow' ),
			'test'  => array( $this, 'test_cdn_integration' ),
		);
		return $tests;
	}

	/**
	 * Test CDN Integration configuration and status.
	 *
	 * @return array Test result.
	 */
	public function test_cdn_integration(): array {
		$is_enabled = $this->is_enabled();
		$cdn_hostname = get_option( 'wpshadow_cdn_hostname', '' );
		
		$enabled_count = 0;
		if ( get_option( 'wpshadow_cdn-integration_rewrite_images', true ) ) {
			++$enabled_count;
		}
		if ( get_option( 'wpshadow_cdn-integration_rewrite_css', true ) ) {
			++$enabled_count;
		}
		if ( get_option( 'wpshadow_cdn-integration_rewrite_js', true ) ) {
			++$enabled_count;
		}
		if ( get_option( 'wpshadow_cdn-integration_rewrite_fonts', true ) ) {
			++$enabled_count;
		}

		if ( $is_enabled && ! empty( $cdn_hostname ) && $enabled_count > 0 ) {
			return array(
				'label'       => __( 'CDN integration is active', 'plugin-wpshadow' ),
				'status'      => 'good',
				'badge'       => array(
					'label' => __( 'Performance', 'plugin-wpshadow' ),
					'color' => 'blue',
				),
				'description' => sprintf(
					'<p>%s</p>',
					wp_kses_post(
						sprintf(
							/* translators: 1: CDN hostname, 2: Number of enabled asset types */
							__( 'CDN is configured for %1$s with %2$d asset types being served from CDN.', 'plugin-wpshadow' ),
							'<code>' . esc_html( $cdn_hostname ) . '</code>',
							$enabled_count
						)
					)
				),
				'actions'     => sprintf(
					'<p><a href="%s">%s</a></p>',
					esc_url( admin_url( 'admin.php?page=wpshadow-feature-details&feature=cdn-integration' ) ),
					esc_html__( 'View CDN Settings', 'plugin-wpshadow' )
				),
				'test'        => 'wpshadow_cdn_integration',
			);
		}

		if ( $is_enabled && empty( $cdn_hostname ) ) {
			return array(
				'label'       => __( 'CDN integration is enabled but not configured', 'plugin-wpshadow' ),
				'status'      => 'recommended',
				'badge'       => array(
					'label' => __( 'Performance', 'plugin-wpshadow' ),
					'color' => 'orange',
				),
				'description' => '<p>' . __( 'CDN integration is enabled but no CDN hostname is configured. Add your CDN hostname to start serving assets faster.', 'plugin-wpshadow' ) . '</p>',
				'actions'     => sprintf(
					'<p><a href="%s">%s</a></p>',
					esc_url( admin_url( 'admin.php?page=wpshadow-feature-details&feature=cdn-integration' ) ),
					esc_html__( 'Configure CDN', 'plugin-wpshadow' )
				),
				'test'        => 'wpshadow_cdn_integration',
			);
		}

		return array(
			'label'       => __( 'CDN integration is not enabled', 'plugin-wpshadow' ),
			'status'      => 'recommended',
			'badge'       => array(
				'label' => __( 'Performance', 'plugin-wpshadow' ),
				'color' => 'gray',
			),
			'description' => '<p>' . __( 'Enable CDN integration to serve static assets faster to global visitors and reduce server load.', 'plugin-wpshadow' ) . '</p>',
			'actions'     => sprintf(
				'<p><a href="%s">%s</a></p>',
				esc_url( admin_url( 'admin.php?page=wpshadow-feature-details&feature=cdn-integration' ) ),
				esc_html__( 'Enable CDN Integration', 'plugin-wpshadow' )
			),
			'test'        => 'wpshadow_cdn_integration',
		);
	}
}
