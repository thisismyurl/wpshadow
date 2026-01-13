<?php
/**
 * Help Content API Client
 *
 * Fetches plugin documentation and help content dynamically from thisismyurl.com
 * with local caching and fallback content.
 *
 * @package WPS_WP_SUPPORT_THISISMYURL
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPS_Help_Content_API
 *
 * Manages fetching and caching of help documentation from remote API.
 */
class WPS_Help_Content_API {
	/**
	 * Remote API endpoint for help content.
	 *
	 * @var string
	 */
	private const API_ENDPOINT = 'https://thisismyurl.com/api/plugin-support/help-content/v1';

	/**
	 * Cache key for help content transient.
	 *
	 * @var string
	 */
	private const CACHE_KEY = 'wps_help_content_cache';

	/**
	 * Cache expiration time in seconds (24 hours).
	 *
	 * @var int
	 */
	private const CACHE_EXPIRATION = DAY_IN_SECONDS;

	/**
	 * Initialize the help content API.
	 *
	 * @return void
	 */
	public static function init(): void {
		// Hook for manual cache refresh if needed.
		add_action( 'wps_refresh_help_content', array( __CLASS__, 'clear_cache' ) );
	}

	/**
	 * Get help content with caching and fallback.
	 *
	 * @param string $section Optional section to retrieve (overview|getting-started|modules|faq).
	 * @return array Help content data.
	 */
	public static function get_content( string $section = '' ): array {
		// Try to get from cache first.
		$cached = get_transient( self::CACHE_KEY );
		if ( false !== $cached && is_array( $cached ) ) {
			return self::filter_section( $cached, $section );
		}

		// Attempt to fetch from remote API.
		$remote_content = self::fetch_remote_content();

		if ( ! empty( $remote_content ) ) {
			// Cache the successful response.
			set_transient( self::CACHE_KEY, $remote_content, self::get_cache_expiration() );
			return self::filter_section( $remote_content, $section );
		}

		// Fallback to static content if remote fetch fails.
		$fallback = self::get_fallback_content();
		return self::filter_section( $fallback, $section );
	}

	/**
	 * Fetch help content from remote API.
	 *
	 * @return array|null Remote content or null on failure.
	 */
	private static function fetch_remote_content(): ?array {
		/**
		 * Filter the help content API endpoint.
		 *
		 * @param string $endpoint The API endpoint URL.
		 */
		$endpoint = apply_filters( 'wps_help_content_api_endpoint', self::API_ENDPOINT );

		// Make remote request with timeout.
		$response = wp_remote_get(
			$endpoint,
			array(
				'timeout' => 10,
				'headers' => array(
					'Accept' => 'application/json',
				),
			)
		);

		// Handle request errors.
		if ( is_wp_error( $response ) ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'WPS Help Content API: Failed to fetch - ' . $response->get_error_message() );
			}
			return null;
		}

		// Check response code.
		$code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $code ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( "WPS Help Content API: Unexpected response code {$code}" );
			}
			return null;
		}

		// Parse JSON response.
		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( ! is_array( $data ) ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'WPS Help Content API: Invalid JSON response' );
			}
			return null;
		}

		/**
		 * Action fired after successful remote content fetch.
		 *
		 * @param array $data The fetched content data.
		 */
		do_action( 'wps_help_content_fetched', $data );

		return $data;
	}

	/**
	 * Filter content by section if requested.
	 *
	 * @param array  $content Full content array.
	 * @param string $section Section to filter.
	 * @return array Filtered or full content.
	 */
	private static function filter_section( array $content, string $section ): array {
		if ( empty( $section ) ) {
			return $content;
		}

		return isset( $content[ $section ] ) && is_array( $content[ $section ] )
			? array( $section => $content[ $section ] )
			: $content;
	}

	/**
	 * Get fallback static help content.
	 *
	 * @return array Static help content structure.
	 */
	private static function get_fallback_content(): array {
		return array(
			'overview'        => array(
				'title'   => __( 'Plugin Overview', 'plugin-wp-support-thisismyurl' ),
				'content' => array(
					array(
						'heading' => __( 'Welcome to WP Support', 'plugin-wp-support-thisismyurl' ),
						'text'    => __( 'WordPress Support is a comprehensive WordPress support and diagnostics plugin that works perfectly as a standalone core or optionally extends with specialized modules (Image Hub, Media Hub, Vault Storage, and more).', 'plugin-wp-support-thisismyurl' ),
					),
					array(
						'heading' => __( 'Core Features', 'plugin-wp-support-thisismyurl' ),
						'text'    => __( 'The core plugin includes health diagnostics, emergency support, backup verification, site documentation, activity logging, guided walkthroughs, and update simulation capabilities.', 'plugin-wp-support-thisismyurl' ),
					),
					array(
						'heading' => __( 'Module Ecosystem', 'plugin-wp-support-thisismyurl' ),
						'text'    => __( 'Extend functionality with optional modules for media optimization, vault storage, and specialized format support. Modules are completely optional - the core plugin provides full support functionality on its own.', 'plugin-wp-support-thisismyurl' ),
					),
				),
			),
			'getting-started' => array(
				'title'   => __( 'Getting Started', 'plugin-wp-support-thisismyurl' ),
				'content' => array(
					array(
						'heading' => __( '1. Dashboard Overview', 'plugin-wp-support-thisismyurl' ),
						'text'    => __( 'Access the main dashboard from the Support menu. The dashboard provides an at-a-glance view of your site health, active modules, and quick actions.', 'plugin-wp-support-thisismyurl' ),
					),
					array(
						'heading' => __( '2. Configure Settings', 'plugin-wp-support-thisismyurl' ),
						'text'    => __( 'Visit the Settings tab to configure module discovery, capability mapping, privacy options, and license settings. All settings include descriptions to guide you.', 'plugin-wp-support-thisismyurl' ),
					),
					array(
						'heading' => __( '3. Install Modules (Optional)', 'plugin-wp-support-thisismyurl' ),
						'text'    => __( 'Navigate to the Modules tab to browse and install optional modules. Each module extends the core plugin with specialized functionality. You can enable/disable modules at any time.', 'plugin-wp-support-thisismyurl' ),
					),
					array(
						'heading' => __( '4. Monitor Health', 'plugin-wp-support-thisismyurl' ),
						'text'    => __( 'Use the built-in diagnostics to monitor site health, check for vulnerabilities, and validate backup integrity. Emergency support features are available when issues are detected.', 'plugin-wp-support-thisismyurl' ),
					),
				),
			),
			'modules'         => array(
				'title'       => __( 'Module Documentation', 'plugin-wp-support-thisismyurl' ),
				'description' => __( 'Documentation for installed modules will appear here. Install modules from the Modules tab to extend functionality.', 'plugin-wp-support-thisismyurl' ),
				'content'     => array(
					array(
						'heading' => __( 'Media Hub', 'plugin-wp-support-thisismyurl' ),
						'text'    => __( 'Provides shared media optimization and processing infrastructure. Enables advanced image format support, optimization pipelines, and smart resizing capabilities.', 'plugin-wp-support-thisismyurl' ),
					),
					array(
						'heading' => __( 'Vault Storage', 'plugin-wp-support-thisismyurl' ),
						'text'    => __( 'Secure original file storage with encryption, versioning, and cloud offload. Protects originals while optimizing delivery copies for performance.', 'plugin-wp-support-thisismyurl' ),
					),
					array(
						'heading' => __( 'Image Formats', 'plugin-wp-support-thisismyurl' ),
						'text'    => __( 'Adds support for modern image formats including AVIF, WebP, HEIC, and various RAW camera formats. Automatic format conversion and optimization.', 'plugin-wp-support-thisismyurl' ),
					),
				),
			),
			'faq'             => array(
				'title'   => __( 'Frequently Asked Questions', 'plugin-wp-support-thisismyurl' ),
				'content' => array(
					array(
						'question' => __( 'Do I need to install modules?', 'plugin-wp-support-thisismyurl' ),
						'answer'   => __( 'No. WordPress Support works perfectly as a standalone core with full diagnostics, emergency recovery, backup verification, and documentation management. Install modules only if you need specialized features like media optimization or vault storage.', 'plugin-wp-support-thisismyurl' ),
					),
					array(
						'question' => __( 'How do I update the plugin?', 'plugin-wp-support-thisismyurl' ),
						'answer'   => __( 'Updates are pulled automatically from GitHub releases. Configure auto-update settings in the Settings tab under License & Updates. You can choose which update types (major, minor, patch) to install automatically.', 'plugin-wp-support-thisismyurl' ),
					),
					array(
						'question' => __( 'Is multisite supported?', 'plugin-wp-support-thisismyurl' ),
						'answer'   => __( 'Yes. Full multisite support with Network Governance. Network admins can manage settings globally while allowing site-level overrides where appropriate.', 'plugin-wp-support-thisismyurl' ),
					),
					array(
						'question' => __( 'How do I troubleshoot issues?', 'plugin-wp-support-thisismyurl' ),
						'answer'   => __( 'Use the built-in diagnostics dashboard to identify issues. Enable diagnostic logging in Settings > Privacy & GDPR for detailed troubleshooting information. Emergency support features can isolate problematic plugins if needed.', 'plugin-wp-support-thisismyurl' ),
					),
					array(
						'question' => __( 'Where is my data stored?', 'plugin-wp-support-thisismyurl' ),
						'answer'   => __( 'All data is stored locally in your WordPress database. If you enable the Vault module, original files are stored in a secure wp-content/vault directory with optional cloud offload. No data is sent to external services without your explicit configuration.', 'plugin-wp-support-thisismyurl' ),
					),
					array(
						'question' => __( 'Can I customize the dashboard?', 'plugin-wp-support-thisismyurl' ),
						'answer'   => __( 'Yes. Use Screen Options to adjust the number of columns and drag widgets to rearrange them. You can also collapse/expand widgets by clicking the toggle arrow. Your layout preferences are saved automatically.', 'plugin-wp-support-thisismyurl' ),
					),
				),
			),
		);
	}

	/**
	 * Clear the help content cache.
	 *
	 * @return void
	 */
	public static function clear_cache(): void {
		delete_transient( self::CACHE_KEY );
	}

	/**
	 * Get cache expiration time in seconds.
	 *
	 * @return int Cache expiration in seconds.
	 */
	public static function get_cache_expiration(): int {
		/**
		 * Filter the help content cache expiration time.
		 *
		 * @param int $expiration Cache expiration in seconds.
		 */
		return (int) apply_filters( 'wps_help_content_cache_expiration', self::CACHE_EXPIRATION );
	}
}
