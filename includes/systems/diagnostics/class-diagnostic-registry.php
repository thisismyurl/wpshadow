<?php
/**
 * Diagnostic Registry
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Abstract_Registry;
use WPShadow\Core\Readiness_Registry;

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
	 * Stats for the most recent registry-driven scan.
	 *
	 * @var array<string, mixed>
	 */
	private static $last_run_stats = array(
		'requested' => array(),
		'executed'  => array(),
		'results'   => array(),
		'timestamp' => 0,
	);

	/**
	 * Flag to track if initialization has been completed
	 *
	 * @var bool
	 */
	private static $initialized = false;

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
		$file_map = self::filter_by_readiness( $file_map );
		return array_keys( $file_map );
	}

	/**
	 * Filter discovered diagnostics by readiness state.
	 *
	 * @param array<string, array{file: string, family: string}> $file_map Diagnostic file map.
	 * @return array<string, array{file: string, family: string}>
	 */
	private static function filter_by_readiness( array $file_map ): array {
		$allowed_states = self::get_allowed_readiness_states();
		$filtered       = array();

		foreach ( $file_map as $class_name => $entry ) {
			$file_path = isset( $entry['file'] ) ? (string) $entry['file'] : '';
			$qualified = self::normalize_class_name( (string) $class_name );
			$state     = Readiness_Registry::get_diagnostic_state( $qualified, $file_path );

			if ( in_array( $state, $allowed_states, true ) ) {
				$filtered[ $class_name ] = $entry;
			}
		}

		return $filtered;
	}

	/**
	 * Determine readiness states allowed for execution.
	 *
	 * The default set is now environment-aware: staging environments include
	 * beta diagnostics, development environments include planned items too.
	 * Individual filters can still override the computed value.
	 *
	 * @return array<int, string>
	 */
	private static function get_allowed_readiness_states(): array {
		// Start from the environment-aware defaults in Readiness_Registry.
		$env_states = Readiness_Registry::get_environment_allowed_states();
		$allowed    = ! empty( $env_states ) ? $env_states : array( Readiness_Registry::STATE_PRODUCTION );

		// Legacy opt-in filters remain functional for backward compatibility.
		if ( ! in_array( Readiness_Registry::STATE_BETA, $allowed, true )
			&& (bool) apply_filters( 'wpshadow_include_beta_diagnostics', false )
		) {
			$allowed[] = Readiness_Registry::STATE_BETA;
		}

		if ( ! in_array( Readiness_Registry::STATE_PLANNED, $allowed, true )
			&& (bool) apply_filters( 'wpshadow_include_planned_diagnostics', false )
		) {
			$allowed[] = Readiness_Registry::STATE_PLANNED;
		}

		/**
		 * Filter allowed readiness states for discovered diagnostics.
		 *
		 * @since 0.7055.1200
		 * @param array<int, string> $allowed Allowed readiness states.
		 */
		$allowed = apply_filters( 'wpshadow_allowed_diagnostic_readiness_states', $allowed );

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
	 * Get diagnostic file map (class name => file path + family)
	 *
	 * @since 0.6093.1200
	 * @return array<string, array{file: string, family: string}> Diagnostic file map.
	 */
	public static function get_diagnostic_file_map(): array {
		if ( null !== self::$diagnostic_file_map ) {
			return self::$diagnostic_file_map;
		}

		$current_version = defined( 'WPSHADOW_VERSION' ) ? WPSHADOW_VERSION : '';
		$cache_key       = 'wpshadow_diagnostic_file_map_v3';

		$cached_mem = wp_cache_get( $cache_key, 'wpshadow' );
		if ( is_array( $cached_mem ) && isset( $cached_mem['version'], $cached_mem['map'] ) && is_array( $cached_mem['map'] ) ) {
			if ( (string) $cached_mem['version'] === $current_version ) {
				$clean = self::filter_valid_file_map_entries( $cached_mem['map'] );
				if ( count( $clean ) === count( $cached_mem['map'] ) ) {
					self::$diagnostic_file_map = $clean;
					return self::$diagnostic_file_map;
				}
				// Stale entries found — fall through to rebuild.
				wp_cache_delete( $cache_key, 'wpshadow' );
			}
		}

		$cached = get_transient( $cache_key );
		if ( is_array( $cached ) && isset( $cached['version'], $cached['map'] ) && is_array( $cached['map'] ) ) {
			if ( (string) $cached['version'] === $current_version ) {
				$clean = self::filter_valid_file_map_entries( $cached['map'] );
				if ( count( $clean ) === count( $cached['map'] ) ) {
					self::$diagnostic_file_map = $clean;
					wp_cache_set( $cache_key, $cached, 'wpshadow', DAY_IN_SECONDS );
					return self::$diagnostic_file_map;
				}
				// Stale entries found — delete transient so it rebuilds.
				delete_transient( $cache_key );
				wp_cache_delete( $cache_key, 'wpshadow' );
			}
		}

		if ( is_array( $cached ) && ! empty( $cached ) ) {
			$legacy_dir = WPSHADOW_PATH . 'includes/diagnostics/tests';
			$has_legacy = false;
			if ( is_dir( $legacy_dir ) ) {
				foreach ( $cached as $item ) {
					if ( ! empty( $item['file'] ) && false !== strpos( $item['file'], '/includes/diagnostics/' ) ) {
						$has_legacy = true;
						break;
					}
				}
			}

			if ( ! is_dir( $legacy_dir ) || $has_legacy ) {
				self::$diagnostic_file_map = $cached;
				$legacy_payload            = array(
					'version' => $current_version,
					'map'     => self::$diagnostic_file_map,
				);
				wp_cache_set( $cache_key, $legacy_payload, 'wpshadow', DAY_IN_SECONDS );
				return self::$diagnostic_file_map;
			}
		}

		$map = self::build_diagnostic_file_map();
		$payload = array(
			'version' => $current_version,
			'map'     => $map,
		);
		set_transient( $cache_key, $payload, WEEK_IN_SECONDS );
		wp_cache_set( $cache_key, $payload, 'wpshadow', DAY_IN_SECONDS );

		self::$diagnostic_file_map = $map;
		return self::$diagnostic_file_map;
	}

	/**
	 * Normalize a diagnostic class name to the fully-qualified diagnostics namespace.
	 *
	 * @since 0.6093.1200
	 * @param  string $class_name Raw class name.
	 * @return string Fully-qualified diagnostic class name.
	 */
	public static function normalize_class_name( string $class_name ): string {
		$normalized = ltrim( trim( $class_name ), '\\' );
		if ( '' === $normalized ) {
			return '';
		}

		if ( 0 !== strpos( $normalized, __NAMESPACE__ . '\\' ) ) {
			$normalized = __NAMESPACE__ . '\\' . $normalized;
		}

		return $normalized;
	}

	/**
	 * Determine whether a diagnostic is currently enabled.
	 *
	 * @since 0.6093.1200
	 * @param  string            $class_name Diagnostic class name.
	 * @param  array<int, mixed>|null $disabled Optional disabled class list.
	 * @return bool True when the diagnostic is enabled.
	 */
	public static function is_diagnostic_enabled( string $class_name, ?array $disabled = null ): bool {
		$qualified = self::normalize_class_name( $class_name );
		if ( '' === $qualified ) {
			return false;
		}

		if ( ! is_array( $disabled ) ) {
			$disabled = get_option( 'wpshadow_disabled_diagnostic_classes', array() );
		}

		$disabled = is_array( $disabled ) ? array_values( array_unique( array_map( 'strval', $disabled ) ) ) : array();
		$short    = str_replace( __NAMESPACE__ . '\\', '', $qualified );
		$enabled  = ! in_array( $qualified, $disabled, true ) && ! in_array( $short, $disabled, true );

		/**
		 * Filters whether a diagnostic is enabled.
		 *
		 * @since 0.6093.1200
		 *
		 * @param bool   $enabled    Whether the diagnostic is enabled.
		 * @param string $class_name Fully-qualified diagnostic class name.
		 */
		return (bool) apply_filters( 'wpshadow_diagnostic_enabled', $enabled, $qualified );
	}

	/**
	 * Get the canonical diagnostics list used by the dashboard and Settings > Diagnostics.
	 *
	 * @since 0.6093.1200
	 * @return array<int, array<string, mixed>> Display-ready diagnostic definitions.
	 */
	public static function get_diagnostic_definitions(): array {
		$file_map = self::get_diagnostic_file_map();
		if ( empty( $file_map ) || ! is_array( $file_map ) ) {
			return array();
		}

		$freq_overrides = get_option( 'wpshadow_diagnostic_frequency_overrides', array() );
		$freq_overrides = is_array( $freq_overrides ) ? $freq_overrides : array();
		$disabled       = get_option( 'wpshadow_disabled_diagnostic_classes', array() );
		$disabled       = is_array( $disabled ) ? $disabled : array();
		$definitions    = array();

		foreach ( $file_map as $entry_class => $diagnostic_data ) {
			if ( ! is_string( $entry_class ) || '' === $entry_class ) {
				continue;
			}

			$class       = self::normalize_class_name( $entry_class );
			$short_class = str_replace( __NAMESPACE__ . '\\', '', $class );
			$file        = isset( $diagnostic_data['file'] ) ? (string) $diagnostic_data['file'] : '';
			if ( ! class_exists( $class ) && '' !== $file && file_exists( $file ) ) {
				require_once $file;
			}

			$class_loaded = class_exists( $class );
			$family_raw   = isset( $diagnostic_data['family'] ) ? (string) $diagnostic_data['family'] : '';
			$family       = $class_loaded && method_exists( $class, 'get_family' )
				? (string) $class::get_family()
				: $family_raw;
			$family_label = $class_loaded && method_exists( $class, 'get_family_label' )
				? (string) $class::get_family_label()
				: '';
			$title        = $class_loaded && method_exists( $class, 'get_title' )
				? (string) $class::get_title()
				: '';
			$description  = $class_loaded && method_exists( $class, 'get_description' )
				? (string) $class::get_description()
				: '';
			$severity     = $class_loaded && method_exists( $class, 'get_severity' )
				? (string) $class::get_severity()
				: 'medium';
			$default_freq = $class_loaded && method_exists( $class, 'get_scan_frequency' )
				? (string) $class::get_scan_frequency()
				: 'daily';
			$run_key      = $class_loaded && method_exists( $class, 'get_slug' )
				? sanitize_key( (string) $class::get_slug() )
				: sanitize_key( strtolower( str_replace( '_', '-', str_replace( 'Diagnostic_', '', $short_class ) ) ) );

			if ( '' === trim( $family_label ) ) {
				$family_label = '' !== $family
					? ucwords( str_replace( array( '-', '_' ), ' ', $family ) )
					: __( 'General', 'wpshadow' );
			}

			if ( '' === trim( $title ) ) {
				$title = ucwords( strtolower( str_replace( '_', ' ', str_replace( 'Diagnostic_', '', $short_class ) ) ) );
			}

			// Pull confidence and core-set membership from Diagnostic_Metadata,
			// with a fallback to the class's own get_confidence() method.
			$slug       = $run_key;
			$meta       = class_exists( \WPShadow\Core\Diagnostic_Metadata::class )
				? \WPShadow\Core\Diagnostic_Metadata::get( $slug )
				: array();
			$confidence = $meta['confidence'] ?? (
				$class_loaded && method_exists( $class, 'get_confidence' )
					? (string) $class::get_confidence()
					: 'standard'
			);
			$is_core    = (bool) ( $meta['is_core'] ?? (
				$class_loaded && method_exists( $class, 'is_core' )
					? $class::is_core()
					: false
			) );
			$auto_fix_safe = (bool) ( $meta['auto_fix_safe'] ?? false );

			$definitions[] = array(
				'class'         => $class,
				'short_class'   => $short_class,
				'file'          => $file,
				'readiness'     => Readiness_Registry::get_diagnostic_state( $class, $file ),
				'title'         => $title,
				'description'   => $description,
				'family'        => $family,
				'family_label'  => $family_label,
				'severity'      => $severity,
				'default_freq'  => $default_freq,
				'enabled'       => self::is_diagnostic_enabled( $class, $disabled ),
				'frequency'     => isset( $freq_overrides[ $class ] ) ? (string) $freq_overrides[ $class ] : 'default',
				'run_key'       => $run_key,
				'confidence'    => $confidence,
				'is_core'       => $is_core,
				'auto_fix_safe' => $auto_fix_safe,
			);
		}

		usort(
			$definitions,
			static function ( array $a, array $b ): int {
				$family_cmp = strcmp( (string) ( $a['family'] ?? '' ), (string) ( $b['family'] ?? '' ) );
				return 0 !== $family_cmp
					? $family_cmp
					: strcmp( (string) ( $a['title'] ?? '' ), (string) ( $b['title'] ?? '' ) );
			}
		);

		return $definitions;
	}

	/**
	 * Return only Core 50 diagnostic definitions.
	 *
	 * Core diagnostics are universally applicable, high-signal checks that are
	 * shown by default for all users.  They are defined in Diagnostic_Metadata.
	 *
	 * @since 0.7055.1200
	 * @return array<int, array<string, mixed>>
	 */
	public static function get_core_diagnostics(): array {
		return array_values(
			array_filter(
				self::get_diagnostic_definitions(),
				static fn( array $d ): bool => ! empty( $d['is_core'] )
			)
		);
	}

	/**
	 * Return diagnostic definitions filtered to a specific confidence tier.
	 *
	 * @since 0.7055.1200
	 * @param string $tier 'high' | 'standard' | 'low'
	 * @return array<int, array<string, mixed>>
	 */
	public static function get_by_confidence( string $tier ): array {
		$tier = strtolower( trim( $tier ) );
		return array_values(
			array_filter(
				self::get_diagnostic_definitions(),
				static fn( array $d ): bool => ( $d['confidence'] ?? 'standard' ) === $tier
			)
		);
	}

	/**
	 * Return diagnostic definitions meeting a minimum confidence level.
	 *
	 * Tier ordering: high > standard > low.
	 *
	 * @since 0.7055.1200
	 * @param string $minimum_tier Minimum tier: 'high', 'standard', or 'low'.
	 * @return array<int, array<string, mixed>>
	 */
	public static function get_by_minimum_confidence( string $minimum_tier ): array {
		$order = array( 'high' => 3, 'standard' => 2, 'low' => 1 );
		$min   = $order[ strtolower( trim( $minimum_tier ) ) ] ?? 1;

		return array_values(
			array_filter(
				self::get_diagnostic_definitions(),
				static function ( array $d ) use ( $order, $min ): bool {
					$tier  = strtolower( (string) ( $d['confidence'] ?? 'standard' ) );
					$score = $order[ $tier ] ?? 2;
					return $score >= $min;
				}
			)
		);
	}

	/**
	 * Return diagnostic definitions for the current environment's policy.
	 *
	 * Applies both readiness state filtering (via Readiness_Registry) and
	 * confidence filtering (via Environment_Detector policy).
	 *
	 * @since 0.7055.1200
	 * @return array<int, array<string, mixed>>
	 */
	public static function get_for_environment(): array {
		$min_confidence = 'low'; // default: include all

		if ( class_exists( \WPShadow\Core\Environment_Detector::class ) ) {
			$policy         = \WPShadow\Core\Environment_Detector::get_policy();
			$min_confidence = (string) ( $policy['confidence_min'] ?? 'low' );
		}

		// Readiness filtering is already applied inside get_diagnostic_definitions()
		// via discover_diagnostics() → filter_by_readiness(), and the allowed
		// states now include environment-aware defaults from Readiness_Registry.
		return self::get_by_minimum_confidence( $min_confidence );
	}

	/**
	 * Remove file-map entries whose backing file no longer exists on disk.
	 *
	 * Stale entries accumulate when diagnostic files are renamed or deleted
	 * between plugin updates while the version number stays the same, causing
	 * ghost diagnostics with auto-generated titles and empty descriptions to
	 * appear in the UI.
	 *
	 * @since 0.6093.1200
	 * @param  array<string, array{file: string, family: string}> $map Raw cached map.
	 * @return array<string, array{file: string, family: string}> Filtered map.
	 */
	private static function filter_valid_file_map_entries( array $map ): array {
		return array_filter(
			$map,
			static function ( $data ): bool {
				return isset( $data['file'] ) && '' !== $data['file'] && file_exists( $data['file'] );
			}
		);
	}

	/**
	 * Build diagnostic file map by scanning diagnostics directory
	 *
	 * @since 0.6093.1200
	 * @return array<string, array{file: string, family: string}> Diagnostic file map.
	 */
	private static function build_diagnostic_file_map(): array {
		$map              = array();
		$seen_titles      = array();
		$seen_intent_keys = array();
		$subdirs          = array( 'tests', 'help', 'todo', 'verified' );
		$base_dirs        = array(
			__DIR__,
			WPSHADOW_PATH . 'includes/diagnostics',
		);

		foreach ( $base_dirs as $base ) {
			if ( ! is_dir( $base ) ) {
				continue;
			}

			foreach ( $subdirs as $subdir ) {
				$dir = $base . '/' . $subdir;
				if ( ! is_dir( $dir ) ) {
					continue;
				}

				$iterator  = new \RecursiveIteratorIterator(
					new \RecursiveDirectoryIterator( $dir, \FilesystemIterator::SKIP_DOTS )
				);
				$file_list = iterator_to_array( $iterator, false );
				usort(
					$file_list,
					static function ( \SplFileInfo $left, \SplFileInfo $right ): int {
						return strcmp( $left->getPathname(), $right->getPathname() );
					}
				);

				foreach ( $file_list as $file_info ) {
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

					$title      = self::get_title_from_file( $file_info->getPathname() );
					$title_key  = self::normalize_title_key( $title );
					$intent_key = self::normalize_intent_key( $title );

					if ( '' !== $title_key && isset( $seen_titles[ $title_key ] ) ) {
						continue;
					}

					if ( '' !== $intent_key && isset( $seen_intent_keys[ $intent_key ] ) ) {
						continue;
					}

					if ( isset( $map[ $class_name ] ) ) {
						continue;
					}

					$family             = self::get_family_from_path( $file_info->getPathname() );
					$map[ $class_name ] = array(
						'file'   => $file_info->getPathname(),
						'family' => $family,
					);

					if ( '' !== $title_key ) {
						$seen_titles[ $title_key ] = true;
					}

					if ( '' !== $intent_key ) {
						$seen_intent_keys[ $intent_key ] = true;
					}
				}
			}
		}

		/**
		 * Filters the diagnostic file map.
		 *
		 * @since 0.6093.1200
		 *
		 * @param array<string, array{file: string, family: string}> $map Diagnostic file map.
		 */
		return apply_filters( 'wpshadow_diagnostic_file_map', $map );
	}

	/**
	 * Derive diagnostic family from file path.
	 *
	 * @since 0.6093.1200
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
	 * Extract diagnostic title from a class file.
	 *
	 * @since 0.6093.1200
	 * @param  string $file Diagnostic class file path.
	 * @return string Parsed title value or empty string when unavailable.
	 */
	private static function get_title_from_file( string $file ): string {
		$content = file_get_contents( $file );
		if ( false === $content || '' === $content ) {
			return '';
		}

		if ( preg_match( '/protected\\s+static\\s+\\$title\\s*=\\s*[\"\']([^\"\']+)[\"\']\\s*;/', $content, $matches ) ) {
			return trim( (string) $matches[1] );
		}

		return '';
	}

	/**
	 * Normalize title string for duplicate matching.
	 *
	 * @since 0.6093.1200
	 * @param  string $title Diagnostic title.
	 * @return string Normalized key.
	 */
	private static function normalize_title_key( string $title ): string {
		$title = trim( preg_replace( '/\\s+/', ' ', $title ) ?? '' );

		if ( '' === $title ) {
			return '';
		}

		return strtolower( $title );
	}

	/**
	 * Normalize title string for semantic duplicate matching.
	 *
	 * @since 0.6093.1200
	 * @param  string $title Diagnostic title.
	 * @return string Normalized semantic key.
	 */
	private static function normalize_intent_key( string $title ): string {
		$title = strtolower( trim( $title ) );
		if ( '' === $title ) {
			return '';
		}

		$title = preg_replace( '/\\ba\\s*\\/\\s*b\\b|\\ba\\s*-\\s*b\\b/', 'ab', $title ) ?? $title;
		$title = preg_replace( '/[^a-z0-9\\s]/', ' ', $title ) ?? $title;
		$title = preg_replace( '/\\s+/', ' ', trim( $title ) ) ?? '';
		if ( '' === $title ) {
			return '';
		}

		$drop_words = array(
			'active',
			'in',
			'use',
			'status',
			'framework',
			'program',
			'configured',
			'configuration',
			'not',
			'check',
			'validation',
			'verification',
			'detection',
		);

		$tokens   = explode( ' ', $title );
		$filtered = array();
		foreach ( $tokens as $token ) {
			if ( '' === $token ) {
				continue;
			}

			if ( in_array( $token, $drop_words, true ) ) {
				continue;
			}

			$filtered[] = $token;
		}

		if ( empty( $filtered ) ) {
			return '';
		}

		return implode( ' ', $filtered );
	}

	/**
	 * Initialize and load all diagnostic classes
	 *
	 * Called during plugins_loaded. Loads all discovered diagnostic files.
	 *
	 * @since 0.6093.1200
	 * @return void
	 */
	public static function init(): void {
		// Don't load all diagnostics at once (causes memory exhaustion)
		// They will be loaded on-demand when needed

		// Only initialize once - subsequent calls are safe no-ops
		if ( self::$initialized ) {
			return;
		}
		self::$initialized = true;

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
			// Wrap in error handler to catch parse errors
			set_error_handler(
				function ( $errno, $errstr ) {
					// Suppress the error - we'll check if class loaded instead
					return true;
				}
			);

			try {
				require_once $file;
			} catch ( \Throwable $e ) {
				// Class file has syntax error or fatal issue
				error_log( 'Failed to load diagnostic file ' . $file . ': ' . $e->getMessage() );
			} finally {
				restore_error_handler();
			}

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
		$enabled_classes = array();

		foreach ( self::get_diagnostic_definitions() as $definition ) {
			if ( ! is_array( $definition ) || empty( $definition['enabled'] ) || empty( $definition['class'] ) ) {
				continue;
			}

			$enabled_classes[] = (string) $definition['class'];
		}

		return self::run_checks( $enabled_classes );
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
	 * Get execution stats for the most recent registry-driven scan.
	 *
	 * @return array<string, mixed> Scan stats.
	 */
	public static function get_last_run_stats(): array {
		return self::$last_run_stats;
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
		$findings        = array();
		$requested       = array();
		$executed        = array();
		$results         = array();
		$stored_findings = get_option( 'wpshadow_site_findings', array() );
		if ( ! is_array( $stored_findings ) ) {
			$stored_findings = array();
		}

		// Read disabled diagnostics from settings (fully-qualified class names)
		$disabled = get_option( 'wpshadow_disabled_diagnostic_classes', array() );
		if ( ! is_array( $disabled ) ) {
			$disabled = array();
		}

		foreach ( $diagnostic_classes as $class_name ) {
			if ( ! is_string( $class_name ) || '' === $class_name ) {
				continue;
			}

			if ( 0 !== strpos( $class_name, __NAMESPACE__ . '\\' ) ) {
				$class_name = __NAMESPACE__ . '\\' . ltrim( $class_name, '\\' );
			}

			if ( ! class_exists( $class_name ) ) {
				$loaded = self::load_diagnostic_class( str_replace( __NAMESPACE__ . '\\', '', $class_name ) );
				if ( ! $loaded ) {
					continue;
				}
			}

			if ( ! self::is_diagnostic_enabled( $class_name, $disabled ) ) {
				continue;
			}

			$requested[] = $class_name;

			$cached_state = function_exists( 'wpshadow_get_valid_diagnostic_test_state' )
				? \wpshadow_get_valid_diagnostic_test_state( $class_name )
				: null;

			if ( is_array( $cached_state ) ) {
				$cached_status = (string) ( $cached_state['status'] ?? 'unknown' );
				if ( 'failed' === $cached_status ) {
					$cached_finding_id = (string) ( $cached_state['finding_id'] ?? '' );
					if ( '' !== $cached_finding_id && isset( $stored_findings[ $cached_finding_id ] ) && is_array( $stored_findings[ $cached_finding_id ] ) ) {
						$findings[] = $stored_findings[ $cached_finding_id ];
					}
				}

				$results[ $class_name ] = array(
					'status'     => $cached_status,
					'category'   => (string) ( $cached_state['category'] ?? '' ),
					'finding_id' => (string) ( $cached_state['finding_id'] ?? '' ),
					'source'     => 'cache',
				);
				continue;
			}

			// Execute through Diagnostic_Base wrapper so hooks/logging stay consistent.
			if ( method_exists( $class_name, 'execute' ) ) {
				try {
					// Set error handler to catch warnings/notices during check
					set_error_handler(
						function ( $errno, $errstr, $errfile, $errline ) {
							// Log but don't stop execution
							if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
									error_log( "Diagnostic warning in check(): [$errno] $errstr at $errfile:$errline" );
							}
							return true;
						}
					);

					// force = false: automated scan — respect per-class frequency schedule.
					$result                 = call_user_func( array( $class_name, 'execute' ), false );
					$executed[]             = $class_name;
					$results[ $class_name ] = array(
						'status'     => null === $result ? 'passed' : 'failed',
						'category'   => is_array( $result ) ? (string) ( $result['category'] ?? '' ) : '',
						'finding_id' => is_array( $result ) ? (string) ( $result['id'] ?? '' ) : '',
						'source'     => 'fresh',
					);

					if ( null !== $result ) {
						$findings[] = $result;
					}
				} catch ( \Throwable $e ) {
					// Catch ALL errors including Errors, not just Exceptions
					// Log error but continue processing
					error_log( 'Diagnostic error in ' . $class_name . ': ' . $e->getMessage() );
				} finally {
					restore_error_handler();
				}
			}
		}

		self::$last_run_stats = array(
			'requested' => array_values( array_unique( $requested ) ),
			'executed'  => array_values( array_unique( $executed ) ),
			'results'   => $results,
			'timestamp' => time(),
		);

		return $findings;
	}

	/**
	 * Clear diagnostic cache
	 *
	 * Call this if diagnostics are added/removed dynamically.
	 * Also clears the file map transient.
	 *
	 * @since 0.6093.1200
	 * @return void
	 */
	public static function clear_cache(): void {
		self::$diagnostics_cache   = null;
		self::$diagnostic_file_map = null;
		delete_transient( 'wpshadow_diagnostic_file_map_v3' );
		wp_cache_delete( 'wpshadow_diagnostic_file_map_v3', 'wpshadow' );
		delete_transient( 'wpshadow_diagnostic_file_map' );
		wp_cache_delete( 'wpshadow_diagnostic_file_map', 'wpshadow' );
	}

	/**
	 * Initialize hooks for cache clearing
	 *
	 * Automatically clear cache when plugins/themes are updated.
	 *
	 * @since 0.6093.1200
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
	 * @since 0.6093.1200
	 * @param \WP_Upgrader $upgrader WP_Upgrader instance.
	 * @param array        $options  Update options.
	 * @return void
	 */
	public static function handle_plugin_update( $upgrader, $options ): void {
		if ( isset( $options['action'], $options['type'] ) && 'update' === $options['action'] && 'plugin' === $options['type'] ) {
			self::clear_cache();
		}
	}
}
