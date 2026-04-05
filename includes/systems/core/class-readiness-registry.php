<?php
/**
 * Readiness Registry
 *
 * Centralized lifecycle/readiness classification for diagnostics and treatments.
 *
 * @package WPShadow
 * @since 0.7055.1200
 */

declare(strict_types=1);

namespace WPShadow\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registry for item readiness states.
 */
class Readiness_Registry {
	/**
	 * Production-ready state.
	 */
	public const STATE_PRODUCTION = 'production';

	/**
	 * Beta state (partially hardened / internally validated).
	 */
	public const STATE_BETA = 'beta';

	/**
	 * Planned state (placeholder / incomplete).
	 */
	public const STATE_PLANNED = 'planned';

	/**
	 * Get a full machine-readable readiness inventory.
	 *
	 * @return array<string, mixed>
	 */
	public static function get_inventory(): array {
		return array(
			'generated_at' => time(),
			'diagnostics'  => self::get_diagnostic_inventory(),
			'treatments'   => self::get_treatment_inventory(),
		);
	}

	/**
	 * Return the readiness states permitted for the current environment.
	 *
	 * Delegates to Environment_Detector so that the allowed state list
	 * automatically tightens in production and broadens in development.
	 *
	 * Callers may still override via the wpshadow_allowed_diagnostic_readiness_states
	 * filter — this method simply provides Environment_Detector's defaults as the
	 * starting point.
	 *
	 * @return array<int, string>
	 */
	public static function get_environment_allowed_states(): array {
		if ( class_exists( Environment_Detector::class ) ) {
			$policy = Environment_Detector::get_policy();
			if ( ! empty( $policy['readiness_states'] ) && is_array( $policy['readiness_states'] ) ) {
				return $policy['readiness_states'];
			}
		}

		// Safe fallback: production-only.
		return array( self::STATE_PRODUCTION );
	}

	/**
	 * Return a summary of the active environment and its governance policy.
	 *
	 * Merges environment detection with readiness state configuration so that
	 * AJAX handlers and admin pages have a single place to fetch all
	 * governance context.
	 *
	 * @return array<string, mixed>
	 */
	public static function get_governance_context(): array {
		$environment = class_exists( Environment_Detector::class )
			? Environment_Detector::get_environment()
			: 'production';

		$policy = class_exists( Environment_Detector::class )
			? Environment_Detector::get_policy()
			: array(
				'readiness_states' => array( self::STATE_PRODUCTION ),
				'confidence_min'   => 'high',
				'auto_fix'         => true,
				'include_beta'     => false,
				'include_planned'  => false,
				'schedule'         => 'weekly',
			);

		$core_slug_count = 0;
		if ( class_exists( Diagnostic_Metadata::class ) ) {
			$core_slug_count = count( Diagnostic_Metadata::get_core_slugs() );
		}

		$treatment_counts = array();
		if ( class_exists( Treatment_Metadata::class ) ) {
			$treatment_counts = Treatment_Metadata::get_counts();
		}

		return array(
			'environment'           => $environment,
			'policy'                => $policy,
			'core_diagnostic_count' => $core_slug_count,
			'readiness_states'      => self::get_environment_allowed_states(),
			'treatments'            => $treatment_counts,
			'generated_at'          => time(),
		);
	}

	/**
	 * Resolve diagnostic readiness state.
	 *
	 * @param string $class_name Diagnostic class name.
	 * @param string $file_path  Backing file path.
	 * @return string
	 */
	public static function get_diagnostic_state( string $class_name, string $file_path ): string {
		$path  = str_replace( '\\', '/', $file_path );
		$state = self::STATE_PRODUCTION;

		if ( false !== strpos( $path, '/diagnostics/todo/' ) ) {
			$state = self::STATE_PLANNED;
		} elseif ( false !== strpos( $path, '/diagnostics/help/' ) ) {
			$state = self::STATE_BETA;
		}

		/**
		 * Filter diagnostic readiness state.
		 *
		 * @since 0.7055.1200
		 * @param string $state      Computed state.
		 * @param string $class_name Diagnostic class name.
		 * @param string $file_path  Source file path.
		 */
		$filtered = apply_filters( 'wpshadow_diagnostic_readiness_state', $state, $class_name, $file_path );
		return self::normalize_state( is_string( $filtered ) ? $filtered : $state );
	}

	/**
	 * Resolve treatment readiness state.
	 *
	 * @param string $class_name Treatment class name.
	 * @return string
	 */
	public static function get_treatment_state( string $class_name ): string {
		if ( ! class_exists( $class_name ) ) {
			return self::STATE_PLANNED;
		}

		$has_apply = self::has_concrete_method( $class_name, 'apply' );
		$has_undo  = self::has_concrete_method( $class_name, 'undo' );

		if ( $has_apply && $has_undo ) {
			$state = self::STATE_PRODUCTION;
		} elseif ( $has_apply || $has_undo ) {
			$state = self::STATE_BETA;
		} else {
			$state = self::STATE_PLANNED;
		}

		/**
		 * Filter treatment readiness state.
		 *
		 * @since 0.7055.1200
		 * @param string $state      Computed state.
		 * @param string $class_name Treatment class name.
		 */
		$filtered = apply_filters( 'wpshadow_treatment_readiness_state', $state, $class_name );
		return self::normalize_state( is_string( $filtered ) ? $filtered : $state );
	}

	/**
	 * Build inventory entries for diagnostics.
	 *
	 * @return array<int, array<string, string>>
	 */
	private static function get_diagnostic_inventory(): array {
		if ( ! class_exists( '\\WPShadow\\Diagnostics\\Diagnostic_Registry' ) ) {
			return array();
		}

		$file_map = \WPShadow\Diagnostics\Diagnostic_Registry::get_diagnostic_file_map();
		$rows     = array();

		foreach ( $file_map as $entry_class => $entry ) {
			if ( ! is_string( $entry_class ) || '' === $entry_class ) {
				continue;
			}

			$class_name = 0 === strpos( $entry_class, 'WPShadow\\Diagnostics\\' )
				? $entry_class
				: 'WPShadow\\Diagnostics\\' . $entry_class;
			$file_path  = isset( $entry['file'] ) ? (string) $entry['file'] : '';
			$rows[]     = array(
				'class' => $class_name,
				'file'  => $file_path,
				'state' => self::get_diagnostic_state( $class_name, $file_path ),
			);
		}

		return $rows;
	}

	/**
	 * Build inventory entries for treatments.
	 *
	 * @return array<int, array<string, string>>
	 */
	private static function get_treatment_inventory(): array {
		$rows = array();
		$dirs = array(
			WPSHADOW_PATH . 'includes/treatments',
			WPSHADOW_PATH . 'includes/systems/treatments',
		);

		foreach ( $dirs as $dir ) {
			if ( ! is_dir( $dir ) ) {
				continue;
			}

			$iterator = new \RecursiveIteratorIterator(
				new \RecursiveDirectoryIterator( $dir, \RecursiveDirectoryIterator::SKIP_DOTS )
			);

			foreach ( $iterator as $file ) {
				/** @var \SplFileInfo $file */
				if ( ! $file->isFile() ) {
					continue;
				}

				$filename = $file->getFilename();
				if ( 0 !== strpos( $filename, 'class-treatment-' ) || 'php' !== $file->getExtension() ) {
					continue;
				}

				$class_name = self::treatment_class_from_filename( $filename );
				$fqcn       = 'WPShadow\\Treatments\\' . $class_name;
				require_once $file->getPathname();

				$rows[] = array(
					'class' => $fqcn,
					'file'  => $file->getPathname(),
					'state' => self::get_treatment_state( $fqcn ),
				);
			}
		}

		return $rows;
	}

	/**
	 * Convert treatment filename to class name.
	 *
	 * @param string $filename Basename like class-treatment-foo-bar.php.
	 * @return string
	 */
	private static function treatment_class_from_filename( string $filename ): string {
		$slug = str_replace( array( 'class-treatment-', '.php' ), '', $filename );
		$slug = str_replace( '-', '_', $slug );
		$slug = str_replace( ' ', '_', ucwords( str_replace( '_', ' ', $slug ) ) );
		return 'Treatment_' . $slug;
	}

	/**
	 * Check whether a method is implemented directly on a class.
	 *
	 * @param string $class_name Class name.
	 * @param string $method     Method name.
	 * @return bool
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
	 * Normalize arbitrary readiness value to known states.
	 *
	 * @param string $state Candidate state.
	 * @return string
	 */
	private static function normalize_state( string $state ): string {
		$state = strtolower( trim( $state ) );
		if ( in_array( $state, array( self::STATE_PRODUCTION, self::STATE_BETA, self::STATE_PLANNED ), true ) ) {
			return $state;
		}

		return self::STATE_PLANNED;
	}
}
