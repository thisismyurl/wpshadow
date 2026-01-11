<?php
/**
 * Module Registry for Suite Plugin Discovery
 *
 * Manages registration and discovery of Hub and Spoke plugins.
 *
 * @package wp_support_SUPPORT
 * @since 1.2601.71800
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Module Registry Class
 *
 * Handles plugin registration, discovery, and status management.
 */
class WPS_Module_Registry {

	/**
	 * Bundled catalog cache.
	 *
	 * @var array
	 */
	private static array $catalog = array();

	private const OPTION_KEY = 'WPS_registered_modules';

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
		self::load_persisted_modules();

		// Allow plugins to register themselves.
		add_action( 'plugins_loaded', array( __CLASS__, 'discover_modules' ), 5 );

		// Warm bundled catalog early for dashboard/updater.
		add_action( 'plugins_loaded', array( __CLASS__, 'load_catalog' ), 4 );

		// Capture module registrations via action hook.
		add_action( 'WPS_register_module', array( __CLASS__, 'register_from_action' ), 10, 1 );

		// Schedule periodic refresh aligned with WordPress plugin update checks (twice daily).
		add_action( 'init', array( __CLASS__, 'schedule_refresh' ) );
		add_action( 'WPS_refresh_modules', array( __CLASS__, 'refresh_modules' ) );

		// Provide hooks for modules to announce themselves.
		do_action( 'WPS_register_modules' );
	}

	/**
	 * Schedule a recurring refresh of module discovery and catalog loading.
	 * Matches WordPress plugin update frequency (twicedaily).
	 *
	 * @return void
	 */
	public static function schedule_refresh(): void {
		if ( wp_next_scheduled( 'WPS_refresh_modules' ) ) {
			return;
		}

		wp_schedule_event( time() + HOUR_IN_SECONDS, 'twicedaily', 'WPS_refresh_modules' );
	}

	/**
	 * Refresh module cache and catalog.
	 *
	 * @return void
	 */
	public static function refresh_modules(): void {
		self::clear_cache();
		self::discover_modules();
		self::load_catalog();

		$update_fn = is_multisite() ? 'update_site_option' : 'update_option';
		call_user_func( $update_fn, 'WPS_modules_last_refresh', time() );
	}

	/**
	 * Provide a snapshot of scheduled tasks relevant to the suite.
	 *
	 * @return array<string,array<string,mixed>>
	 */
	public static function get_schedule_snapshot(): array {
		$next_refresh = wp_next_scheduled( 'WPS_refresh_modules' ) ?: 0;
		$next_vault   = wp_next_scheduled( 'WPS_vault_queue_runner' ) ?: 0;
		$get_fn       = is_multisite() ? 'get_site_option' : 'get_option';
		$last_refresh = (int) call_user_func( $get_fn, 'WPS_modules_last_refresh', 0 );

		$queue_state  = class_exists( '\WPS\CoreSupport\WPS_Vault' ) ? \WPS\CoreSupport\WPS_Vault::get_queue_state() : array();
		$queue_last   = isset( $queue_state['last_run'] ) ? (int) $queue_state['last_run'] : 0;
		$queue_status = isset( $queue_state['status'] ) ? (string) $queue_state['status'] : 'idle';

		return array(
			'catalog_refresh' => array(
				'label'    => __( 'Catalog refresh', 'plugin-wp-support-thisismyurl' ),
				'hook'     => 'WPS_refresh_modules',
				'next_run' => $next_refresh,
				'last_run' => $last_refresh,
			),
			'vault_queue'     => array(
				'label'       => __( 'Vault queue runner', 'plugin-wp-support-thisismyurl' ),
				'hook'        => 'WPS_vault_queue_runner',
				'next_run'    => $next_vault,
				'last_run'    => $queue_last,
				'queue_state' => $queue_status,
			),
		);
	}

	/**
	 * Register a module.
	 *
	 * @param array $module_data Module information.
	 * @return bool True on success, false on failure.
	 */
	public static function register( array $module_data ): bool {
		$module_data = self::sanitize_module_data( $module_data );

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
				'author'      => '@thisismyurl',
				'author_uri'  => 'https://thisismyurl.com',
				'menu_parent' => 'wp-support',
				'icon'        => 'dashicons-admin-plugins',
				'enabled'     => true,
				'hidden'      => false,
				'capabilities' => array(),
				'suite'        => 'general',
			)
		);

		self::persist_modules();

		return true;
	}

	/**
	 * Register a module via action hook payload.
	 *
	 * @param array $module_data Module information.
	 * @return void
	 */
	public static function register_from_action( array $module_data ): void {
		self::register( $module_data );
	}

	/**
	 * Sanitize module data payload.
	 *
	 * @param array $module_data Raw module data.
	 * @return array
	 */
	private static function sanitize_module_data( array $module_data ): array {
		$module_data['slug']    = isset( $module_data['slug'] ) ? sanitize_key( (string) $module_data['slug'] ) : '';
		$module_data['type']    = isset( $module_data['type'] ) ? sanitize_key( (string) $module_data['type'] ) : 'spoke';
		$module_data['suite']   = isset( $module_data['suite'] ) ? sanitize_text_field( (string) $module_data['suite'] ) : 'general';
		$module_data['name']    = isset( $module_data['name'] ) ? sanitize_text_field( (string) $module_data['name'] ) : '';
		$module_data['version'] = isset( $module_data['version'] ) ? sanitize_text_field( (string) $module_data['version'] ) : '';
		$module_data['file']    = isset( $module_data['file'] ) ? sanitize_text_field( (string) $module_data['file'] ) : '';
		$module_data['path']    = isset( $module_data['path'] ) ? sanitize_text_field( (string) $module_data['path'] ) : '';
		$module_data['url']     = isset( $module_data['url'] ) ? esc_url_raw( (string) $module_data['url'] ) : '';
		$module_data['basename'] = isset( $module_data['basename'] ) ? sanitize_text_field( (string) $module_data['basename'] ) : '';
		$module_data['capabilities'] = isset( $module_data['capabilities'] ) && is_array( $module_data['capabilities'] )
			? array_values( array_map( 'sanitize_key', $module_data['capabilities'] ) )
			: array();

		return $module_data;
	}

	/**
	 * Persist registered modules.
	 *
	 * @return void
	 */
	private static function persist_modules(): void {
		$storage = self::$modules;

		if ( is_multisite() ) {
			update_site_option( self::OPTION_KEY, $storage );
			return;
		}

		update_option( self::OPTION_KEY, $storage );
	}

	/**
	 * Load persisted modules from storage.
	 *
	 * @return void
	 */
	private static function load_persisted_modules(): void {
		$stored = is_multisite()
			? get_site_option( self::OPTION_KEY, array() )
			: get_option( self::OPTION_KEY, array() );

		if ( is_array( $stored ) && ! empty( $stored ) ) {
			self::$modules = $stored;
		}
	}

	/**
	 * Discover and cache active modules.
	 *
	 * @return void
	 */
	public static function discover_modules(): void {
		$persisted = self::$modules;

		// Get cached modules.
		$cache_key = is_multisite() ? 'WPS_modules_network' : 'WPS_modules';
		$cached    = is_multisite()
			? get_site_transient( $cache_key )
			: get_transient( $cache_key );

		if ( false !== $cached && is_array( $cached ) ) {
			self::$modules = array_merge( $persisted, $cached );
			return;
		}

		// Scan for active plugins with WPS Suite identifier.
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

			// Check if it's a WPS Suite plugin (basic heuristic).
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

		// Merge with persisted store for durability.
		self::$modules = array_merge( $persisted, self::$modules );
		self::persist_modules();
	}

	/**
	 * Load catalog (bundled JSON with optional remote override) and cache it.
	 *
	 * @return array Catalog data.
	 */
	public static function load_catalog(): array {
		$cache_key = is_multisite() ? 'WPS_catalog_network' : 'WPS_catalog';
		$cached    = is_multisite()
			? get_site_transient( $cache_key )
			: get_transient( $cache_key );

		if ( false !== $cached && is_array( $cached ) ) {
			self::$catalog = $cached;
			return self::$catalog;
		}

		$catalog    = self::get_bundled_catalog();
		$remote_url = apply_filters( 'WPS_catalog_remote_url', '' );
		$cache_ttl  = (int) apply_filters( 'WPS_catalog_cache_ttl', 5 * MINUTE_IN_SECONDS );

		if ( ! empty( $remote_url ) && self::is_allowed_catalog_url( $remote_url ) ) {
			$remote_catalog = self::fetch_remote_catalog( $remote_url );
			if ( ! empty( $remote_catalog ) ) {
				$catalog = $remote_catalog;
			}
		}

		if ( is_multisite() ) {
			set_site_transient( $cache_key, $catalog, $cache_ttl );
		} else {
			set_transient( $cache_key, $catalog, $cache_ttl );
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
		
		// Check for bundled modules in modules/ directory.
		$bundled_modules = self::get_bundled_modules_from_filesystem();
		
		$result    = array();

		foreach ( $catalog as $entry ) {
			$slug          = $entry['slug'] ?? '';
			$installed_mod = $installed[ $slug ] ?? null;
			$bundled_mod   = $bundled_modules[ $slug ] ?? null;

			if ( empty( $slug ) ) {
				continue;
			}

			// If not registered but exists as bundled module, treat as installed.
			$is_installed = ! empty( $installed_mod ) || ! empty( $bundled_mod );

			$available_version = $entry['version'] ?? '0.0.0';
			$installed_version = $installed_mod['version'] ?? ( $bundled_mod['version'] ?? null );
			$update_available  = false;

			if ( ! empty( $installed_version ) && version_compare( $available_version, $installed_version, '>' ) ) {
				$update_available = true;
			}

			$result[ $slug ] = array_merge(
				$entry,
				array(
					'installed'         => $is_installed,
					'installed_version' => $installed_version,
					'update_available'  => $update_available,
					'enabled'           => $installed_mod ? self::is_enabled( $slug ) : ( $bundled_mod ? true : false ),
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
					'installed_version' => $installed_mod['version'] ?? null,
					'update_available'  => false,
					'enabled'           => self::is_enabled( $slug ),
					'version'           => $installed_mod['version'] ?? '0.0.0',
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
	 * Get modules filtered by type and suite.
	 *
	 * @param string|null $type  Optional module type.
	 * @param string|null $suite Optional suite filter.
	 * @return array
	 */
	public static function get_modules_filtered( ?string $type = null, ?string $suite = null ): array {
		return array_filter(
			self::$modules,
			static function ( $module ) use ( $type, $suite ) {
				if ( $type && ( $module['type'] ?? '' ) !== $type ) {
					return false;
				}

				if ( $suite && ( $module['suite'] ?? '' ) !== $suite ) {
					return false;
				}

				return true;
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
	 * Get bundled modules from filesystem (modules/ directory).
	 *
	 * @return array Modules found in modules/ directory, keyed by slug.
	 */
	private static function get_bundled_modules_from_filesystem(): array {
		$bundled = array();
		$modules_path = defined( 'wp_support_PATH' ) ? wp_support_PATH . 'modules/' : '';

		if ( empty( $modules_path ) || ! is_dir( $modules_path ) ) {
			return $bundled;
		}

		// Check hubs/, spokes/, formats/ directories.
		foreach ( array( 'hubs', 'spokes', 'formats' ) as $type ) {
			$type_path = $modules_path . $type . '/';

			if ( ! is_dir( $type_path ) ) {
				continue;
			}

			// Scan for module directories.
			$modules = array_filter(
				glob( $type_path . '*', GLOB_ONLYDIR ),
				function( $dir ) {
					return is_dir( $dir );
				}
			);

			foreach ( $modules as $module_dir ) {
				$module_name = basename( $module_dir );
				$module_file = $module_dir . '/module.php';

				if ( ! file_exists( $module_file ) ) {
					continue;
				}

				// Parse module constants from file to get version.
				$file_contents = file_get_contents( $module_file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
				$version = '1.0.0';

				// Try to extract version constant.
				if ( preg_match( "/define\s*\(\s*['\"]WPS_\w+_VERSION['\"]\s*,\s*['\"]([\d.]+)['\"]\s*\)/i", $file_contents, $matches ) ) {
					$version = $matches[1];
				}

				// Map directory name to expected plugin slug (e.g., "media" => "media-support-thisismyurl").
				$slug_map = array(
					'media' => 'media-support-thisismyurl',
					'vault' => 'vault-support-thisismyurl',
					'image' => 'image-support-thisismyurl',
				);

				$slug = $slug_map[ $module_name ] ?? ( $module_name . '-support-thisismyurl' );

				$bundled[ $slug ] = array(
					'slug'    => $slug,
					'name'    => ucfirst( $module_name ) . ' Support',
					'type'    => rtrim( $type, 's' ), // hubs => hub, spokes => spoke.
					'version' => $version,
					'path'    => $module_dir,
				);
			}
		}

		return $bundled;
	}

	/**
	 * Determine whether any module declares a capability.
	 *
	 * @param string $capability Capability key.
	 * @return bool
	 */
	public static function module_has_capability( string $capability ): bool {
		$capability = sanitize_key( $capability );

		foreach ( self::$modules as $module ) {
			if ( empty( $module['capabilities'] ) || ! is_array( $module['capabilities'] ) ) {
				continue;
			}

			if ( in_array( $capability, $module['capabilities'], true ) ) {
				return true;
			}
		}

		return false;
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
		$option_key = 'WPS_module_' . $slug;

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
		$option_key = 'WPS_module_' . $slug;

		// Clear module cache.
		$cache_key = is_multisite() ? 'WPS_modules_network' : 'WPS_modules';
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
		$cache_key = is_multisite() ? 'WPS_modules_network' : 'WPS_modules';

		if ( is_multisite() ) {
			delete_site_transient( $cache_key );
		} else {
			delete_transient( $cache_key );
		}

		self::$modules = array();
	}

	/**
	 * Get bundled catalog from installed plugins and fallback JSON.
	 *
	 * Scans the plugins directory for installed plugins matching the pattern
	 * "*-support-thisismyurl" and builds the catalog from them, with fallback
	 * to hardcoded JSON for any missing plugins.
	 *
	 * @return array
	 */
	private static function get_bundled_catalog(): array {
		// First, load hardcoded fallback catalog.
		$fallback_json = '[
			{"slug":"plugin-wp-support-thisismyurl","type":"core","name":"WP Support","description":"Foundation plugin managing all hub and spoke plugins for the thisismyurl plugin suite.","version":"1.2601.73001","author":"@thisismyurl","uri":"https://github.com/thisismyurl/plugin-wp-support-thisismyurl","suite_id":"thisismyurl-media-suite-2026","requires_core":"1.2601.73001","requires_php":"8.1.29","requires_wp":"6.4.0","download_url":"https://github.com/thisismyurl/plugin-wp-support-thisismyurl/archive/refs/heads/main.zip"},
			{"slug":"media-support-thisismyurl","type":"hub","name":"Media","description":"Media Hub providing multi-engine fallback, encryption, and cloud bridge capabilities.","version":"1.0.0","author":"@thisismyurl","uri":"https://github.com/thisismyurl/media-support-thisismyurl","suite_id":"thisismyurl-media-suite-2026","requires_core":"1.2601.73001","requires_php":"8.1.29","requires_wp":"6.4.0","download_url":"https://github.com/thisismyurl/media-support-thisismyurl/archive/refs/heads/main.zip"},
			{"slug":"vault-support-thisismyurl","type":"hub","name":"Vault","description":"The Vault - secure original storage with encryption, compression, and broken link guardian.","version":"1.0.0","author":"@thisismyurl","uri":"https://github.com/thisismyurl/vault-support-thisismyurl","suite_id":"thisismyurl-media-suite-2026","requires_core":"1.2601.73001","requires_php":"8.1.29","requires_wp":"6.4.0","download_url":"https://github.com/thisismyurl/vault-support-thisismyurl/archive/refs/heads/main.zip"},
			{"slug":"image-support-thisismyurl","type":"spoke","name":"Image","description":"Image Hub orchestrating format spokes with Pixel-Sovereign and Smart Focus-Point.","version":"1.2601.71701","author":"@thisismyurl","uri":"https://github.com/thisismyurl/image-support-thisismyurl","suite_id":"thisismyurl-media-suite-2026","requires_hub":"media-support-thisismyurl","requires_core":"1.2601.71818","requires_php":"8.1.29","requires_wp":"6.9.0","basename":"image/module.php","download_url":"https://github.com/thisismyurl/module-images-support-thisismyurl/archive/refs/heads/main.zip"},
			{"slug":"avif-support-thisismyurl","type":"spoke","name":"AVIF Support","description":"AVIF spoke for high-efficiency images with Vault mapping.","version":"1.0.0","author":"@thisismyurl","uri":"https://github.com/thisismyurl/avif-support-thisismyurl","suite_id":"thisismyurl-media-suite-2026","requires_core":"1.2601.71818","requires_php":"8.1.29","requires_wp":"6.4.0","download_url":"https://github.com/thisismyurl/avif-support-thisismyurl/archive/refs/heads/main.zip"},
			{"slug":"webp-support-thisismyurl","type":"spoke","name":"WebP Support","description":"WebP spoke for modern browser delivery with Broken Link Guardian.","version":"1.0.0","author":"@thisismyurl","uri":"https://github.com/thisismyurl/webp-support-thisismyurl","suite_id":"thisismyurl-media-suite-2026","requires_core":"1.2601.71818","requires_php":"8.1.29","requires_wp":"6.4.0","download_url":"https://github.com/thisismyurl/webp-support-thisismyurl/archive/refs/heads/main.zip"},
			{"slug":"heic-support-thisismyurl","type":"spoke","name":"HEIC Support","description":"HEIC spoke with conversion to web-safe formats.","version":"1.0.0","author":"@thisismyurl","uri":"https://github.com/thisismyurl/heic-support-thisismyurl","suite_id":"thisismyurl-media-suite-2026","requires_core":"1.2601.71818","requires_php":"8.1.29","requires_wp":"6.4.0","download_url":"https://github.com/thisismyurl/heic-support-thisismyurl/archive/refs/heads/main.zip"},
			{"slug":"raw-support-thisismyurl","type":"spoke","name":"RAW Support","description":"RAW spoke for ingesting professional formats into the Vault.","version":"1.0.0","author":"@thisismyurl","uri":"https://github.com/thisismyurl/raw-support-thisismyurl","suite_id":"thisismyurl-media-suite-2026","requires_core":"1.2601.71818","requires_php":"8.1.29","requires_wp":"6.4.0","download_url":"https://github.com/thisismyurl/raw-support-thisismyurl/archive/refs/heads/main.zip"},
			{"slug":"svg-support-thisismyurl","type":"spoke","name":"SVG Support","description":"SVG spoke with sanitization and Vault mapping.","version":"1.0.0","author":"@thisismyurl","uri":"https://github.com/thisismyurl/svg-support-thisismyurl","suite_id":"thisismyurl-media-suite-2026","requires_core":"1.2601.71818","requires_php":"8.1.29","requires_wp":"6.4.0","download_url":"https://github.com/thisismyurl/svg-support-thisismyurl/archive/refs/heads/main.zip"},
			{"slug":"tiff-support-thisismyurl","type":"spoke","name":"TIFF Support","description":"TIFF spoke with archival-grade handling and Vault routing.","version":"1.0.0","author":"@thisismyurl","uri":"https://github.com/thisismyurl/tiff-support-thisismyurl","suite_id":"thisismyurl-media-suite-2026","requires_core":"1.2601.71818","requires_php":"8.1.29","requires_wp":"6.4.0","download_url":"https://github.com/thisismyurl/tiff-support-thisismyurl/archive/refs/heads/main.zip"},
			{"slug":"bmp-support-thisismyurl","type":"spoke","name":"BMP Support","description":"BMP spoke for legacy ingestion with scrubbing.","version":"1.0.0","author":"@thisismyurl","uri":"https://github.com/thisismyurl/bmp-support-thisismyurl","suite_id":"thisismyurl-media-suite-2026","requires_core":"1.2601.71818","requires_php":"8.1.29","requires_wp":"6.4.0","download_url":"https://github.com/thisismyurl/bmp-support-thisismyurl/archive/refs/heads/main.zip"}
		]';

		$fallback_catalog = json_decode( $fallback_json, true );
		if ( json_last_error() !== JSON_ERROR_NONE || ! is_array( $fallback_catalog ) ) {
			$fallback_catalog = array();
		}

		// Scan plugins directory for installed *-support-thisismyurl plugins.
		$installed_catalog = self::scan_installed_plugins();

		// Merge installed plugins with fallback catalog.
		// Installed plugins override fallback entries (by slug).
		$merged          = $fallback_catalog;
		$installed_slugs = array();

		foreach ( $installed_catalog as $plugin ) {
			$merged[ $plugin['slug'] ] = $plugin;
			$installed_slugs[]         = $plugin['slug'];
		}

		// Convert to indexed array for consistency.
		return array_values( $merged );
	}

	/**
	 * Scan plugins directory for installed *-support-thisismyurl plugins.
	 *
	 * @return array Array of plugin data.
	 */
	private static function scan_installed_plugins(): array {
		$plugins     = array();
		$plugins_dir = WP_PLUGIN_DIR;

		if ( ! is_dir( $plugins_dir ) ) {
			return $plugins;
		}

		$dir_items = @scandir( $plugins_dir ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		if ( false === $dir_items ) {
			return $plugins;
		}

		foreach ( $dir_items as $item ) {
			// Only look for directories matching "*-support-thisismyurl" pattern.
			if ( ! preg_match( '/^[a-z0-9]+-support-thisismyurl$/i', $item ) ) {
				continue;
			}

			$item_path = $plugins_dir . '/' . $item;
			if ( ! is_dir( $item_path ) ) {
				continue;
			}

			// Find main plugin file in the directory.
			$plugin_file = $item_path . '/' . $item . '.php';
			if ( ! file_exists( $plugin_file ) ) {
				continue;
			}

			// Read plugin headers.
			$plugin_data = get_file_data(
				$plugin_file,
				array(
					'Name'        => 'Plugin Name',
					'Version'     => 'Version',
					'Description' => 'Description',
					'Author'      => 'Author',
					'AuthorURI'   => 'Author URI',
					'PluginURI'   => 'Plugin URI',
					'TextDomain'  => 'Text Domain',
				)
			);

			// Validate this is a WPS Suite plugin.
			if ( empty( $plugin_data['Name'] ) ||
				( strpos( $plugin_data['TextDomain'], 'thisismyurl' ) === false &&
					strpos( $plugin_data['Name'], 'thisismyurl' ) === false ) ) {
				continue;
			}

			// Determine module type based on name pattern.
			$type = 'spoke'; // Default to spoke.
			if ( preg_match( '/^(core|image|video|license)-support-thisismyurl$/i', $item ) ) {
				$type = 'hub'; // Known hub patterns.
			}

			// Build plugin entry.
			$plugins[] = array(
				'slug'          => sanitize_key( $item ),
				'type'          => $type,
				'name'          => sanitize_text_field( $plugin_data['Name'] ),
				'description'   => sanitize_text_field( $plugin_data['Description'] ),
				'version'       => sanitize_text_field( $plugin_data['Version'] ),
				'author'        => sanitize_text_field( $plugin_data['Author'] ),
				'author_uri'    => esc_url_raw( $plugin_data['AuthorURI'] ),
				'uri'           => esc_url_raw( $plugin_data['PluginURI'] ),
				'suite_id'      => 'thisismyurl-media-suite-2026',
				'requires_core' => '1.2601.71818',
				'requires_php'  => '8.1.29',
				'requires_wp'   => '6.4.0',
				'basename'     => sanitize_key( $item ) . '/' . sanitize_key( $item ) . '.php',
				'download_url'  => 'https://github.com/thisismyurl/' . sanitize_key( $item ) . '/archive/refs/heads/main.zip',
			);
		}

		return $plugins;
	}

	/**
	 * Fetch remote catalog with retries and integrity checks.
	 *
	 * @param string $remote_url Catalog URL.
	 * @return array Catalog modules or empty array on failure.
	 */
	private static function fetch_remote_catalog( string $remote_url ): array {
		$allowed_hosts = apply_filters(
			'WPS_catalog_allowed_hosts',
			array( 'thisismyurl.com', 'raw.githubusercontent.com', 'github.com' )
		);

		if ( ! self::is_allowed_catalog_url( $remote_url, $allowed_hosts ) ) {
			do_action(
				'WPS_catalog_fetch_error',
				array(
					'url'    => $remote_url,
					'reason' => 'disallowed_host',
				)
			);
			return array();
		}

		$timeout  = (int) apply_filters( 'WPS_catalog_timeout', 5 );
		$attempts = 2;
		$last_err = null;

		for ( $i = 0; $i < $attempts; $i++ ) {
			$response = wp_remote_get(
				$remote_url,
				array(
					'timeout' => $timeout,
					'headers' => array( 'Accept' => 'application/json' ),
				)
			);

			if ( is_wp_error( $response ) ) {
				$last_err = $response->get_error_message();
				continue;
			}

			if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
				$last_err = 'http_' . wp_remote_retrieve_response_code( $response );
				continue;
			}

			$body    = wp_remote_retrieve_body( $response );
			$decoded = json_decode( $body, true );

			if ( json_last_error() !== JSON_ERROR_NONE || ! is_array( $decoded ) ) {
				$last_err = 'invalid_json';
				continue;
			}

			$modules = self::normalize_catalog( $decoded );
			if ( empty( $modules ) ) {
				$last_err = 'empty_catalog';
				continue;
			}

			if ( ! self::validate_catalog_checksum( $decoded, $modules ) ) {
				$last_err = 'checksum_mismatch';
				continue;
			}

			return $modules;
		}

		do_action(
			'WPS_catalog_fetch_error',
			array(
				'url'    => $remote_url,
				'reason' => $last_err ?? 'unknown',
			)
		);

		return array();
	}

	/**
	 * Validate allowed hosts for catalog URL.
	 *
	 * @param string $url URL to validate.
	 * @param array  $allowed_hosts Allowed hostnames.
	 * @return bool
	 */
	private static function is_allowed_catalog_url( string $url, array $allowed_hosts = array() ): bool {
		$host = wp_parse_url( $url, PHP_URL_HOST );
		if ( empty( $host ) ) {
			return false;
		}

		if ( empty( $allowed_hosts ) ) {
			return true;
		}

		return in_array( strtolower( $host ), array_map( 'strtolower', $allowed_hosts ), true );
	}

	/**
	 * Normalize catalog structure to a modules array.
	 *
	 * @param array $decoded Decoded JSON.
	 * @return array Modules array.
	 */
	private static function normalize_catalog( array $decoded ): array {
		if ( isset( $decoded['modules'] ) && is_array( $decoded['modules'] ) ) {
			return $decoded['modules'];
		}

		// Already a flat array of modules.
		if ( isset( $decoded[0] ) && is_array( $decoded[0] ) ) {
			return $decoded;
		}

		return array();
	}

	/**
	 * Validate catalog checksum when provided.
	 *
	 * @param array $decoded Raw decoded catalog (may include checksum/modules).
	 * @param array $modules Normalized modules array.
	 * @return bool True if valid or not provided, false if mismatch.
	 */
	private static function validate_catalog_checksum( array $decoded, array $modules ): bool {
		if ( empty( $decoded['checksum'] ) ) {
			return true; // No checksum provided, accept.
		}

		$expected = (string) $decoded['checksum'];
		$actual   = hash( 'sha256', wp_json_encode( $modules ) );

		return hash_equals( $expected, $actual );
	}
}


