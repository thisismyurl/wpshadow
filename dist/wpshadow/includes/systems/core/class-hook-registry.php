<?php
/**
 * Hook Registry
 *
 * Auto-discovers and registers all Hook_Subscriber_Base classes.
 * Eliminates the need to manually call ::subscribe() on every class.
 *
 * Philosophy:
 * - Commandment #7: Ridiculously Good (zero manual registration)
 * - Phase 2: Perfect Hooks Pattern (convention over configuration)
 * - DRY: Scan once, register all
 *
 * @package    WPShadow
 * @subpackage Core
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Hook_Registry Class
 *
 * Discovers and registers all hook subscribers automatically.
 *
 * @since 1.6093.1200
 */
class Hook_Registry {

	/**
	 * Directories to scan for hook subscribers.
	 *
	 * @var array
	 */
	private static $scan_directories = array(
		'includes/features/',
		'includes/admin/',
		'includes/content/',
		'includes/systems/',
		'includes/ui/',
	);

	/**
	 * Cache key for discovered subscribers.
	 *
	 * @var string
	 */
	private const CACHE_KEY = 'wpshadow_hook_subscribers';

	/**
	 * Initialize hook registry and subscribe all discovered classes.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function init(): void {
		// Get cached subscribers or discover them
		$subscribers = self::get_cached_subscribers();

		if ( empty( $subscribers ) ) {
			$subscribers = self::discover_subscribers();
			self::cache_subscribers( $subscribers );
		}

		// Subscribe all discovered classes
		self::subscribe_all( $subscribers );
	}

	/**
	 * Discover all classes that extend Hook_Subscriber_Base.
	 *
	 * @since 1.6093.1200
	 * @return array Array of fully qualified class names.
	 */
	private static function discover_subscribers(): array {
		$subscribers = array();
		$base_path   = defined( 'WPSHADOW_PATH' ) ? WPSHADOW_PATH : '';

		if ( empty( $base_path ) ) {
			return $subscribers;
		}

		foreach ( self::$scan_directories as $dir ) {
			$full_path = $base_path . $dir;

			if ( ! is_dir( $full_path ) ) {
				continue;
			}

			// Recursively scan directory for PHP files
			$files = self::scan_directory_recursive( $full_path );

			foreach ( $files as $file ) {
				$class_name = self::file_to_class( $file, $base_path );

				// Check if class exists and extends Hook_Subscriber_Base
				if ( $class_name && class_exists( $class_name ) ) {
					if ( is_subclass_of( $class_name, __CLASS__ . '_Base' ) ) {
						$subscribers[] = $class_name;
					}
				}
			}
		}

		return $subscribers;
	}

	/**
	 * Recursively scan directory for PHP files.
	 *
	 * @since 1.6093.1200
	 * @param  string $directory Directory to scan.
	 * @return array Array of file paths.
	 */
	private static function scan_directory_recursive( string $directory ): array {
		$files   = array();
		$pattern = $directory . '*.php';
		$matches = glob( $pattern );

		if ( $matches ) {
			$files = array_merge( $files, $matches );
		}

		// Scan subdirectories
		$subdirs = glob( $directory . '*/', GLOB_ONLYDIR );
		foreach ( $subdirs as $subdir ) {
			$files = array_merge( $files, self::scan_directory_recursive( $subdir ) );
		}

		return $files;
	}

	/**
	 * Convert file path to fully qualified class name.
	 *
	 * Examples:
	 * - includes/features/academy/class-academy-ui.php -> WPShadow\Academy\Academy_UI
	 * - includes/admin/pages/class-dashboard-page.php -> WPShadow\Admin\Dashboard_Page
	 *
	 * @since 1.6093.1200
	 * @param  string $file      Full file path.
	 * @param  string $base_path Plugin base path.
	 * @return string|null Fully qualified class name or null.
	 */
	private static function file_to_class( string $file, string $base_path ): ?string {
		// Remove base path
		$relative = str_replace( $base_path, '', $file );

		// Remove .php extension
		$relative = str_replace( '.php', '', $relative );

		// Remove includes/ prefix
		$relative = preg_replace( '#^includes/#', '', $relative );

		// Split into parts
		$parts = explode( '/', $relative );

		// Remove 'class-' prefix from filename if present
		$filename = end( $parts );
		$filename = preg_replace( '/^class-/', '', $filename );

		// Convert filename from kebab-case to PascalCase_With_Underscores
		$class_name = implode( '_', array_map( 'ucfirst', explode( '-', $filename ) ) );

		// Build namespace from directory structure
		$namespace_parts = array_slice( $parts, 0, -1 );

		// Convert directory names to namespace format
		$namespace_parts = array_map(
			function ( $part ) {
				// Convert kebab-case to PascalCase
				return implode( '', array_map( 'ucfirst', explode( '-', $part ) ) );
			},
			$namespace_parts
		);

		// Special case: 'features' directory maps to individual feature namespaces
		if ( isset( $namespace_parts[0] ) && 'features' === strtolower( $namespace_parts[0] ) ) {
			array_shift( $namespace_parts ); // Remove 'features'
		}

		// Build full class name
		if ( ! empty( $namespace_parts ) ) {
			$namespace = 'WPShadow\\' . implode( '\\', $namespace_parts );
			return $namespace . '\\' . $class_name;
		}

		return 'WPShadow\\' . $class_name;
	}

	/**
	 * Subscribe all discovered classes to their hooks.
	 *
	 * Respects version gating - only subscribes if class required_version
	 * is met by current WPSHADOW_VERSION.
	 *
	 * @since 1.6093.1200
	 * @param array $subscribers Array of class names.
	 * @return void
	 */
	private static function subscribe_all( array $subscribers ): void {
		foreach ( $subscribers as $class_name ) {
			// Check version gating
			if ( ! self::is_version_met( $class_name ) ) {
				continue;
			}

			if ( method_exists( $class_name, 'subscribe' ) ) {
				$class_name::subscribe();
			}
		}
	}

	/**
	 * Check if current plugin version meets class version requirement.
	 *
	 * @since 1.6093.1200
	 * @param  string $class_name Fully qualified class name.
	 * @return bool True if version requirement met, false otherwise.
	 */
	private static function is_version_met( string $class_name ): bool {
		// Get class required version
		if ( ! method_exists( $class_name, 'get_required_version' ) ) {
			return true; // No version requirement
		}

		$required_version = $class_name::get_required_version();

		if ( empty( $required_version ) ) {
			return true; // No version gate
		}

		// Get current plugin version
		$current_version = defined( 'WPSHADOW_VERSION' ) ? WPSHADOW_VERSION : '1.0.0.0';

		// Compare versions (format: 1.YDDD.HHMM)
		return self::compare_versions( $current_version, $required_version ) >= 0;
	}

	/**
	 * Compare two plugin versions.
	 *
	 * Version format: 1.YDDD.HHMM
	 * Examples:1.0,1.0,1.0
	 *
	 * @since 1.6093.1200
	 * @param  string $current  Current version.
	 * @param  string $required Required version.
	 * @return int Positive if current >= required, negative otherwise.
	 */
	private static function compare_versions( string $current, string $required ): int {
		// Parse versions into components
		$current_parts  = explode( '.', $current );
		$required_parts = explode( '.', $required );

		// Compare each component numerically
		$max_parts = max( count( $current_parts ), count( $required_parts ) );
		for ( $i = 0; $i < $max_parts; $i++ ) {
			$curr = isset( $current_parts[ $i ] ) ? (int) $current_parts[ $i ] : 0;
			$req  = isset( $required_parts[ $i ] ) ? (int) $required_parts[ $i ] : 0;

			if ( $curr > $req ) {
				return 1;
			}
			if ( $curr < $req ) {
				return -1;
			}
		}

		return 0;
	}

	/**
	 * Get cached subscribers from transient.
	 *
	 * @since 1.6093.1200
	 * @return array Cached subscribers or empty array.
	 */
	private static function get_cached_subscribers(): array {
		// Only cache in production (not during development)
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			return array();
		}

		$cached = get_transient( self::CACHE_KEY );
		return is_array( $cached ) ? $cached : array();
	}

	/**
	 * Cache discovered subscribers.
	 *
	 * @since 1.6093.1200
	 * @param array $subscribers Array of class names.
	 * @return void
	 */
	private static function cache_subscribers( array $subscribers ): void {
		// Cache for 1 hour
		set_transient( self::CACHE_KEY, $subscribers, HOUR_IN_SECONDS );
	}

	/**
	 * Clear subscriber cache.
	 *
	 * Useful after plugin updates or when adding new subscribers.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function clear_cache(): void {
		delete_transient( self::CACHE_KEY );
	}

	/**
	 * Manually register a single subscriber.
	 *
	 * Useful for testing or selective registration.
	 *
	 * @since 1.6093.1200
	 * @param string $class_name Fully qualified class name.
	 * @return bool True if subscribed, false otherwise.
	 */
	public static function register_subscriber( string $class_name ): bool {
		if ( ! class_exists( $class_name ) ) {
			return false;
		}

		if ( ! is_subclass_of( $class_name, __NAMESPACE__ . '\\Hook_Subscriber_Base' ) ) {
			return false;
		}

		if ( ! method_exists( $class_name, 'subscribe' ) ) {
			return false;
		}

		$class_name::subscribe();
		return true;
	}
}
