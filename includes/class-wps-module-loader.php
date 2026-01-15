<?php
/**
 * WPS Module Loader
 *
 * Handles loading and managing independent module repositories.
 * Modules are stored in /modules/{type}/{name}/ and loaded dynamically.
 *
 * @package wp_support
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Module_Loader
 *
 * Manages module discovery, loading, and registry.
 */
class Module_Loader {
	/**
	 * Registry of loaded modules
	 *
	 * @var array
	 */
	private static $modules = array();

	/**
	 * Initialize module loader
	 *
	 * @return void
	 */
	public static function init() {
		// Skip module loading if modules directory doesn't exist (standalone core mode).
		$modules_dir = wp_support_PATH . 'modules/';
		if ( ! is_dir( $modules_dir ) ) {
			// No modules directory - running in standalone core mode.
			return;
		}

		// Load modules immediately since this is called during wp_support_init() which is already on plugins_loaded
		self::load_modules();

		// Then register activation hook for later
		add_action( 'plugins_loaded', array( static::class, 'activate_modules' ), 11 );
	}

	/**
	 * Load all available modules
	 *
	 * @return void
	 */
	public static function load_modules() {
		// Silently return if modules directory doesn't exist (standalone core mode).
		if ( ! is_dir( wp_support_PATH . 'modules/' ) ) {
			return;
		}

		// Load hubs first
		self::load_module_type( 'hubs' );

		// Load spokes second
		self::load_module_type( 'spokes' );

		// Load formats last
		self::load_module_type( 'formats' );

		/**
		 * Action fired after all modules are loaded
		 */
		do_action( 'WPS_modules_loaded', self::$modules );
	}

	/**
	 * Load modules of a specific type
	 *
	 * @param string $type Module type: hubs, spokes, formats.
	 * @return void
	 */
	private static function load_module_type( $type ) {

		$modules_dir = wp_support_PATH . "modules/{$type}/";
		if ( ! is_dir( $modules_dir ) ) {
			return;
		}

		$modules = array_filter(
			glob( $modules_dir . '*', GLOB_ONLYDIR ),
			function ( $dir ) {
				return is_dir( $dir );
			}
		);

		foreach ( $modules as $module_dir ) {

			self::load_module( $module_dir, $type );
		}
	}

	/**
	 * Load an individual module
	 *
	 * @param string $module_dir Full path to module directory.
	 * @param string $type       Module type (hubs, spokes, formats).
	 * @return void
	 */
	private static function load_module( $module_dir, $type ) {
		$module_name = basename( $module_dir );
		$module_id   = "{$type}/{$module_name}";

		// Look for module.php (primary) or {name}.php (legacy)
		$module_file = $module_dir . '/module.php';
		if ( ! file_exists( $module_file ) ) {
			$module_file = $module_dir . "/{$module_name}.php";
		}

		if ( ! file_exists( $module_file ) ) {
			// Silent skip if module file not found
			return;
		}

		// Check if module is enabled before loading.
		// Derive slug from directory name using the naming convention: {dirname}-support-thisismyurl
		// Bundled modules are enabled by default, but can be explicitly disabled.
		$module_slug = $module_name . '-support-thisismyurl';
		if ( ! \WPS\CoreSupport\WPS_Module_Registry::is_enabled( $module_slug ) ) {

			return;
		}

		// Load module file
		require_once $module_file;

		self::$modules[ $module_id ] = array(
			'id'   => $module_id,
			'type' => $type,
			'name' => $module_name,
			'path' => $module_dir,
			'file' => $module_file,
			'url'  => str_replace( '\\', '/', str_replace( ABSPATH, '', $module_dir ) ),
			'url'  => plugins_url( '', $module_file ),
		);
	}

	/**
	 * Activate modules if their auto-load flag is set
	 *
	 * @return void
	 */
	public static function activate_modules() {
		// Modules can register auto-activation hooks during load
		// This fires after plugins_loaded to allow module initialization
	}

	/**
	 * Get all registered modules
	 *
	 * @param string|null $type Optional. Filter by type (hubs, spokes, formats).
	 * @return array
	 */
	public static function get_modules( $type = null ) {
		if ( null === $type ) {
			return self::$modules;
		}

		return array_filter(
			self::$modules,
			function ( $module ) use ( $type ) {
				return $module['type'] === $type;
			}
		);
	}

	/**
	 * Get a specific module
	 *
	 * @param string $module_id Module ID in format: {type}/{name}.
	 * @return array|null
	 */
	public static function get_module( $module_id ) {
		return self::$modules[ $module_id ] ?? null;
	}

	/**
	 * Check if a module is loaded
	 *
	 * @param string $module_id Module ID in format: {type}/{name}.
	 * @return bool
	 */
	public static function has_module( $module_id ) {
		return isset( self::$modules[ $module_id ] );
	}
}
