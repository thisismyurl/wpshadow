<?php
/**
 * GitHub Updater for Private Repositories
 *
 * Handles automatic updates from private GitHub repositories using
 * GitHub API with optional authentication token support.
 *
 * @package wp_support_SUPPORT
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GitHub Updater Class
 *
 * Enables private GitHub repository updates via WordPress plugin update system.
 */
class WPS_GitHub_Updater {

	/**
	 * GitHub repository owner
	 *
	 * @var string
	 */
	private const REPO_OWNER = 'thisismyurl';

	/**
	 * GitHub repository name
	 *
	 * @var string
	 */
	private const REPO_NAME = 'plugin-wp-support-thisismyurl';

	/**
	 * GitHub API base URL
	 *
	 * @var string
	 */
	private const GITHUB_API = 'https://api.github.com';

	/**
	 * Plugin basename
	 *
	 * @var string
	 */
	private static $plugin_basename = '';

	/**
	 * Initialize the GitHub updater
	 *
	 * @param string $plugin_basename Plugin basename (plugin-dir/plugin.php).
	 * @return void
	 */
	public static function init( string $plugin_basename ): void {
		self::$plugin_basename = $plugin_basename;

		// Hook into WordPress update checks.
		add_filter( 'site_transient_update_plugins', array( __CLASS__, 'check_for_updates' ), 10, 1 );
		add_filter( 'plugins_api', array( __CLASS__, 'plugin_info_api' ), 10, 3 );

		// Add settings link to include GitHub token option.
		add_action( 'plugin_action_links_' . $plugin_basename, array( __CLASS__, 'add_action_links' ) );
	}

	/**
	 * Check for plugin updates from GitHub
	 *
	 * @param object $transient WordPress update transient.
	 * @return object Modified transient with update info if available.
	 */
	public static function check_for_updates( $transient ) {
		if ( empty( $transient->checked ) ) {
			return $transient;
		}

		$current_version = $transient->checked[ self::$plugin_basename ] ?? '0.0.0';
		$release_data    = self::get_latest_release();

		if ( ! $release_data ) {
			return $transient;
		}

		$release_version = ltrim( $release_data['tag_name'], 'v' );

		// Compare versions - update available if release is newer.
		if ( version_compare( $release_version, $current_version, '>' ) ) {
			$transient->response[ self::$plugin_basename ] = (object) array(
				'id'            => self::$plugin_basename,
				'slug'          => 'plugin-wp-support-thisismyurl',
				'plugin'        => self::$plugin_basename,
				'new_version'   => $release_version,
				'url'           => $release_data['html_url'],
				'package'       => $release_data['zipball_url'],
				'icons'         => array(),
				'banners'       => array(),
				'banners_rtl'   => array(),
				'tested'        => '6.9',
				'requires_php'  => '8.1.29',
				'requires'      => '6.4',
				'requires_wp'   => '6.4',
				'compatibility' => array(),
			);
		}

		return $transient;
	}

	/**
	 * Provide plugin info from GitHub for the plugin details modal
	 *
	 * @param bool|object $result      API response object or bool.
	 * @param string      $action      API action ('query_plugins', 'plugin_information', etc).
	 * @param object      $args        API call arguments.
	 * @return object|bool Modified plugin info or false.
	 */
	public static function plugin_info_api( $result, string $action, $args ) {
		if ( 'plugin_information' !== $action || 'plugin-wp-support-thisismyurl' !== $args->slug ) {
			return $result;
		}

		$release_data = self::get_latest_release();

		if ( ! $release_data ) {
			return $result;
		}

		$plugin_info = (object) array(
			'name'           => 'WP Support (thisismyurl)',
			'slug'           => 'plugin-wp-support-thisismyurl',
			'version'        => ltrim( $release_data['tag_name'], 'v' ),
			'author'         => 'Christopher Ross',
			'author_profile' => 'https://github.com/thisismyurl',
			'download_link'  => $release_data['zipball_url'],
			'description'    => $release_data['body'] ?? 'The foundational support plugin for WordPress with comprehensive diagnostics, emergency recovery, and backup verification.',
			'homepage'       => 'https://thisismyurl.com/plugin-wp-support-thisismyurl',
			'requires'       => '6.4',
			'requires_php'   => '8.1.29',
			'tested'         => '6.9',
			'last_updated'   => $release_data['published_at'] ?? current_time( 'mysql' ),
			'sections'       => array(
				'description' => 'WP Support provides comprehensive WordPress health diagnostics, emergency recovery tools, backup verification, and documentation management.',
			),
			'banners'        => array(),
			'banners_rtl'    => array(),
			'icons'          => array(),
		);

		return $plugin_info;
	}

	/**
	 * Get latest release from GitHub API
	 *
	 * @return array|null Release data or null if not found.
	 */
	private static function get_latest_release(): ?array {
		// Check transient cache first (6 hour expiration).
		$cached = get_transient( 'wps_github_latest_release' );
		if ( is_array( $cached ) && ! empty( $cached ) ) {
			return $cached;
		}

		// Build API URL.
		$url = sprintf(
			'%s/repos/%s/%s/releases/latest',
			self::GITHUB_API,
			self::REPO_OWNER,
			self::REPO_NAME
		);

		// Get optional GitHub token from options (for private repos or higher rate limits).
		$token = self::get_github_token();
		$args  = array(
			'timeout' => 10,
			'headers' => array(),
		);

		if ( $token ) {
			$args['headers']['Authorization'] = 'token ' . $token;
		}

		// Fetch release data from GitHub API.
		$response = wp_remote_get( $url, $args );

		if ( is_wp_error( $response ) ) {

			return null;
		}

		$status_code = (int) wp_remote_retrieve_response_code( $response );
		if ( 200 !== $status_code ) {

			return null;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( ! is_array( $data ) || empty( $data['tag_name'] ) ) {
			return null;
		}

		// Cache the release data for 6 hours.
		set_transient( 'wps_github_latest_release', $data, 6 * HOUR_IN_SECONDS );

		return $data;
	}

	/**
	 * Get GitHub API token from options
	 *
	 * @return string|null Token if configured, null otherwise.
	 */
	private static function get_github_token(): ?string {
		// Check for token in plugin option.
		$token = get_option( 'wps_github_token' );
		if ( ! empty( $token ) && is_string( $token ) ) {
			return sanitize_text_field( $token );
		}

		// Check for token in environment variable (useful for hosting environments).
		$env_token = getenv( 'GITHUB_TOKEN' );
		if ( ! empty( $env_token ) && is_string( $env_token ) ) {
			return sanitize_text_field( $env_token );
		}

		return null;
	}

	/**
	 * Add action links to plugin row (Settings link for token configuration)
	 *
	 * @param array $links Plugin action links.
	 * @return array Modified action links.
	 */
	public static function add_action_links( array $links ): array {
		if ( current_user_can( 'manage_options' ) ) {
			$settings_link = sprintf(
				'<a href="%s">%s</a>',
				esc_url( add_query_arg( 'WPS_tab', 'github-updates', admin_url( 'admin.php?page=wp-support' ) ) ),
				esc_html__( 'GitHub Updates', 'plugin-wp-support-thisismyurl' )
			);
			array_unshift( $links, $settings_link );
		}

		return $links;
	}

	/**
	 * Clear update cache (useful when manually triggering updates)
	 *
	 * @return void
	 */
	public static function clear_cache(): void {
		delete_transient( 'wps_github_latest_release' );
		delete_site_transient( 'update_plugins' );
	}

	/**
	 * Manual update check (useful for testing)
	 *
	 * @return array|null Release data or null.
	 */
	public static function manual_check(): ?array {
		self::clear_cache();
		return self::get_latest_release();
	}
}
