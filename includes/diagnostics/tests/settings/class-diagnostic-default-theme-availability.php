<?php
/**
 * Default Theme Availability Diagnostic
 *
 * Validates that a valid fallback theme is always available in case
 * the active theme becomes corrupted or deleted.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6032.1340
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default Theme Availability Diagnostic Class
 *
 * Checks fallback theme availability.
 *
 * @since 1.6032.1340
 */
class Diagnostic_Default_Theme_Availability extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'default-theme-availability';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Default Theme Availability';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates fallback theme availability';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6032.1340
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Get currently active theme.
		$active_theme = wp_get_theme();
		$active_slug  = $active_theme->get_stylesheet();

		// Check if active theme exists.
		if ( ! $active_theme->exists() ) {
			$issues[] = sprintf(
				/* translators: %s: theme slug */
				__( 'Active theme "%s" does not exist (corrupted or deleted)', 'wpshadow' ),
				$active_slug
			);
		}

		// Get list of available themes.
		$available_themes = wp_get_themes();

		if ( empty( $available_themes ) ) {
			$issues[] = __( 'No themes available (critical - WordPress will malfunction)', 'wpshadow' );
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'No themes available on this installation!', 'wpshadow' ),
				'severity'     => 'critical',
				'threat_level' => 100,
				'auto_fixable' => false,
				'details'      => array(
					'recommendation' => __( 'Install at least one WordPress theme immediately.', 'wpshadow' ),
				),
			);
		}

		// Check for fallback/default theme.
		// WordPress includes a default theme (Twenty* series).
		$has_wordpress_default = false;
		$default_themes        = array();

		foreach ( $available_themes as $theme_slug => $theme ) {
			// Check for WordPress default themes.
			if ( preg_match( '/^twenty/', $theme_slug ) ) {
				$has_wordpress_default = true;
				$default_themes[]      = $theme->get( 'Name' );
			}
		}

		if ( ! $has_wordpress_default ) {
			$issues[] = __( 'No WordPress default theme (Twenty*) available as fallback', 'wpshadow' );
		}

		// Check if there are at least 2 themes available.
		if ( count( $available_themes ) < 2 ) {
			$issues[] = __( 'Only one theme available (no fallback if active theme fails)', 'wpshadow' );
		}

		// Check active theme health.
		$active_dir = get_template_directory();

		// Check if essential files exist.
		$required_files = array(
			'functions.php',
			'index.php',
			'style.css',
		);

		$missing_files = array();
		foreach ( $required_files as $file ) {
			if ( ! file_exists( $active_dir . '/' . $file ) ) {
				$missing_files[] = $file;
			}
		}

		if ( ! empty( $missing_files ) ) {
			$issues[] = sprintf(
				/* translators: %s: comma-separated file names */
				__( 'Active theme missing essential files: %s', 'wpshadow' ),
				implode( ', ', $missing_files )
			);
		}

		// Check for theme errors (parse errors, etc).
		if ( file_exists( $active_dir . '/functions.php' ) ) {
			$functions_file = $active_dir . '/functions.php';
			$content        = file_get_contents( $functions_file );

			// Check for common PHP errors (very basic check).
			if ( preg_match( '/^\s*<\?php/', $content ) === 0 ) {
				$issues[] = __( 'Active theme functions.php missing PHP opening tag', 'wpshadow' );
			}

			// Check for syntax - attempt to parse.
			$error_level = error_reporting( 0 );
			$output      = @ shell_exec( 'php -l ' . escapeshellarg( $functions_file ) . ' 2>&1' );
			error_reporting( $error_level );

			if ( $output && false !== strpos( $output, 'Parse error' ) ) {
				$issues[] = __( 'Active theme functions.php has PHP parse errors', 'wpshadow' );
			}
		}

		// Check theme.json if it exists.
		$theme_json = $active_dir . '/theme.json';
		if ( file_exists( $theme_json ) ) {
			$json_content = file_get_contents( $theme_json );
			$decoded      = json_decode( $json_content, true );

			if ( null === $decoded ) {
				$issues[] = __( 'Active theme theme.json has invalid JSON', 'wpshadow' );
			}
		}

		// Check if there's a fallback theme configured.
		$fallback_theme = get_option( 'fallback_theme' );
		if ( ! $fallback_theme ) {
			// Not specifically configured, but WordPress has built-in fallback to default theme.
		}

		// Check multisite fallback theme setting.
		if ( is_multisite() ) {
			$network_fallback = get_site_option( 'allowed_themes' );
			if ( empty( $network_fallback ) ) {
				// No restriction - all themes available.
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of theme availability issues */
					__( 'Found %d default theme availability issues.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'high',
				'threat_level' => 60,
				'auto_fixable' => false,
				'details'      => array(
					'issues'          => $issues,
					'active_theme'    => $active_slug,
					'available_count' => count( $available_themes ),
					'default_themes'  => $default_themes,
					'recommendation'  => __( 'Ensure at least one WordPress default theme is installed and active themes are valid.', 'wpshadow' ),
				),
			);
		}

		return null;
	}
}
