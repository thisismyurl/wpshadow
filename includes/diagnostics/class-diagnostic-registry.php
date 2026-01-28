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
		$diagnostics = array();
		$base_dir    = __DIR__;
		$subdirs     = array( 'tests', 'help', 'todo', 'verified' );

		foreach ( $subdirs as $subdir ) {
			$dir = $base_dir . '/' . $subdir;

			if ( ! is_dir( $dir ) ) {
				continue;
			}

			// Get all class-diagnostic-*.php and class-test-*.php files recursively
			$diagnostic_files = glob( $dir . '/**/class-diagnostic-*.php', GLOB_BRACE );
			$test_files       = glob( $dir . '/**/class-test-*.php', GLOB_BRACE );
			// Also get files in the root of the directory
			$diagnostic_root  = glob( $dir . '/class-diagnostic-*.php' );
			$test_root        = glob( $dir . '/class-test-*.php' );
			$files            = array_merge( (array) $diagnostic_files, (array) $test_files, (array) $diagnostic_root, (array) $test_root );

			if ( empty( $files ) ) {
				continue;
			}

			foreach ( $files as $file ) {
				$class_name = self::get_class_name_from_file( $file );

				if ( $class_name ) {
					$diagnostics[] = $class_name;
				}
			}
		}

		return $diagnostics;
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
	 */
	public static function init(): void {
		// Don't load all diagnostics at once (causes memory exhaustion)
		// They will be loaded on-demand when needed
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
		$base_dir = __DIR__;
		$subdirs  = array( 'tests', 'help', 'todo', 'verified' );

		// Convert class name to file name (e.g., "Diagnostic_Ssl" -> "class-diagnostic-ssl.php")
		$class_file = 'class-' . str_replace( '_', '-', strtolower( $class_name ) ) . '.php';

		foreach ( $subdirs as $subdir ) {
			$dir = $base_dir . '/' . $subdir;

			if ( ! is_dir( $dir ) ) {
				continue;
			}

			// Search recursively for the file
			$files = glob( $dir . '/**/' . $class_file, GLOB_BRACE );
			if ( ! empty( $files ) && file_exists( $files[0] ) ) {
				return $files[0];
			}

			// Also check in root of directory
			$root_file = $dir . '/' . $class_file;
			if ( file_exists( $root_file ) ) {
				return $root_file;
			}
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
				continue;
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
	 */
	public static function clear_cache(): void {
		self::$diagnostics_cache = null;
	}
}
