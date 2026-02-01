<?php
/**
 * Diagnostic Registry
 *
 * Manages auto-discovery and registration of all diagnostic classes.
 * Scans subdirectories (tests/, help/, todo/) for diagnostic files.
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Abstract_Registry;

/**
 * Registry for managing diagnostics
 *
 * Auto-discovers diagnostic classes from subdirectories and provides
 * access to them for scanning operations.
 */
class Diagnostic_Registry extends Abstract_Registry {
	/**
	 * Cached list of diagnostic classes
	 *
	 * @var array|null
	 */
	private static $diagnostics_cache = null;

	/**
	 * Cached diagnostic file map
	 *
	 * @var array|null
	 */
	private static $diagnostic_file_map = null;

	/**
	 * Get the list of registered items.
	 *
	 * Discovers diagnostic classes from tests/, help/, and todo/ subdirectories.
	 *
	 * @return array Array of class names.
	 */
	protected static function get_registered_items() {
		if ( null !== self::$diagnostics_cache ) {
			return self::$diagnostics_cache;
		}

		self::$diagnostics_cache = self::discover_diagnostics();

		return self::$diagnostics_cache;
	}

	/**
	 * Get the namespace for registered items.
	 *
	 * @return string Namespace prefix.
	 */
	protected static function get_namespace() {
		return __NAMESPACE__ . '\\';
	}

	/**
	 * Discover all diagnostic classes from subdirectories
	 *
	 * Scans tests/, help/, todo/, verified/ directories recursively for class files.
	 * Captures both class-diagnostic-* and class-test-* patterns.
	 *
	 * @return array Array of diagnostic class names
	 */
	private static function discover_diagnostics(): array {
		$file_map = self::get_diagnostic_file_map();
		return array_keys( $file_map );
	}

	/**
	 * Get diagnostic file map (class name => file path + family)
	 *
	 * @since  1.26030.0135
	 * @return array<string, array{file: string, family: string}> Diagnostic file map.
	 */
	public static function get_diagnostic_file_map(): array {
		if ( null !== self::$diagnostic_file_map ) {
			return self::$diagnostic_file_map;
		}

		$cached = get_transient( 'wpshadow_diagnostic_file_map' );
		if ( is_array( $cached ) && ! empty( $cached ) ) {
			self::$diagnostic_file_map = $cached;
			return self::$diagnostic_file_map;
		}

		$map = self::build_diagnostic_file_map();
		set_transient( 'wpshadow_diagnostic_file_map', $map, DAY_IN_SECONDS );

		self::$diagnostic_file_map = $map;
		return self::$diagnostic_file_map;
	}

	/**
	 * Build diagnostic file map by scanning diagnostics directory
	 *
	 * @since  1.26030.0135
	 * @return array<string, array{file: string, family: string}> Diagnostic file map.
	 */
	private static function build_diagnostic_file_map(): array {
		$map     = array();
		$base    = __DIR__;
		$subdirs = array( 'tests', 'help', 'todo', 'verified' );

		foreach ( $subdirs as $subdir ) {
			$dir = $base . '/' . $subdir;
			if ( ! is_dir( $dir ) ) {
				continue;
			}

			$iterator = new \RecursiveIteratorIterator(
				new \RecursiveDirectoryIterator( $dir, \FilesystemIterator::SKIP_DOTS )
			);

			foreach ( $iterator as $file_info ) {
				/** @var \SplFileInfo $file_info */
				if ( ! $file_info->isFile() ) {
					continue;
				}

				$filename = $file_info->getFilename();
				if ( 0 !== strpos( $filename, 'class-diagnostic-' ) && 0 !== strpos( $filename, 'class-test-' ) ) {
					continue;
				}

				if ( 'php' !== $file_info->getExtension() ) {
					continue;
				}

				$class_name = self::get_class_name_from_file( $file_info->getPathname() );
				if ( ! $class_name ) {
					continue;
				}

				if ( isset( $map[ $class_name ] ) ) {
					continue;
				}

				$family = self::get_family_from_path( $file_info->getPathname() );
				$map[ $class_name ] = array(
					'file'   => $file_info->getPathname(),
					'family' => $family,
				);
			}
		}

		/**
		 * Filters the diagnostic file map.
		 *
		 * @since 1.26030.0135
		 *
		 * @param array<string, array{file: string, family: string}> $map Diagnostic file map.
		 */
		return apply_filters( 'wpshadow_diagnostic_file_map', $map );
	}

	/**
	 * Derive diagnostic family from file path.
	 *
	 * @since  1.26030.0135
	 * @param  string $path Diagnostic file path.
	 * @return string Family slug.
	 */
	private static function get_family_from_path( string $path ): string {
		if ( preg_match( '#/tests/([^/]+)/#', $path, $matches ) ) {
			return sanitize_key( $matches[1] );
		}

		return 'uncategorized';
	}

	/**
	 * Extract class name from file path
	 *
	 * Converts 'class-diagnostic-ssl.php' to 'Diagnostic_Ssl'
	 *
	 * @param string $file File path
	 * @return string|null Class name or null if unable to extract
	 */
	private static function get_class_name_from_file( $file ): ?string {
		$basename = basename( $file, '.php' );

		// Remove 'class-' prefix
		if ( strpos( $basename, 'class-' ) === 0 ) {
			$basename = substr( $basename, 6 );
		}

		// Convert 'diagnostic-ssl' to 'Diagnostic_Ssl'
		$parts = explode( '-', $basename );
		$parts = array_map( 'ucfirst', $parts );

		return implode( '_', $parts );
	}

	/**
	 * Initialize and load all diagnostic classes
	 *
	 * Called during plugins_loaded. Loads all discovered diagnostic files.
	 *
	 * @since 1.2601.2148
	 * @return void
	 */
	public static function init(): void {
		// Don't load all diagnostics at once (causes memory exhaustion)
		// They will be loaded on-demand when needed

		// Initialize cache clearing hooks
		self::init_hooks();
	}

	/**
	 * Load a specific diagnostic class file
	 *
	 * Loads the class file for a given diagnostic class name if it hasn't been loaded yet.
	 *
	 * @param string $class_name Class name without namespace (e.g., "Diagnostic_Ssl")
	 * @return bool True if loaded or already exists, false otherwise
	 */
	private static function load_diagnostic_class( string $class_name ): bool {
		// Check if class already exists
		if ( class_exists( __NAMESPACE__ . '\\' . $class_name ) ) {
			return true;
		}

		// Find and load the class file
		$file = self::find_diagnostic_file( $class_name );
		if ( $file && file_exists( $file ) ) {
			require_once $file;
			return class_exists( __NAMESPACE__ . '\\' . $class_name );
		}

		return false;
	}

	/**
	 * Find the file for a diagnostic class
	 *
	 * Searches for the class file in the diagnostic directories.
	 *
	 * @param string $class_name Class name (e.g., "Diagnostic_Ssl")
	 * @return string|null File path if found, null otherwise
	 */
	private static function find_diagnostic_file( string $class_name ): ?string {
		$map = self::get_diagnostic_file_map();
		if ( isset( $map[ $class_name ]['file'] ) ) {
			return $map[ $class_name ]['file'];
		}

		return null;
	}

	/**
	 * Get all diagnostics as instantiated objects
	 *
	 * For compatibility with code that expects objects instead of class names.
	 *
	 * @return array Array of diagnostic class names (fully qualified)
	 */
	public static function get_all(): array {
		$class_names = static::get_registered_items();
		$namespace   = static::get_namespace();
		$qualified   = array();

		foreach ( $class_names as $class_name ) {
			$qualified[] = $namespace . $class_name;
		}

		return $qualified;
	}

	/**
	 * Get diagnostics for quick scan
	 *
	 * Returns high-value, low-impact diagnostics suitable for frequent runs.
	 *
	 * @return array Array of diagnostic class names
	 */
	public static function get_quick_scan_diagnostics(): array {
		// Filter for quick scan suitable diagnostics
		// For now, return all until we have a classification system
		return self::get_all();
	}

	/**
	 * Get diagnostics for deep scan
	 *
	 * Returns comprehensive diagnostics including expensive checks.
	 *
	 * @return array Array of diagnostic class names
	 */
	public static function get_deep_scan_diagnostics(): array {
		return self::get_all();
	}

	/**
	 * Run quick scan diagnostics
	 *
	 * Executes quick scan diagnostics and returns findings.
	 *
	 * @return array Array of findings
	 */
	public static function run_quickscan_checks(): array {
		return self::run_checks( self::get_quick_scan_diagnostics() );
	}

	/**
	 * Run deep scan diagnostics
	 *
	 * Executes all diagnostics and returns findings.
	 *
	 * @return array Array of findings
	 */
	public static function run_deepscan_checks(): array {
		return self::run_checks( self::get_deep_scan_diagnostics() );
	}

	/**
	 * Run enabled scan diagnostics
	 *
	 * Executes diagnostics that are enabled in user settings.
	 *
	 * @return array Array of findings
	 */
	public static function run_enabled_scans(): array {
		return self::run_checks( self::get_all() );
	}

	/**
	 * Get all diagnostics
	 *
	 * For compatibility with existing code that calls get_diagnostics().
	 *
	 * @return array Array of diagnostic class names
	 */
	public static function get_diagnostics(): array {
		return self::get_all();
	}

	/**
	 * Run checks for given diagnostics
	 *
	 * Executes each diagnostic and collects findings.
	 *
	 * @param array $diagnostic_classes Array of diagnostic class names
	 * @return array Array of findings
	 */
	private static function run_checks( array $diagnostic_classes ): array {
		$findings = array();

		// Read disabled diagnostics from settings (fully-qualified class names)
		$disabled = get_option( 'wpshadow_disabled_diagnostic_classes', array() );
		if ( ! is_array( $disabled ) ) {
			$disabled = array();
		}

		foreach ( $diagnostic_classes as $class_name ) {
			if ( ! class_exists( $class_name ) ) {
				$loaded = self::load_diagnostic_class( str_replace( __NAMESPACE__ . '\\', '', $class_name ) );
				if ( ! $loaded ) {
					continue;
				}
			}

			// Check if diagnostic is enabled (apply filter for external control)
			$enabled = ! in_array( $class_name, $disabled, true );
			/**
			 * Filters whether a diagnostic is enabled.
			 *
			 * @since 1.2601.2148
			 *
			 * @param bool   $enabled    Whether the diagnostic is enabled.
			 * @param string $class_name Fully-qualified diagnostic class name.
			 */
			$enabled = apply_filters( 'wpshadow_diagnostic_enabled', $enabled, $class_name );

			if ( ! $enabled ) {
				continue;
			}

			// Call the static check() method if it exists
			if ( method_exists( $class_name, 'check' ) ) {
				try {
					$result = call_user_func( array( $class_name, 'check' ) );

					if ( null !== $result ) {
						$findings[] = $result;
					}
				} catch ( \Exception $e ) {
					// Log error but continue processing
					error_log( 'Diagnostic error in ' . $class_name . ': ' . $e->getMessage() );
				}
			}
		}

		return $findings;
	}

	/**
	 * Clear diagnostic cache
	 *
	 * Call this if diagnostics are added/removed dynamically.
	 * Also clears the file map transient.
	 *
	 * @since 1.2601.2148
	 * @return void
	 */
	public static function clear_cache(): void {
		self::$diagnostics_cache   = null;
		self::$diagnostic_file_map = null;
		delete_transient( 'wpshadow_diagnostic_file_map' );
	}

	/**
	 * Initialize hooks for cache clearing
	 *
	 * Automatically clear cache when plugins/themes are updated.
	 *
	 * @since 1.26030.2046
	 * @return void
	 */
	public static function init_hooks(): void {
		// Clear cache when plugins are activated/deactivated/updated
		add_action( 'activated_plugin', array( __CLASS__, 'clear_cache' ) );
		add_action( 'deactivated_plugin', array( __CLASS__, 'clear_cache' ) );
		add_action( 'upgrader_process_complete', array( __CLASS__, 'clear_cache' ) );

		// Clear cache on WPShadow plugin update
		add_action( 'upgrader_process_complete', array( __CLASS__, 'handle_plugin_update' ), 10, 2 );
	}

	/**
	 * Handle plugin update to clear cache
	 *
	 * @since 1.26030.2046
	 * @param \WP_Upgrader $upgrader WP_Upgrader instance.
	 * @param array        $options  Update options.
	 * @return void
	 */
	public static function handle_plugin_update( $upgrader, $options ): void {
		if ( 'update' === $options['action'] && 'plugin' === $options['type'] ) {
			self::clear_cache();
		}
	}
}
