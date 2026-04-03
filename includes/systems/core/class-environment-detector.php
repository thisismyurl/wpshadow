<?php
/**
 * Environment Detector
 *
 * Detects the current WordPress deployment environment and returns a
 * normalized identifier used by the readiness and governance systems to
 * apply context-appropriate diagnostic policies.
 *
 * Detection priority:
 *   1. WP_ENVIRONMENT_TYPE constant (WordPress 5.5+)  — most reliable
 *   2. WPSHADOW_ENVIRONMENT constant               — operator override
 *   3. Heuristic indicators (URL, constants)       — fallback
 *   4. 'production'                                — safe default
 *
 * @package WPShadow
 * @since   0.7055.1200
 */

declare(strict_types=1);

namespace WPShadow\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Stateless environment detection helper.
 *
 * All methods are static; no instantiation needed.
 */
class Environment_Detector {

	/**
	 * Known environment identifiers.
	 */
	public const ENV_PRODUCTION  = 'production';
	public const ENV_STAGING     = 'staging';
	public const ENV_DEVELOPMENT = 'development';
	public const ENV_LOCAL       = 'local';

	/**
	 * Per-request detected environment (null = not yet resolved).
	 *
	 * @var string|null
	 */
	private static ?string $detected = null;

	// -------------------------------------------------------------------------
	// Public API
	// -------------------------------------------------------------------------

	/**
	 * Return the current environment identifier.
	 *
	 * The result is cached for the lifetime of the request.  Call flush() to
	 * reset (useful in tests).
	 *
	 * @return string One of: 'production', 'staging', 'development', 'local'
	 */
	public static function get_environment(): string {
		if ( null !== self::$detected ) {
			return self::$detected;
		}

		self::$detected = self::resolve_environment();

		/**
		 * Filter the detected environment.
		 *
		 * Useful for overriding detection when automated heuristics cannot
		 * determine the correct value (e.g. containerised staging servers
		 * owned by an external host).
		 *
		 * @since 0.7055.1200
		 *
		 * @param string $environment Detected environment identifier.
		 */
		$filtered = apply_filters( 'wpshadow_environment', self::$detected );
		if ( is_string( $filtered ) ) {
			self::$detected = self::normalize_environment( $filtered );
		}

		return self::$detected;
	}

	/**
	 * Return true when the site is running in production.
	 *
	 * @return bool
	 */
	public static function is_production(): bool {
		return self::get_environment() === self::ENV_PRODUCTION;
	}

	/**
	 * Return true when the site is running in staging.
	 *
	 * @return bool
	 */
	public static function is_staging(): bool {
		return self::get_environment() === self::ENV_STAGING;
	}

	/**
	 * Return true when the site is running in development.
	 *
	 * @return bool
	 */
	public static function is_development(): bool {
		return self::get_environment() === self::ENV_DEVELOPMENT;
	}

	/**
	 * Return true when the site is running locally.
	 *
	 * @return bool
	 */
	public static function is_local(): bool {
		return self::get_environment() === self::ENV_LOCAL;
	}

	/**
	 * Return the scan policy for the current environment.
	 *
	 * Policy keys:
	 *   readiness_states  array   Allowed Readiness_Registry state values
	 *   confidence_min    string  Minimum confidence tier ('high'|'standard'|'low')
	 *   auto_fix          bool    Auto-fix offered for high-confidence items
	 *   include_beta      bool    Beta diagnostics visible in dashboard
	 *   include_planned   bool    Planned diagnostics visible in dashboard
	 *   schedule          string  Suggested scan cadence label
	 *
	 * @return array<string, mixed>
	 */
	public static function get_policy(): array {
		$policies = array(
			self::ENV_PRODUCTION  => array(
				'readiness_states' => array( Readiness_Registry::STATE_PRODUCTION ),
				'confidence_min'   => 'high',
				'auto_fix'         => true,
				'include_beta'     => false,
				'include_planned'  => false,
				'schedule'         => 'weekly',
			),
			self::ENV_STAGING     => array(
				'readiness_states' => array( Readiness_Registry::STATE_PRODUCTION, Readiness_Registry::STATE_BETA ),
				'confidence_min'   => 'standard',
				'auto_fix'         => false,
				'include_beta'     => true,
				'include_planned'  => false,
				'schedule'         => 'twice_weekly',
			),
			self::ENV_DEVELOPMENT => array(
				'readiness_states' => array( Readiness_Registry::STATE_PRODUCTION, Readiness_Registry::STATE_BETA, Readiness_Registry::STATE_PLANNED ),
				'confidence_min'   => 'low',
				'auto_fix'         => false,
				'include_beta'     => true,
				'include_planned'  => true,
				'schedule'         => 'daily',
			),
			self::ENV_LOCAL       => array(
				'readiness_states' => array( Readiness_Registry::STATE_PRODUCTION, Readiness_Registry::STATE_BETA, Readiness_Registry::STATE_PLANNED ),
				'confidence_min'   => 'low',
				'auto_fix'         => true,
				'include_beta'     => true,
				'include_planned'  => true,
				'schedule'         => 'on_demand',
			),
		);

		$env    = self::get_environment();
		$policy = $policies[ $env ] ?? $policies[ self::ENV_PRODUCTION ];

		/**
		 * Filter the scan policy for the active environment.
		 *
		 * @since 0.7055.1200
		 *
		 * @param array  $policy      Policy configuration array.
		 * @param string $environment Active environment identifier.
		 */
		$filtered = apply_filters( 'wpshadow_environment_policy', $policy, $env );

		return is_array( $filtered ) && ! empty( $filtered ) ? $filtered : $policy;
	}

	/**
	 * Invalidate the per-request environment cache.
	 *
	 * @return void
	 */
	public static function flush(): void {
		self::$detected = null;
	}

	// -------------------------------------------------------------------------
	// Internal helpers
	// -------------------------------------------------------------------------

	/**
	 * Resolve the environment from available signals, in priority order.
	 *
	 * @return string
	 */
	private static function resolve_environment(): string {

		// 1. Operator override constant (highest priority).
		if ( defined( 'WPSHADOW_ENVIRONMENT' ) ) {
			$override = self::normalize_environment( (string) WPSHADOW_ENVIRONMENT );
			if ( '' !== $override ) {
				return $override;
			}
		}

		// 2. WordPress 5.5+ native constant.
		if ( function_exists( 'wp_get_environment_type' ) ) {
			$wp_env = wp_get_environment_type();
			$mapped = self::normalize_environment( $wp_env );
			if ( '' !== $mapped ) {
				return $mapped;
			}
		}

		// 3. Heuristic detection — URL-based staging/local signals.
		$host = isset( $_SERVER['HTTP_HOST'] ) ? strtolower( (string) $_SERVER['HTTP_HOST'] ) : '';

		$local_hosts = array( 'localhost', '127.0.0.1', '::1', '.local', '.test', '.ddev.site', '.lndo.site' );
		foreach ( $local_hosts as $pattern ) {
			if ( '' !== $host && false !== strpos( $host, $pattern ) ) {
				return self::ENV_LOCAL;
			}
		}

		$staging_patterns = array( 'staging', 'stage', 'uat', 'preprod', 'pre-prod', 'review', '.wpenginedev.' );
		foreach ( $staging_patterns as $pattern ) {
			if ( '' !== $host && false !== strpos( $host, $pattern ) ) {
				return self::ENV_STAGING;
			}
		}

		// 4. WP_DEBUG on with display errors → almost certainly development.
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_DISPLAY' ) && WP_DEBUG_DISPLAY ) {
			return self::ENV_DEVELOPMENT;
		}

		// 5. Safe default.
		return self::ENV_PRODUCTION;
	}

	/**
	 * Normalise a raw environment string to one of the known constants.
	 *
	 * WordPress uses 'local' directly; we accept it as-is and also map common
	 * synonyms from other platforms.
	 *
	 * @param string $raw Raw environment value.
	 * @return string Normalised value, or '' when unrecognised.
	 */
	private static function normalize_environment( string $raw ): string {
		$raw = strtolower( trim( $raw ) );

		$map = array(
			// production
			'production'   => self::ENV_PRODUCTION,
			'prod'         => self::ENV_PRODUCTION,
			'live'         => self::ENV_PRODUCTION,
			// staging
			'staging'      => self::ENV_STAGING,
			'stage'        => self::ENV_STAGING,
			'uat'          => self::ENV_STAGING,
			'pre-prod'     => self::ENV_STAGING,
			'preprod'      => self::ENV_STAGING,
			'review'       => self::ENV_STAGING,
			// development
			'development'  => self::ENV_DEVELOPMENT,
			'develop'      => self::ENV_DEVELOPMENT,
			'dev'          => self::ENV_DEVELOPMENT,
			// local
			'local'        => self::ENV_LOCAL,
		);

		return $map[ $raw ] ?? '';
	}
}
