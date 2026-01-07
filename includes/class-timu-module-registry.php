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
	 * Bundled catalog cache.
	 *
	 * @var array
	 */
	private static array $catalog = array();

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

		// Warm bundled catalog early for dashboard/updater.
		add_action( 'plugins_loaded', array( __CLASS__, 'load_catalog' ), 4 );

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
	 * Load catalog (bundled JSON with optional remote override) and cache it.
	 *
	 * @return array Catalog data.
	 */
	public static function load_catalog(): array {
		$cache_key = is_multisite() ? 'timu_catalog_network' : 'timu_catalog';
		$cached    = is_multisite()
			? get_site_transient( $cache_key )
			: get_transient( $cache_key );

		if ( false !== $cached && is_array( $cached ) ) {
			self::$catalog = $cached;
			return self::$catalog;
		}

		$catalog = self::get_bundled_catalog();

		$remote_url = apply_filters( 'timu_catalog_remote_url', '' );
		if ( ! empty( $remote_url ) ) {
			$response = wp_remote_get(
				$remote_url,
				array(
					'timeout' => 5,
					'headers' => array( 'Accept' => 'application/json' ),
				)
			);

			if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
				$body = wp_remote_retrieve_body( $response );
				$decoded = json_decode( $body, true );

				if ( json_last_error() === JSON_ERROR_NONE && is_array( $decoded ) ) {
					$catalog = $decoded;
				}
			}
		}

		$duration = 5 * MINUTE_IN_SECONDS;
		if ( is_multisite() ) {
			set_site_transient( $cache_key, $catalog, $duration );
		} else {
			set_transient( $cache_key, $catalog, $duration );
		}

		self::$catalog = $catalog;

		return self::$catalog;
	}

	/**
	 * Get catalog modules.
	 *
	 * @return array
	 */
	public static function get_catalog_modules(): array {
		if ( empty( self::$catalog ) ) {
			self::load_catalog();
		}

		return self::$catalog;
	}

	/**
	 * Merge catalog with installed modules to provide status context.
	 *
	 * @return array
	 */
	public static function get_catalog_with_status(): array {
		$catalog   = self::get_catalog_modules();
		$installed = self::get_modules();
		$result    = array();

		foreach ( $catalog as $entry ) {
			$slug          = $entry['slug'] ?? '';
			$installed_mod = $installed[ $slug ] ?? null;

			if ( empty( $slug ) ) {
				continue;
			}

			$available_version = $entry['version'] ?? '0.0.0';
			$installed_version = $installed_mod['version'] ?? null;
			$update_available  = false;

			if ( ! empty( $installed_version ) && version_compare( $available_version, $installed_version, '>' ) ) {
				$update_available = true;
			}

			$result[ $slug ] = array_merge(
				$entry,
				array(
					'installed'         => ! empty( $installed_mod ),
					'installed_version'  => $installed_version,
					'update_available'   => $update_available,
					'enabled'            => $installed_mod ? self::is_enabled( $slug ) : false,
				)
			);
		}

		// Append installed modules that are missing from the catalog.
		foreach ( $installed as $slug => $installed_mod ) {
			if ( isset( $result[ $slug ] ) ) {
				continue;
			}

			$result[ $slug ] = array_merge(
				$installed_mod,
				array(
					'installed'         => true,
					'installed_version'  => $installed_mod['version'] ?? null,
					'update_available'   => false,
					'enabled'            => self::is_enabled( $slug ),
					'version'            => $installed_mod['version'] ?? '0.0.0',
				)
			);
		}

		return $result;
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

	/**
	 * Get bundled catalog JSON as array.
	 *
	 * @return array
	 */
	private static function get_bundled_catalog(): array {
		$json = '[
			{"slug":"core-support-thisismyurl","type":"hub","name":"Core Support","description":"Hub for Multi-Engine Fallback, Vault, and suite governance.","version":"1.2601.71818","author":"Christopher Ross","uri":"https://github.com/thisismyurl/core-support-thisismyurl","suite_id":"thisismyurl-media-suite-2026","requires_core":"1.2601.71818","requires_php":"8.1.29","requires_wp":"6.4.0","download_url":"https://github.com/thisismyurl/core-support-thisismyurl/releases/latest"},
			{"slug":"image-support-thisismyurl","type":"hub","name":"Image Support","description":"Image Hub orchestrating format spokes with Pixel-Sovereign and Smart Focus-Point.","version":"1.2601.71701","author":"Christopher Ross","uri":"https://github.com/thisismyurl/image-support-thisismyurl","suite_id":"thisismyurl-media-suite-2026","requires_core":"1.2601.71818","requires_php":"8.1.29","requires_wp":"6.9.0","download_url":"https://github.com/thisismyurl/image-support-thisismyurl/releases/latest"},
			{"slug":"avif-support-thisismyurl","type":"spoke","name":"AVIF Support","description":"AVIF spoke for high-efficiency images with Vault mapping.","version":"1.0.0","author":"Christopher Ross","uri":"https://github.com/thisismyurl/avif-support-thisismyurl","suite_id":"thisismyurl-media-suite-2026","requires_core":"1.2601.71818","requires_php":"8.1.29","requires_wp":"6.4.0","download_url":"https://github.com/thisismyurl/avif-support-thisismyurl/releases/latest"},
			{"slug":"webp-support-thisismyurl","type":"spoke","name":"WebP Support","description":"WebP spoke for modern browser delivery with Broken Link Guardian.","version":"1.0.0","author":"Christopher Ross","uri":"https://github.com/thisismyurl/webp-support-thisismyurl","suite_id":"thisismyurl-media-suite-2026","requires_core":"1.2601.71818","requires_php":"8.1.29","requires_wp":"6.4.0","download_url":"https://github.com/thisismyurl/webp-support-thisismyurl/releases/latest"},
			{"slug":"heic-support-thisismyurl","type":"spoke","name":"HEIC Support","description":"HEIC spoke with conversion to web-safe formats.","version":"1.0.0","author":"Christopher Ross","uri":"https://github.com/thisismyurl/heic-support-thisismyurl","suite_id":"thisismyurl-media-suite-2026","requires_core":"1.2601.71818","requires_php":"8.1.29","requires_wp":"6.4.0","download_url":"https://github.com/thisismyurl/heic-support-thisismyurl/releases/latest"},
			{"slug":"raw-support-thisismyurl","type":"spoke","name":"RAW Support","description":"RAW spoke for ingesting professional formats into the Vault.","version":"1.0.0","author":"Christopher Ross","uri":"https://github.com/thisismyurl/raw-support-thisismyurl","suite_id":"thisismyurl-media-suite-2026","requires_core":"1.2601.71818","requires_php":"8.1.29","requires_wp":"6.4.0","download_url":"https://github.com/thisismyurl/raw-support-thisismyurl/releases/latest"},
			{"slug":"svg-support-thisismyurl","type":"spoke","name":"SVG Support","description":"SVG spoke with sanitization and Vault mapping.","version":"1.0.0","author":"Christopher Ross","uri":"https://github.com/thisismyurl/svg-support-thisismyurl","suite_id":"thisismyurl-media-suite-2026","requires_core":"1.2601.71818","requires_php":"8.1.29","requires_wp":"6.4.0","download_url":"https://github.com/thisismyurl/svg-support-thisismyurl/releases/latest"},
			{"slug":"tiff-support-thisismyurl","type":"spoke","name":"TIFF Support","description":"TIFF spoke with archival-grade handling and Vault routing.","version":"1.0.0","author":"Christopher Ross","uri":"https://github.com/thisismyurl/tiff-support-thisismyurl","suite_id":"thisismyurl-media-suite-2026","requires_core":"1.2601.71818","requires_php":"8.1.29","requires_wp":"6.4.0","download_url":"https://github.com/thisismyurl/tiff-support-thisismyurl/releases/latest"},
			{"slug":"bmp-support-thisismyurl","type":"spoke","name":"BMP Support","description":"BMP spoke for legacy ingestion with scrubbing.","version":"1.0.0","author":"Christopher Ross","uri":"https://github.com/thisismyurl/bmp-support-thisismyurl","suite_id":"thisismyurl-media-suite-2026","requires_core":"1.2601.71818","requires_php":"8.1.29","requires_wp":"6.4.0","download_url":"https://github.com/thisismyurl/bmp-support-thisismyurl/releases/latest"}
		]';

		$decoded = json_decode( $json, true );

		return ( json_last_error() === JSON_ERROR_NONE && is_array( $decoded ) ) ? $decoded : array();
	}
}
