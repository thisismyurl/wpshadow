<?php
/**
 * Update Server Client for WP Support Plugin
 *
 * Handles automatic updates from thisismyurl.com update server.
 * Supports license key validation and tracking for all TIMU plugins.
 *
 * @package    WP_Support
 * @subpackage Core
 * @since      1.2601.73001
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Update Server Client Class
 *
 * Integrates with WordPress plugin update system to check for updates
 * from thisismyurl.com update server instead of WordPress.org.
 *
 * JSON Response Format Expected:
 * {
 *   "success": true,
 *   "license_valid": true,
 *   "license_expires": "2026-12-31",
 *   "plugins": {
 *     "plugin-wp-support-thisismyurl": {
 *       "name": "WP Support",
 *       "version": "1.3.0",
 *       "download_url": "https://thisismyurl.com/downloads/plugin-wp-support-thisismyurl-1.3.0.zip",
 *       "homepage": "https://thisismyurl.com/wp-support/",
 *       "requires": "6.4",
 *       "requires_php": "8.1",
 *       "tested": "6.9",
 *       "last_updated": "2026-01-11 12:00:00",
 *       "description": "Core support functionality...",
 *       "changelog": "<h3>1.3.0</h3><ul><li>Feature X</li></ul>",
 *       "banners": {
 *         "high": "https://thisismyurl.com/banners/wp-support-high.png",
 *         "low": "https://thisismyurl.com/banners/wp-support-low.png"
 *       },
 *       "icons": {
 *         "1x": "https://thisismyurl.com/icons/wp-support.png",
 *         "2x": "https://thisismyurl.com/icons/wp-support@2x.png"
 *       }
 *     },
 *     "plugin-image-hub-thisismyurl": { ... },
 *     "plugin-video-hub-thisismyurl": { ... }
 *   }
 * }
 */
class WPS_Update_Client {

	/**
	 * Update server endpoint URL
	 *
	 * @var string
	 */
	private const UPDATE_SERVER_URL = 'https://thisismyurl.com/api/updates/check.json';

	/**
	 * Plugin basename (e.g., 'plugin-wp-support-thisismyurl/wp-support-thisismyurl.php')
	 *
	 * @var string
	 */
	private static string $plugin_basename = '';

	/**
	 * Initialize the updater
	 *
	 * @param string $plugin_basename The plugin's basename.
	 */
	public static function init( string $plugin_basename ): void {
		self::$plugin_basename = $plugin_basename;

		// Hook into WordPress update system.
		add_filter( 'site_transient_update_plugins', array( __CLASS__, 'check_for_updates' ) );
		add_filter( 'plugins_api', array( __CLASS__, 'plugin_info_api' ), 10, 3 );

		// Add settings link on plugins page.
		add_filter( 'plugin_action_links_' . $plugin_basename, array( __CLASS__, 'add_action_links' ) );

		// Admin AJAX handlers for manual checks.
		add_action( 'wp_ajax_wps_check_updates', array( __CLASS__, 'manual_check' ) );
	}

	/**
	 * Check for plugin updates
	 *
	 * Hooks into WordPress update check to inject update server data.
	 *
	 * @param object $transient Update transient object.
	 * @return object Modified transient object.
	 */
	public static function check_for_updates( $transient ) {
		if ( empty( $transient->checked ) ) {
			return $transient;
		}

		$update_data = self::get_update_data();
		if ( ! $update_data || empty( $update_data['plugins'] ) ) {
			return $transient;
		}

		// Check each plugin in the update data.
		foreach ( $update_data['plugins'] as $plugin_slug => $plugin_info ) {
			// Find the plugin basename.
			$plugin_basename = self::find_plugin_basename( $plugin_slug );
			if ( ! $plugin_basename ) {
				continue;
			}

			// Get current version.
			$plugin_file = WP_PLUGIN_DIR . '/' . $plugin_basename;
			if ( ! file_exists( $plugin_file ) ) {
				continue;
			}

			if ( ! function_exists( 'get_plugin_data' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			$plugin_data     = get_plugin_data( $plugin_file );
			$current_version = $plugin_data['Version'];
			$latest_version  = $plugin_info['version'];

			if ( version_compare( $current_version, $latest_version, '<' ) ) {
				$transient->response[ $plugin_basename ] = (object) array(
					'slug'         => $plugin_slug,
					'plugin'       => $plugin_basename,
					'new_version'  => $latest_version,
					'url'          => $plugin_info['homepage'] ?? '',
					'package'      => $plugin_info['download_url'],
					'tested'       => $plugin_info['tested'] ?? get_bloginfo( 'version' ),
					'requires'     => $plugin_info['requires'] ?? '6.0',
					'requires_php' => $plugin_info['requires_php'] ?? '8.1',
				);
			}
		}

		return $transient;
	}

	/**
	 * Provide plugin information for updates
	 *
	 * Hooks into WordPress plugins API to provide details about our plugins.
	 *
	 * @param false|object|array $result Result object.
	 * @param string             $action Action being performed.
	 * @param object             $args   Arguments for the action.
	 * @return false|object Modified result or false.
	 */
	public static function plugin_info_api( $result, string $action, $args ) {
		if ( 'plugin_information' !== $action ) {
			return $result;
		}

		$update_data = self::get_update_data();
		if ( ! $update_data || empty( $update_data['plugins'] ) ) {
			return $result;
		}

		// Find the requested plugin in update data.
		$plugin_info = null;
		foreach ( $update_data['plugins'] as $plugin_slug => $info ) {
			if ( $args->slug === $plugin_slug ) {
				$plugin_info = $info;
				break;
			}
		}

		if ( ! $plugin_info ) {
			return $result;
		}

		return (object) array(
			'name'          => $plugin_info['name'],
			'slug'          => $args->slug,
			'version'       => $plugin_info['version'],
			'author'        => $plugin_info['author'] ?? 'Christopher Ross',
			'homepage'      => $plugin_info['homepage'] ?? '',
			'requires'      => $plugin_info['requires'] ?? '6.0',
			'requires_php'  => $plugin_info['requires_php'] ?? '8.1',
			'tested'        => $plugin_info['tested'] ?? get_bloginfo( 'version' ),
			'last_updated'  => $plugin_info['last_updated'] ?? '',
			'sections'      => array(
				'description' => $plugin_info['description'] ?? '',
				'changelog'   => $plugin_info['changelog'] ?? '',
			),
			'download_link' => $plugin_info['download_url'],
			'banners'       => $plugin_info['banners'] ?? array(),
			'icons'         => $plugin_info['icons'] ?? array(),
		);
	}

	/**
	 * Get updates from thisismyurl.com server
	 *
	 * Fetches update information for all TIMU plugins from update server.
	 * Caches the result for 6 hours to minimize server load.
	 *
	 * @return array|false Update data or false on failure.
	 */
	private static function get_update_data() {
		$transient_key = 'wps_update_data';
		$cached        = get_transient( $transient_key );

		if ( false !== $cached ) {
			return $cached;
		}

		// Build request with site info and license key.
		$args = array(
			'timeout' => 15,
			'body'    => array(
				'site_url'    => home_url(),
				'license_key' => self::get_license_key(),
				'plugins'     => self::get_installed_plugins(),
				'wp_version'  => get_bloginfo( 'version' ),
				'php_version' => phpversion(),
			),
		);

		$response = wp_remote_post( self::UPDATE_SERVER_URL, $args );

		if ( is_wp_error( $response ) ) {
			error_log( 'WPS Update Server Error: ' . $response->get_error_message() );
			return false;
		}

		$code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $code ) {
			error_log( 'WPS Update Server returned HTTP ' . $code );
			return false;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( empty( $data ) || ! is_array( $data ) ) {
			return false;
		}

		// Cache for 6 hours.
		set_transient( $transient_key, $data, 6 * HOUR_IN_SECONDS );

		return $data;
	}

	/**
	 * Get license key
	 *
	 * Checks for license key in WordPress options or environment variable.
	 *
	 * @return string License key or empty string.
	 */
	private static function get_license_key(): string {
		// Check WordPress option first.
		$key = get_option( 'wps_license_key' );
		if ( ! empty( $key ) ) {
			return $key;
		}

		// Check constant.
		if ( defined( 'WPS_LICENSE_KEY' ) ) {
			return WPS_LICENSE_KEY;
		}

		// Check environment variable.
		if ( isset( $_ENV['WPS_LICENSE_KEY'] ) ) {
			return sanitize_text_field( wp_unslash( $_ENV['WPS_LICENSE_KEY'] ) );
		}

		return '';
	}

	/**
	 * Get list of installed TIMU/WPS plugins
	 *
	 * @return array Plugin slugs and versions.
	 */
	private static function get_installed_plugins(): array {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$all_plugins   = get_plugins();
		$installed     = array();
		$timu_prefixes = array( 'plugin-wp-support', 'plugin-image-hub', 'plugin-video-hub' );

		foreach ( $all_plugins as $plugin_file => $plugin_data ) {
			$slug = dirname( $plugin_file );

			// Check if it's a TIMU plugin.
			foreach ( $timu_prefixes as $prefix ) {
				if ( 0 === strpos( $slug, $prefix ) ) {
					$installed[ $slug ] = $plugin_data['Version'];
					break;
				}
			}
		}

		return $installed;
	}

	/**
	 * Find plugin basename from slug
	 *
	 * @param string $slug Plugin slug.
	 * @return string|false Plugin basename or false.
	 */
	private static function find_plugin_basename( string $slug ) {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$all_plugins = get_plugins();
		foreach ( $all_plugins as $plugin_file => $plugin_data ) {
			if ( dirname( $plugin_file ) === $slug ) {
				return $plugin_file;
			}
		}

		return false;
	}

	/**
	 * Add action links to plugin list
	 *
	 * @param array $links Existing action links.
	 * @return array Modified action links.
	 */
	public static function add_action_links( array $links ): array {
		$settings_link = sprintf(
			'<a href="%s">%s</a>',
			esc_url( admin_url( 'admin.php?page=wp-support&tab=updates' ) ),
			esc_html__( 'Updates & License', 'plugin-wp-support-thisismyurl' )
		);

		array_unshift( $links, $settings_link );

		return $links;
	}

	/**
	 * Clear cached update data
	 *
	 * Useful for testing or forcing an immediate update check.
	 */
	public static function clear_cache(): void {
		delete_transient( 'wps_update_data' );
		delete_site_transient( 'update_plugins' );
	}

	/**
	 * Manual update check via AJAX
	 */
	public static function manual_check(): void {
		check_ajax_referer( 'wps_check_updates', 'nonce' );

		if ( ! current_user_can( 'update_plugins' ) ) {
			wp_send_json_error( array( 'message' => 'Insufficient permissions' ) );
		}

		self::clear_cache();

		$update_data = self::get_update_data();

		if ( $update_data ) {
			wp_send_json_success( array(
				'message'       => 'Updates checked successfully',
				'license_valid' => $update_data['license_valid'] ?? false,
				'plugins'       => count( $update_data['plugins'] ?? array() ),
			) );
		} else {
			wp_send_json_error( array( 'message' => 'Failed to check for updates' ) );
		}
	}
}
