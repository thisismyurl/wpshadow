<?php
/**
 * Treatment Registry
 *
 * Discovers treatment classes, maps them to finding IDs, and provides
 * centralized execution helpers for treatment execution flows.
 *
 * @package ThisIsMyURL\Shadow
 * @since 0.6095
 */

declare(strict_types=1);

namespace ThisIsMyURL\Shadow\Treatments;

use ThisIsMyURL\Shadow\Core\Readiness_Registry;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Discover and resolve executable treatments.
 *
 * Treatments are looked up by finding ID throughout the plugin, but the code
 * should not need to know which concrete class implements each fix. This
 * registry owns that mapping and applies readiness rules so only intentional,
 * executable treatments are exposed to the rest of the system.
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
	 * Return every treatment class currently exposed as executable.
	 *
	 * This is the treatment-side equivalent of the diagnostic registry inventory.
	 * The result reflects readiness filtering, so the returned classes are the
	 * ones the plugin considers safe to present in normal runtime flows.
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
				'message' => __( 'No treatment is available for this finding.', 'thisismyurl-shadow' ),
			);
		}

		if ( ! method_exists( $treatment, 'execute' ) ) {
			return array(
				'success' => false,
				'message' => __( 'Treatment cannot be executed in this environment.', 'thisismyurl-shadow' ),
			);
		}

		return $treatment::execute( $dry_run );
	}

	/**
	 * Build the lookup table that connects findings to treatments.
	 *
	 * The key output of treatment discovery is not just a list of classes. It is
	 * a stable map from diagnostic finding IDs to concrete treatment classes so
	 * UI actions and automation flows can resolve "fix this finding" into a class
	 * that actually knows how to do the work.
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

			if ( ! is_subclass_of( $class_name, '\\ThisIsMyURL\\Shadow\\Core\\Treatment_Base' ) ) {
				continue;
			}

			if ( ! method_exists( $class_name, 'get_finding_id' ) ) {
				continue;
			}

			if ( ! self::is_treatment_ready( $class_name ) ) {
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
	 * Determine whether a treatment class is production-ready.
	 *
	 * A treatment is considered ready when it provides concrete implementations
	 * for both apply() and undo(), or when a filter explicitly allows fallback
	 * behavior for a specific class.
	 *
	 * @param string $class_name Fully-qualified class name.
	 * @return bool True when treatment can be exposed in the registry.
	 */
	private static function is_treatment_ready( string $class_name ): bool {
		$allow_fallback = (bool) apply_filters( 'thisismyurl_shadow_allow_fallback_treatment', false, $class_name );
		if ( $allow_fallback ) {
			return true;
		}

		$state         = Readiness_Registry::get_treatment_state( $class_name );
		$allowed       = self::get_allowed_readiness_states();
		$default_ready = in_array( $state, $allowed, true );

		/**
		 * Filter whether a treatment should be exposed as executable.
		 *
		 * @since 0.7055
		 * @param bool               $default_ready Computed readiness result.
		 * @param string             $class_name    Treatment class name.
		 * @param string             $state         Computed readiness state.
		 * @param array<int, string> $allowed       Allowed readiness states.
		 */
		return (bool) apply_filters( 'thisismyurl_shadow_treatment_ready', $default_ready, $class_name, $state, $allowed );
	}

	/**
	 * Determine readiness states allowed for treatment execution.
	 *
	 * @return array<int, string>
	 */
	private static function get_allowed_readiness_states(): array {
		$allowed = array( Readiness_Registry::STATE_PRODUCTION );

		if ( (bool) apply_filters( 'thisismyurl_shadow_include_beta_treatments', false ) ) {
			$allowed[] = Readiness_Registry::STATE_BETA;
		}

		if ( (bool) apply_filters( 'thisismyurl_shadow_include_planned_treatments', false ) ) {
			$allowed[] = Readiness_Registry::STATE_PLANNED;
		}

		/**
		 * Filter allowed readiness states for executable treatments.
		 *
		 * @since 0.7055
		 * @param array<int, string> $allowed Allowed readiness states.
		 */
		$allowed = apply_filters( 'thisismyurl_shadow_allowed_treatment_readiness_states', $allowed );

		if ( ! is_array( $allowed ) || empty( $allowed ) ) {
			return array( Readiness_Registry::STATE_PRODUCTION );
		}

		$normalized = array();
		foreach ( $allowed as $state ) {
			if ( ! is_string( $state ) ) {
				continue;
			}

			$state = strtolower( trim( $state ) );
			if ( in_array( $state, array( Readiness_Registry::STATE_PRODUCTION, Readiness_Registry::STATE_BETA, Readiness_Registry::STATE_PLANNED ), true ) ) {
				$normalized[] = $state;
			}
		}

		if ( empty( $normalized ) ) {
			return array( Readiness_Registry::STATE_PRODUCTION );
		}

		return array_values( array_unique( $normalized ) );
	}

	/**
	 * Check whether a method is implemented directly on a class.
	 *
	 * @param string $class_name Fully-qualified class name.
	 * @param string $method     Method name.
	 * @return bool True when method is declared by the class itself.
	 */
	private static function has_concrete_method( string $class_name, string $method ): bool {
		if ( ! method_exists( $class_name, $method ) ) {
			return false;
		}

		try {
			$reflection = new \ReflectionMethod( $class_name, $method );
			return $reflection->getDeclaringClass()->getName() === $class_name;
		} catch ( \ReflectionException $exception ) {
			return false;
		}
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
			THISISMYURL_SHADOW_PATH . 'includes/treatments',
			THISISMYURL_SHADOW_PATH . 'includes/systems/treatments',
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
	 * Mark the registry ready for use.
	 *
	 * Unlike systems that need heavy startup work, this registry is mostly lazy.
	 * Initialization exists so repeated entry points can safely request the
	 * registry without worrying whether another part of the plugin touched it
	 * first.
	 *
	 * @since 0.6095
	 * @return void
	 */
	public static function init(): void {
		// Only initialize once - subsequent calls are safe no-ops.
		if ( self::$initialized ) {
			return;
		}
		self::$initialized = true;

		// Treatments are loaded on-demand via build_finding_class_map().
		// No additional setup needed at this time.
	}
}
