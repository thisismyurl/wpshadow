<?php
/**
 * Treatment Registry
 *
 * Discovers treatment classes, maps them to finding IDs, and provides
 * centralized execution helpers for apply/rollback flows.
 *
 * @package WPShadow
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registry for treatment classes.
 */
class Treatment_Registry {

	/**
	 * Cached map of class => file path.
	 *
	 * @var array<string, string>|null
	 */
	private static $class_file_map = null;

	/**
	 * Cached map of finding_id => class.
	 *
	 * @var array<string, string>|null
	 */
	private static $finding_class_map = null;

	/**
	 * Flag to track if initialization has been completed
	 *
	 * @var bool
	 */
	private static $initialized = false;

	/**
	 * Get all discovered treatment classes.
	 *
	 * @return array<int, string> Fully-qualified treatment classes.
	 */
	public static function get_all(): array {
		return array_values( self::build_finding_class_map() );
	}

	/**
	 * Get treatment class by finding ID.
	 *
	 * @param string $finding_id Finding identifier.
	 * @return string|null Fully-qualified treatment class name.
	 */
	public static function get_treatment( string $finding_id ): ?string {
		$finding_id = sanitize_key( $finding_id );
		$map        = self::build_finding_class_map();

		return $map[ $finding_id ] ?? null;
	}

	/**
	 * Get treatment class by identifier.
	 *
	 * Supports finding ID and class basename lookups.
	 *
	 * @param string $id Identifier.
	 * @return string|null Fully-qualified treatment class name.
	 */
	public static function get( string $id ): ?string {
		$id = sanitize_key( $id );

		$treatment = self::get_treatment( $id );
		if ( null !== $treatment ) {
			return $treatment;
		}

		foreach ( self::get_all() as $class_name ) {
			$basename = strtolower( str_replace( '_', '-', str_replace( 'Treatment_', '', self::short_class_name( $class_name ) ) ) );
			if ( $basename === $id ) {
				return $class_name;
			}
		}

		return null;
	}

	/**
	 * Apply treatment by finding ID.
	 *
	 * @param string $finding_id Finding identifier.
	 * @param bool   $dry_run    Optional. Dry-run mode.
	 * @return array Result array.
	 */
	public static function apply_treatment( string $finding_id, bool $dry_run = false ): array {
		$treatment = self::get_treatment( $finding_id );

		if ( null === $treatment ) {
			return array(
				'success' => false,
				'message' => __( 'No treatment is available for this finding.', 'wpshadow' ),
			);
		}

		if ( ! method_exists( $treatment, 'execute' ) ) {
			return array(
				'success' => false,
				'message' => __( 'Treatment cannot be executed in this environment.', 'wpshadow' ),
			);
		}

		return $treatment::execute( $dry_run );
	}

	/**
	 * Undo treatment by finding ID.
	 *
	 * @param string $finding_id Finding identifier.
	 * @return array Result array.
	 */
	public static function undo_treatment( string $finding_id ): array {
		if ( class_exists( '\\WPShadow\\Treatments\\Rollback_Manager' ) && method_exists( '\\WPShadow\\Treatments\\Rollback_Manager', 'undo_treatment' ) ) {
			return \WPShadow\Treatments\Rollback_Manager::undo_treatment( $finding_id, false );
		}

		return array(
			'success' => false,
			'message' => __( 'Rollback is not available right now.', 'wpshadow' ),
		);
	}

	/**
	 * Discover treatment classes and map them to finding IDs.
	 *
	 * @return array<string, string> finding_id => class
	 */
	private static function build_finding_class_map(): array {
		if ( null !== self::$finding_class_map ) {
			return self::$finding_class_map;
		}

		$map = array();

		foreach ( self::get_class_file_map() as $class_name => $file_path ) {
			if ( ! self::load_treatment_class( $class_name, $file_path ) ) {
				continue;
			}

			if ( ! is_subclass_of( $class_name, '\\WPShadow\\Core\\Treatment_Base' ) ) {
				continue;
			}

			if ( ! method_exists( $class_name, 'get_finding_id' ) ) {
				continue;
			}

			try {
				$finding_id = sanitize_key( (string) $class_name::get_finding_id() );
				if ( '' !== $finding_id && ! isset( $map[ $finding_id ] ) ) {
					$map[ $finding_id ] = $class_name;
				}
			} catch ( \Throwable $e ) {
				continue;
			}
		}

		self::$finding_class_map = $map;

		return self::$finding_class_map;
	}

	/**
	 * Build map of class name => file path.
	 *
	 * @return array<string, string>
	 */
	private static function get_class_file_map(): array {
		if ( null !== self::$class_file_map ) {
			return self::$class_file_map;
		}

		$directories = array(
			WPSHADOW_PATH . 'includes/treatments',
			WPSHADOW_PATH . 'includes/systems/treatments',
		);

		$map = array();

		foreach ( $directories as $directory ) {
			if ( ! is_dir( $directory ) ) {
				continue;
			}

			$iterator = new \RecursiveIteratorIterator(
				new \RecursiveDirectoryIterator( $directory, \RecursiveDirectoryIterator::SKIP_DOTS )
			);

			foreach ( $iterator as $file ) {
				if ( ! $file->isFile() ) {
					continue;
				}

				$filename = $file->getFilename();
				if ( 0 !== strpos( $filename, 'class-treatment-' ) || 'php' !== $file->getExtension() ) {
					continue;
				}

				$class_name = self::class_name_from_file( $filename );
				$fqcn       = __NAMESPACE__ . '\\' . $class_name;

				if ( ! isset( $map[ $fqcn ] ) ) {
					$map[ $fqcn ] = $file->getPathname();
				}
			}
		}

		self::$class_file_map = $map;

		return self::$class_file_map;
	}

	/**
	 * Load treatment class from mapped file.
	 *
	 * @param string $class_name Fully-qualified class name.
	 * @param string $file_path  File path.
	 * @return bool Whether class is available.
	 */
	private static function load_treatment_class( string $class_name, string $file_path ): bool {
		if ( class_exists( $class_name ) ) {
			return true;
		}

		if ( ! file_exists( $file_path ) ) {
			return false;
		}

		require_once $file_path;

		return class_exists( $class_name );
	}

	/**
	 * Convert class-treatment file name to class name.
	 *
	 * @param string $filename File name.
	 * @return string Class name.
	 */
	private static function class_name_from_file( string $filename ): string {
		$base = basename( $filename, '.php' );
		$base = str_replace( 'class-treatment-', '', $base );

		$parts = explode( '-', $base );
		$parts = array_map( 'ucfirst', $parts );

		return 'Treatment_' . implode( '_', $parts );
	}

	/**
	 * Get short class name from FQCN.
	 *
	 * @param string $class_name Fully-qualified class name.
	 * @return string
	 */
	private static function short_class_name( string $class_name ): string {
		$parts = explode( '\\', $class_name );
		return (string) end( $parts );
	}

	/**
	 * Initialize Treatment_Registry
	 *
	 * Safe to call multiple times - only initializes once.
	 * This method is called during normal bootstrap and also during
	 * heartbeat execution to ensure treatments are available.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function init(): void {
		// Only initialize once - subsequent calls are safe no-ops
		if ( self::$initialized ) {
			return;
		}
		self::$initialized = true;

		// Treatments are loaded on-demand via build_finding_class_map()
		// No additional setup needed at this time
	}
}
