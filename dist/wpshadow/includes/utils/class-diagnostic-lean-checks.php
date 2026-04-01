<?php
/**
 * Lean Diagnostic Checks Utility.
 *
 * Provides lightweight baseline checks used by fast diagnostic paths.
 *
 * @package WPShadow\Core
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Core;

if ( ! defined( 'ABSPATH' ) ) {
	// Allow CLI tools to include this file without WordPress bootstrap.
	define( 'ABSPATH', __DIR__ );
}

class Diagnostic_Lean_Checks {
	/**
	 * Map a family slug to a plain-English category.
	 *
	 * @since 0.6093.1200
	 * @param  string $family Diagnostic family slug.
	 * @return string Canonical category slug.
	 */
	public static function family_to_category( string $family ): string {
		$map = array(
			'security'    => 'security',
			'performance' => 'performance',
			'seo'         => 'seo',
			'design'      => 'design',
			'monitor'     => 'monitoring',
			'code'        => 'code-quality',
			'config'      => 'configuration',
			'system'      => 'system',
		);
		return $map[ $family ] ?? 'general';
	}

	/**
	 * Very lean security baseline signal.
	 *
	 * @since 0.6093.1200
	 * @return bool True when a baseline security issue is detected.
	 */
	public static function security_basics_issue(): bool {
		// Flag if file editing in wp-admin is not disabled (common hardening step).
		if ( ! defined( 'DISALLOW_FILE_EDIT' ) ) {
			return true;
		}
		return (bool) ( defined( 'DISALLOW_FILE_EDIT' ) && DISALLOW_FILE_EDIT === false );
	}

	/**
	 * Very lean SEO baseline signal.
	 *
	 * @since 0.6093.1200
	 * @return bool True when a baseline SEO issue is detected.
	 */
	public static function seo_basics_issue(): bool {
		// Blog set to discourage search engines?
		if ( function_exists( 'get_option' ) ) {
			$public = get_option( 'blog_public' );
			return (string) $public === '0';
		}
		return false;
	}

	/**
	 * Very lean performance baseline signal.
	 *
	 * @since 0.6093.1200
	 * @return bool True when a baseline performance issue is detected.
	 */
	public static function performance_basics_issue(): bool {
		// Object cache in use? If not (and function exists), flag as opportunity.
		if ( function_exists( 'wp_using_ext_object_cache' ) ) {
			return wp_using_ext_object_cache() === false;
		}
		// Fallback: SCRIPT_DEBUG true suggests dev-mode assets.
		return defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG === true;
	}

	/**
	 * Very lean code-quality baseline signal.
	 *
	 * @since 0.6093.1200
	 * @return bool True when a baseline code-quality issue is detected.
	 */
	public static function code_basics_issue(): bool {
		// Displaying errors in production is risky.
		if ( defined( 'WP_DEBUG_DISPLAY' ) && WP_DEBUG_DISPLAY ) {
			return true;
		}
		return false;
	}

	/**
	 * Very lean configuration baseline signal.
	 *
	 * @since 0.6093.1200
	 * @return bool True when a baseline configuration issue is detected.
	 */
	public static function config_basics_issue(): bool {
		if ( function_exists( 'get_option' ) ) {
			$tz = (string) get_option( 'timezone_string' );
			return $tz === '';
		}
		return false;
	}

	/**
	 * Build a standard finding array using minimal inputs.
	 *
	 * @since 0.6093.1200
	 * @param  string $id Finding identifier.
	 * @param  string $title Finding title.
	 * @param  string $description Finding description.
	 * @param  string $family Finding family slug.
	 * @param  string $severity Finding severity.
	 * @param  int    $threat_level Threat level score.
	 * @param  string $kb_slug KB article slug.
	 * @return array Standardized finding payload.
	 */
	public static function build_finding(
		string $id,
		string $title,
		string $description,
		string $family,
		string $severity,
		int $threat_level,
		string $kb_slug
	): array {
		$category = self::family_to_category( $family );
		return array(
			'id'            => $id,
			'title'         => $title,
			'description'   => $description,
			'category'      => $category,
			'severity'      => $severity,
			'threat_level'  => $threat_level,
			'kb_link'       => UTM_Link_Manager::kb_link( rawurlencode( $kb_slug ), 'diagnostic' ),
			'training_link' => UTM_Link_Manager::academy_link( rawurlencode( $kb_slug ), 'diagnostic' ),
			'auto_fixable'  => false,
		);
	}
}
