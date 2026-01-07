<?php
/**
 * Module Registry for Suite Plugin Discovery
 *
 * Manages registration and discovery of Hub and Spoke plugins.
 *
 * @package TIMU_CORE_SUPPORT
 * @since 1.2601.71800
 */

declare(strict_types=1);

namespace TIMU\CoreSupport;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Module Registry Class
 *
 * Handles plugin registration, discovery, and status management.
 */
class TIMU_Module_Registry {

	/**
	 * Registered modules storage.
	 *
	 * @var array
	 */
	private static array $modules = array();

	/**
	 * Initialize the registry.
	 *
	 * @return void
	 */
	public static function init(): void {
		// Allow plugins to register themselves.
		add_action( 'plugins_loaded', array( __CLASS__, 'discover_modules' ), 5 );

		// Provide hooks for modules to announce themselves.
		do_action( 'timu_register_modules' );
	}

	/**
	 * Register a module.
	 *
	 * @param array $module_data Module information.
	 * @return bool True on success, false on failure.
	 */
	public static function register( array $module_data ): bool {
		// Validate required fields.
		$required = array( 'type', 'slug', 'name', 'version', 'file' );
		foreach ( $required as $field ) {
			if ( empty( $module_data[ $field ] ) ) {
				return false;
			}
		}

		// Store module.
		self::$modules[ $module_data['slug'] ] = wp_parse_args(
			$module_data,
			array(
				'type'        => 'spoke',
				'category'    => 'general',
				'description' => '',
				'author'      => 'Christopher Ross',
				'author_uri'  => 'https://thisismyurl.com',
				'menu_parent' => 'timu-core-support',
				'icon'        => 'dashicons-admin-plugins',
				'enabled'     => true,
				'hidden'      => false,
			)
		);

		return true;
	}

	/**
	 * Discover and cache active modules.
	 *
	 * @return void
	 */
	public static function discover_modules(): void {
		// Get cached modules.
		$cache_key = is_multisite() ? 'timu_modules_network' : 'timu_modules';
		$cached    = is_multisite()
			? get_site_transient( $cache_key )
			: get_transient( $cache_key );

		if ( false !== $cached && is_array( $cached ) ) {
			self::$modules = $cached;
			return;
		}

		// Scan for active plugins with TIMU suite identifier.
		$active_plugins = is_multisite()
			? array_keys( get_site_option( 'active_sitewide_plugins', array() ) )
			: get_option( 'active_plugins', array() );

		foreach ( $active_plugins as $plugin_file ) {
			$plugin_path = WP_PLUGIN_DIR . '/' . $plugin_file;

			if ( ! file_exists( $plugin_path ) ) {
				continue;
			}

			// Read plugin headers.
			$plugin_data = get_file_data(
				$plugin_path,
				array(
					'Name'        => 'Plugin Name',
					'Version'     => 'Version',
					'Description' => 'Description',
					'Author'      => 'Author',
					'AuthorURI'   => 'Author URI',
					'TextDomain'  => 'Text Domain',
				)
			);

			// Check if it's a TIMU suite plugin (basic heuristic).
			if ( strpos( $plugin_data['TextDomain'], 'thisismyurl' ) !== false ||
			     strpos( $plugin_data['Name'], 'thisismyurl' ) !== false ) {

				$slug = dirname( $plugin_file );
				if ( $slug === '.' ) {
					$slug = basename( $plugin_file, '.php' );
				}

				// Auto-register discovered module if not already registered.
				if ( ! isset( self::$modules[ $slug ] ) ) {
					self::register(
						array(
							'type'        => 'spoke',
							'slug'        => $slug,
							'name'        => $plugin_data['Name'],
							'version'     => $plugin_data['Version'],
							'description' => $plugin_data['Description'],
							'author'      => $plugin_data['Author'],
							'author_uri'  => $plugin_data['AuthorURI'],
							'file'        => $plugin_file,
						)
					);
				}
			}
		}

		// Cache for 5 minutes.
		$cache_duration = 5 * MINUTE_IN_SECONDS;
		if ( is_multisite() ) {
			set_site_transient( $cache_key, self::$modules, $cache_duration );
		} else {
			set_transient( $cache_key, self::$modules, $cache_duration );
		}
	}

	/**
	 * Get all registered modules.
	 *
	 * @param string $type Optional. Filter by type (hub, spoke).
	 * @return array Registered modules.
	 */
	public static function get_modules( string $type = '' ): array {
		if ( empty( $type ) ) {
			return self::$modules;
		}

		return array_filter(
			self::$modules,
			function ( $module ) use ( $type ) {
				return isset( $module['type'] ) && $module['type'] === $type;
			}
		);
	}

	/**
	 * Get a specific module.
	 *
	 * @param string $slug Module slug.
	 * @return array|null Module data or null if not found.
	 */
	public static function get_module( string $slug ): ?array {
		return self::$modules[ $slug ] ?? null;
	}

	/**
	 * Check if a module is enabled.
	 *
	 * @param string $slug Module slug.
	 * @return bool True if enabled, false otherwise.
	 */
	public static function is_enabled( string $slug ): bool {
		$settings = self::get_module_settings( $slug );
		return (bool) ( $settings['enabled'] ?? true );
	}

	/**
	 * Get module settings.
	 *
	 * @param string $slug Module slug.
	 * @return array Module settings.
	 */
	public static function get_module_settings( string $slug ): array {
		$option_key = 'timu_module_' . $slug;

		if ( is_multisite() ) {
			$network_settings = get_site_option( $option_key, array() );
			$site_settings    = get_option( $option_key, array() );

			// Merge with site settings taking precedence where allowed.
			return array_merge( $network_settings, $site_settings );
		}

		return get_option( $option_key, array( 'enabled' => true ) );
	}

	/**
	 * Update module settings.
	 *
	 * @param string $slug Module slug.
	 * @param array  $settings Settings to update.
	 * @param bool   $network Whether to update network-wide settings.
	 * @return bool True on success, false on failure.
	 */
	public static function update_module_settings( string $slug, array $settings, bool $network = false ): bool {
		$option_key = 'timu_module_' . $slug;

		// Clear module cache.
		$cache_key = is_multisite() ? 'timu_modules_network' : 'timu_modules';
		if ( is_multisite() ) {
			delete_site_transient( $cache_key );
		} else {
			delete_transient( $cache_key );
		}

		if ( $network && is_multisite() ) {
			return update_site_option( $option_key, $settings );
		}

		return update_option( $option_key, $settings );
	}

	/**
	 * Clear the module cache.
	 *
	 * @return void
	 */
	public static function clear_cache(): void {
		$cache_key = is_multisite() ? 'timu_modules_network' : 'timu_modules';

		if ( is_multisite() ) {
			delete_site_transient( $cache_key );
		} else {
			delete_transient( $cache_key );
		}

		self::$modules = array();
	}
}
