<?php
/**
 * Bootstrap Autoloader
 *
 * Automatically loads all WPShadow classes in dependency order.
 * Replaces 130+ manual require_once calls with intelligent auto-loading.
 *
 * Philosophy:
 * - Commandment #7: Ridiculously Good (zero manual loading)
 * - Phase 4: Bootstrap Consolidation (DRY bootstrap)
 * - Convention over configuration
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
 * Bootstrap_Autoloader Class
 *
 * Intelligently loads all WPShadow classes with proper dependency ordering.
 *
 * @since 1.6093.1200
 */
class Bootstrap_Autoloader {

	/**
	 * Critical classes that must load before everything else.
	 *
	 * These are loaded in order, as each may depend on the previous.
	 *
	 * @var array
	 */
	private static $critical_classes = array(
		// Phase 2 infrastructure (loads first)
		'includes/systems/core/class-hook-subscriber-base.php',
		'includes/systems/core/class-hook-registry.php',
		
		// Base classes (required by many features)
		'includes/systems/core/class-ajax-handler-base.php',
		'includes/systems/core/class-diagnostic-base.php',
		'includes/systems/core/class-treatment-interface.php',
		'includes/systems/core/class-treatment-base.php',
		
		// Admin base classes
		'includes/admin/pages/class-settings-page-base.php',
		
		// Core utilities
		'includes/systems/core/class-security-validator.php',
		'includes/systems/core/class-secret-manager.php',
		'includes/systems/core/class-secret-audit-log.php',
		'includes/systems/core/class-activity-logger.php',
		'includes/systems/core/class-error-handler.php',
		'includes/systems/core/class-cache-manager.php',
		'includes/systems/core/class-kpi-tracker.php',
		'includes/systems/core/class-settings-registry.php',
		'includes/systems/core/class-admin-asset-registry.php',
		'includes/systems/core/class-database-migrator.php',
		'includes/systems/core/class-form-param-helper.php',
		'includes/systems/core/class-options-manager.php',
		'includes/systems/core/class-abstract-registry.php',
		'includes/systems/core/class-upgrade-path-helper.php',
		'includes/systems/core/class-utm-link-manager.php',
		'includes/systems/core/class-finding-utils.php',
		'includes/core/class-version-checker.php',
		
		// Plugin initialization
		'includes/systems/core/class-plugin-bootstrap.php',
		
		// Diagnostic registry
		'includes/systems/diagnostics/class-diagnostic-registry.php',
		
		// Treatment functions
		'includes/systems/core/functions-treatment.php',
		'includes/systems/core/class-category-metadata.php',
		
		// Helper functions
		'includes/utils/helpers/form-controls.php',
		'includes/utils/helpers/html-fetcher-helpers.php',
		'includes/utils/helpers/findings-cache-helpers.php',
		'includes/utils/helpers/feature-status-helpers.php',
		
		// View functions
		'includes/ui/templates/functions-page-layout.php',
		'includes/ui/templates/menu-stubs.php',
		'includes/ui/templates/dashboard-page.php',
		'includes/ui/dashboard/gauges-module.php',
		
		// Backup/recovery
		'includes/features/monitoring/recovery/class-backup-manager.php',
		'includes/features/monitoring/recovery/class-backup-scheduler.php',
		
		// Menu and routing
		'includes/systems/core/class-menu-manager.php',
		'includes/systems/core/class-ajax-router.php',
		'includes/systems/core/class-hooks-initializer.php',
		'includes/systems/dashboard/class-asset-manager.php',
		'includes/systems/dashboard/class-asset-optimizer.php',
		
		// Monitoring/tracking
		'includes/features/monitoring/class-wordpress-hooks-tracker.php',
		
		// Privacy (required by AJAX handlers)
		'includes/utils/privacy/class-consent-preferences.php',
		'includes/utils/privacy/class-first-run-consent.php',
		
		// AJAX handlers loader
		'includes/admin/ajax/ajax-handlers-loader.php',
	);

	/**
	 * Feature directories to auto-load.
	 *
	 * All PHP files in these directories will be loaded automatically.
	 *
	 * @var array
	 */
	private static $feature_directories = array(
		'includes/content/post-types/',
		'includes/content/',
		'includes/blocks/',
		// 'includes/utils/', // Commented out - has parse errors in class-treatment-hooks.php
		'includes/admin/',
		'includes/analytics/',
		'includes/features/',
	);

	/**
	 * Cache key for loaded files.
	 *
	 * @var string
	 */
	private const CACHE_KEY = 'wpshadow_autoloaded_files';

	/**
	 * Initialize autoloader and load all classes.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function init(): void {
		// Load essential classes first (in order)
		self::load_critical_classes();

		// Load feature classes
		self::load_features();

		// Fire autoloading complete hook
		do_action( 'wpshadow_autoloader_complete' );
	}

	/**
	 * Load critical classes in dependency order.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	private static function load_critical_classes(): void {
		foreach ( self::$critical_classes as $file ) {
			$path = WPSHADOW_PATH . $file;
			
			if ( file_exists( $path ) ) {
				require_once $path;
			} else {
				// Log missing file but don't stop execution (degraded mode)
				// Programming wisdom: 404 - File Not Found. Unlike my sense of humor, which is always included.
				if ( function_exists( 'error_log' ) ) {
					error_log( sprintf( 'WPShadow: Essential file missing: %s', $file ) );
				}
			}
		}
	}

	/**
	 * Load feature classes from directories.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	private static function load_features(): void {
		// Get cached file list or discover them
		$files = wp_cache_get( self::CACHE_KEY, 'wpshadow' );

		if ( false === $files ) {
			$files = self::discover_feature_files();
			wp_cache_set( self::CACHE_KEY, $files, 'wpshadow', HOUR_IN_SECONDS );
		}

		// Load all discovered files
		foreach ( $files as $file ) {
			if ( file_exists( $file ) ) {
				require_once $file;
			}
		}
	}

	/**
	 * Discover all PHP files in feature directories.
	 *
	 * @since 1.6093.1200
	 * @return array Array of file paths.
	 */
	private static function discover_feature_files(): array {
		$files = array();

		foreach ( self::$feature_directories as $directory ) {
			$dir_path = WPSHADOW_PATH . $directory;

			if ( ! is_dir( $dir_path ) ) {
				continue;
			}

			$discovered = self::scan_directory_recursive( $dir_path );
			$files      = array_merge( $files, $discovered );
		}

		return $files;
	}

	/**
	 * Recursively scan directory for PHP files.
	 *
	 * @since 1.6093.1200
	 * @param  string $directory Directory to scan.
	 * @return array Array of file paths.
	 */
	private static function scan_directory_recursive( string $directory ): array {
		$files = array();

		if ( ! is_dir( $directory ) || ! is_readable( $directory ) ) {
			return $files;
		}

		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator( $directory, \RecursiveDirectoryIterator::SKIP_DOTS ),
			\RecursiveIteratorIterator::SELF_FIRST
		);

		foreach ( $iterator as $file ) {
			if ( $file->isFile() && 'php' === $file->getExtension() ) {
				$file_path = $file->getPathname();

				if ( false !== strpos( $file_path, '/includes/ui/reports/' ) || false !== strpos( $file_path, '/includes/features/onboarding/data/' ) ) {
					continue;
				}

				// Skip test files
				if ( false !== strpos( $file_path, '/tests/' ) ) {
					continue;
				}

				// Skip vendor files
				if ( false !== strpos( $file_path, '/vendor/' ) ) {
					continue;
				}

				$files[] = $file_path;
			}
		}

		return $files;
	}

	/**
	 * Clear autoloader cache.
	 *
	 * Useful during development when new files are added.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function clear_cache(): void {
		wp_cache_delete( self::CACHE_KEY, 'wpshadow' );
	}

	/**
	 * Get list of loaded files (for debugging).
	 *
	 * @since 1.6093.1200
	 * @return array Array of loaded file paths.
	 */
	public static function get_loaded_files(): array {
		$critical = array_map(
			function ( $file ) {
				return WPSHADOW_PATH . $file;
			},
			self::$critical_classes
		);

		$features = self::discover_feature_files();

		return array_merge( $critical, $features );
	}
}
