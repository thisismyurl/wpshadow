<?php
/**
 * CPT Registry
 *
 * Auto-discovers and registers all CPT_Base classes.
 *
 * @package    WPShadow
 * @subpackage Core
 * @since      1.6035.1200
 */

declare(strict_types=1);

namespace WPShadow\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CPT_Registry Class
 *
 * Automatically discovers all CPT_Base classes and subscribes them
 * to WordPress hooks. No manual registration needed.
 *
 * DISCOVERY PATTERN:
 * - Scans includes/content/post-types/
 * - Finds all classes extending CPT_Base
 * - Auto-subscribes them to hooks
 * - Caches discovery for performance
 *
 * @since 1.6035.1200
 */
class CPT_Registry {

	/**
	 * Cache key for discovered CPTs.
	 *
	 * @var string
	 */
	const CACHE_KEY = 'wpshadow_discovered_cpts';

	/**
	 * Cache group.
	 *
	 * @var string
	 */
	const CACHE_GROUP = 'wpshadow';

	/**
	 * Initialize the registry.
	 *
	 * @since  1.6035.1200
	 * @return void
	 */
	public static function init(): void {
		add_action( 'init', array( __CLASS__, 'discover_and_subscribe' ), 5 );
	}

	/**
	 * Discover and subscribe all CPT_Base classes.
	 *
	 * @since  1.6035.1200
	 * @return void
	 */
	public static function discover_and_subscribe(): void {
		$cpts = self::get_discovered_cpts();

		foreach ( $cpts as $cpt_class ) {
			if ( class_exists( $cpt_class ) && is_subclass_of( $cpt_class, CPT_Base::class ) ) {
				$cpt_class::subscribe();
			}
		}
	}

	/**
	 * Get discovered CPTs (with caching).
	 *
	 * @since  1.6035.1200
	 * @return array Array of CPT class names.
	 */
	private static function get_discovered_cpts(): array {
		// Check cache first.
		$cached = wp_cache_get( self::CACHE_KEY, self::CACHE_GROUP );
		if ( false !== $cached && is_array( $cached ) ) {
			return $cached;
		}

		// Discover CPTs.
		$cpts = self::discover_cpts();

		// Cache for 1 hour.
		wp_cache_set( self::CACHE_KEY, $cpts, self::CACHE_GROUP, HOUR_IN_SECONDS );

		return $cpts;
	}

	/**
	 * Discover all CPT_Base classes in the codebase.
	 *
	 * @since  1.6035.1200
	 * @return array Array of fully qualified class names.
	 */
	private static function discover_cpts(): array {
		$cpts = array();

		// Scan CPT directories.
		$directories = array(
			WPSHADOW_PATH . 'includes/content/post-types/',
		);

		foreach ( $directories as $directory ) {
			if ( ! is_dir( $directory ) ) {
				continue;
			}

			$files = glob( $directory . '*.php' );

			if ( false === $files ) {
				continue;
			}

			foreach ( $files as $file ) {
				$class_name = self::extract_class_name( $file );

				if ( ! empty( $class_name ) && class_exists( $class_name ) ) {
					if ( is_subclass_of( $class_name, CPT_Base::class ) ) {
						$cpts[] = $class_name;
					}
				}
			}
		}

		return $cpts;
	}

	/**
	 * Extract fully qualified class name from file.
	 *
	 * @since  1.6035.1200
	 * @param  string $file File path.
	 * @return string|null Fully qualified class name or null.
	 */
	private static function extract_class_name( string $file ): ?string {
		if ( ! file_exists( $file ) || ! is_readable( $file ) ) {
			return null;
		}

		// Read first 50 lines for namespace and class.
		$handle = fopen( $file, 'r' );
		if ( ! $handle ) {
			return null;
		}

		$namespace  = '';
		$class_name = '';
		$line_count = 0;
		$max_lines  = 50;

		while ( ! feof( $handle ) && $line_count < $max_lines ) {
			$line = fgets( $handle );
			++$line_count;

			// Extract namespace.
			if ( preg_match( '/^namespace\s+([^;]+);/i', $line, $matches ) ) {
				$namespace = trim( $matches[1] );
			}

			// Extract class name (skip abstract classes).
			if ( preg_match( '/^class\s+(\w+)/i', $line, $matches ) ) {
				// Check if it's abstract (should not auto-register base classes).
				if ( stripos( $line, 'abstract' ) === false ) {
					$class_name = trim( $matches[1] );
					break;
				}
			}
		}

		fclose( $handle );

		if ( empty( $namespace ) || empty( $class_name ) ) {
			return null;
		}

		return '\\' . $namespace . '\\' . $class_name;
	}

	/**
	 * Clear discovery cache.
	 *
	 * Useful when new CPT classes are added during development.
	 *
	 * @since  1.6035.1200
	 * @return void
	 */
	public static function clear_cache(): void {
		wp_cache_delete( self::CACHE_KEY, self::CACHE_GROUP );
	}

	/**
	 * Get all registered CPT slugs.
	 *
	 * @since  1.6035.1200
	 * @return array Array of post type slugs.
	 */
	public static function get_registered_slugs(): array {
		$cpts  = self::get_discovered_cpts();
		$slugs = array();

		foreach ( $cpts as $cpt_class ) {
			if ( class_exists( $cpt_class ) && is_subclass_of( $cpt_class, CPT_Base::class ) ) {
				$config = $cpt_class::get_post_type_config();
				if ( ! empty( $config['slug'] ) ) {
					$slugs[] = $config['slug'];
				}
			}
		}

		return $slugs;
	}
}
