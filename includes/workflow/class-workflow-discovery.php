<?php
/**
 * Workflow Discovery - Auto-detect diagnostics and treatments
 *
 * Scans the diagnostics and treatments directories to dynamically
 * register triggers and actions without manual configuration.
 * Extensible for the Pro version to support multiple actions per trigger.
 *
 * @package WPShadow
 * @subpackage Workflow
 */

namespace WPShadow\Workflow;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Workflow Discovery class - discovers diagnostics and treatments
 */
class Workflow_Discovery {

	/**
	 * Cache for discovered items
	 *
	 * @var array
	 */
	private static $diagnostics_cache = null;

	/**
	 * Cache for discovered treatments
	 *
	 * @var array
	 */
	private static $treatments_cache = null;

	/**
	 * Discover all diagnostic classes and convert to triggers
	 *
	 * @return array Array of discovered diagnostic triggers
	 */
	public static function discover_diagnostics(): array {
		if ( null !== self::$diagnostics_cache ) {
			return self::$diagnostics_cache;
		}

		self::$diagnostics_cache = array();

		$diagnostics_dir = WP_PLUGIN_DIR . '/wpshadow/includes/diagnostics/';

		if ( ! is_dir( $diagnostics_dir ) ) {
			return self::$diagnostics_cache;
		}

		// Get files from root and subdirectories
		$files = glob( $diagnostics_dir . 'class-diagnostic-*.php' );
		if ( ! $files ) {
			$files = array();
		}
		// Also scan subdirectories
		foreach ( glob( $diagnostics_dir . '*/class-diagnostic-*.php' ) as $subfile ) {
			$files[] = $subfile;
		}

		foreach ( $files as $file ) {
			$trigger_data = self::extract_diagnostic_data( $file );

			if ( $trigger_data ) {
				$slug                             = $trigger_data['slug'];
				self::$diagnostics_cache[ $slug ] = $trigger_data;
			}
		}

		return self::$diagnostics_cache;
	}

	/**
	 * Discover all treatment classes and convert to actions
	 *
	 * @return array Array of discovered treatment actions
	 */
	public static function discover_treatments(): array {
		if ( null !== self::$treatments_cache ) {
			return self::$treatments_cache;
		}

		self::$treatments_cache = array();

		$treatments_dir = WP_PLUGIN_DIR . '/wpshadow/includes/treatments/';

		if ( ! is_dir( $treatments_dir ) ) {
			return self::$treatments_cache;
		}

		$files = glob( $treatments_dir . 'class-treatment-*.php' );

		if ( ! $files ) {
			return self::$treatments_cache;
		}

		foreach ( $files as $file ) {
			$action_data = self::extract_treatment_data( $file );

			if ( $action_data ) {
				$slug                            = $action_data['slug'];
				self::$treatments_cache[ $slug ] = $action_data;
			}
		}

		return self::$treatments_cache;
	}

	/**
	 * Extract trigger metadata from a diagnostic file
	 *
	 * @param string $file_path Path to diagnostic class file
	 * @return array|null Extracted trigger data or null if invalid
	 */
	private static function extract_diagnostic_data( $file_path ): ?array {
		$file_contents = file_get_contents( $file_path );

		if ( ! $file_contents ) {
			return null;
		}

		// Extract class name from file
		if ( ! preg_match( '/class\s+(Diagnostic_\w+)/', $file_contents, $class_matches ) ) {
			return null;
		}

		$class_name = $class_matches[1];

		// Extract static properties
		$slug        = self::extract_static_property( $file_contents, 'slug' );
		$title       = self::extract_static_property( $file_contents, 'title' );
		$description = self::extract_static_property( $file_contents, 'description' );

		if ( ! $slug ) {
			return null;
		}

		return array(
			'id'          => $slug,
			'slug'        => $slug,
			'label'       => $title ?: self::humanize_slug( $slug ),
			'description' => $description ?: 'Run ' . self::humanize_slug( $slug ) . ' diagnostic',
			'icon'        => 'search',
			'class'       => 'WPShadow\\Diagnostics\\' . $class_name,
			'type'        => 'diagnostic',
			'file'        => $file_path,
		);
	}

	/**
	 * Extract action metadata from a treatment file
	 *
	 * @param string $file_path Path to treatment class file
	 * @return array|null Extracted action data or null if invalid
	 */
	private static function extract_treatment_data( $file_path ): ?array {
		$file_contents = file_get_contents( $file_path );

		if ( ! $file_contents ) {
			return null;
		}

		// Extract class name from file
		if ( ! preg_match( '/class\s+(Treatment_\w+)/', $file_contents, $class_matches ) ) {
			return null;
		}

		$class_name = $class_matches[1];

		// Extract finding_id from get_finding_id method
		$finding_id = self::extract_method_return( $file_contents, 'get_finding_id' );

		if ( ! $finding_id ) {
			return null;
		}

		// Clean up quotes
		$finding_id = trim( $finding_id, '\'"' );

		return array(
			'id'          => $finding_id,
			'slug'        => $finding_id,
			'label'       => self::humanize_slug( $finding_id ),
			'description' => 'Apply ' . self::humanize_slug( $finding_id ) . ' fix',
			'icon'        => 'admin-tools',
			'class'       => 'WPShadow\\Treatments\\' . $class_name,
			'type'        => 'treatment',
			'file'        => $file_path,
		);
	}

	/**
	 * Extract static property value from class
	 *
	 * @param string $content PHP file contents
	 * @param string $property Property name
	 * @return string|null Property value or null if not found
	 */
	private static function extract_static_property( $content, $property ): ?string {
		$pattern = '/protected\s+static\s+\$' . preg_quote( $property, '/' ) . '\s*=\s*[\'"]([^\'"]+)[\'"]/';

		if ( preg_match( $pattern, $content, $matches ) ) {
			return $matches[1];
		}

		return null;
	}

	/**
	 * Extract return value from a method
	 *
	 * @param string $content PHP file contents
	 * @param string $method_name Method name
	 * @return string|null Return value or null if not found
	 */
	private static function extract_method_return( $content, $method_name ): ?string {
		$pattern = '/public\s+static\s+function\s+' . preg_quote( $method_name, '/' ) . '\s*\(\s*\)\s*:\s*string\s*\{[^}]*return\s+([\'"][^\'"]*[\'"])/';

		if ( preg_match( $pattern, $content, $matches ) ) {
			return $matches[1];
		}

		return null;
	}

	/**
	 * Convert slug to human-readable label
	 *
	 * @param string $slug Slug string
	 * @return string Human-readable label
	 */
	private static function humanize_slug( $slug ): string {
		$words = explode( '-', $slug );
		$words = array_map( 'ucfirst', $words );
		return implode( ' ', $words );
	}

	/**
	 * Clear the cache (useful when files are added dynamically)
	 */
	public static function clear_cache(): void {
		self::$diagnostics_cache = null;
		self::$treatments_cache  = null;
	}

	/**
	 * Get a specific diagnostic trigger by slug
	 *
	 * @param string $slug Diagnostic slug
	 * @return array|null Trigger data or null if not found
	 */
	public static function get_diagnostic( $slug ): ?array {
		$diagnostics = self::discover_diagnostics();
		return $diagnostics[ $slug ] ?? null;
	}

	/**
	 * Get a specific treatment action by slug
	 *
	 * @param string $slug Treatment slug
	 * @return array|null Action data or null if not found
	 */
	public static function get_treatment( $slug ): ?array {
		$treatments = self::discover_treatments();
		return $treatments[ $slug ] ?? null;
	}
}
