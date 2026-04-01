<?php
/**
 * Active Theme Validity Diagnostic
 *
 * Validates that the active theme is properly installed, configured,
 * and not corrupted or missing critical files.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Active Theme Validity Diagnostic Class
 *
 * Checks active theme integrity and validity.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Active_Theme_Validity extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'active-theme-validity';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Active Theme Validity';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates active theme integrity and configuration';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		$theme = wp_get_theme();
		$theme_slug = $theme->get_stylesheet();

		if ( ! $theme->exists() ) {
			$issues[] = __( 'Active theme does not exist (theme is missing or corrupted)', 'wpshadow' );
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Active theme is missing or corrupted.', 'wpshadow' ),
				'severity'     => 'critical',
				'threat_level' => 100,
				'auto_fixable' => false,
				'details'      => array(
					'recommendation' => __( 'Restore the active theme files or switch to a fallback theme immediately.', 'wpshadow' ),
				),
			);
		}

		// Check for required files.
		$template_dir = get_template_directory();
		$required_files = array( 'style.css', 'functions.php', 'index.php' );
		$missing_files  = array();

		foreach ( $required_files as $file ) {
			if ( ! file_exists( $template_dir . '/' . $file ) ) {
				$missing_files[] = $file;
			}
		}

		if ( ! empty( $missing_files ) ) {
			$issues[] = sprintf(
				/* translators: %s: missing file list */
				__( 'Active theme missing required files: %s', 'wpshadow' ),
				implode( ', ', $missing_files )
			);
		}

		// Check for PHP syntax errors in functions.php.
		$functions_file = $template_dir . '/functions.php';
		if ( file_exists( $functions_file ) ) {
			$lint_output = @ shell_exec( 'php -l ' . escapeshellarg( $functions_file ) . ' 2>&1' );
			if ( $lint_output && false !== stripos( $lint_output, 'Parse error' ) ) {
				$issues[] = __( 'Active theme functions.php has PHP syntax errors', 'wpshadow' );
			}
		}

		// Check theme.json validity (if present).
		$theme_json = $template_dir . '/theme.json';
		if ( file_exists( $theme_json ) ) {
			$json_content = file_get_contents( $theme_json );
			$decoded      = json_decode( $json_content, true );

			if ( null === $decoded ) {
				$issues[] = __( 'Active theme theme.json contains invalid JSON', 'wpshadow' );
			}
		}

		// Check if theme has required metadata.
		if ( empty( $theme->get( 'Name' ) ) ) {
			$issues[] = __( 'Active theme missing Name metadata', 'wpshadow' );
		}

		if ( empty( $theme->get( 'Version' ) ) ) {
			$issues[] = __( 'Active theme missing Version metadata', 'wpshadow' );
		}

		// Check for theme update availability.
		$updates = get_transient( 'update_themes' );
		if ( ! empty( $updates ) && isset( $updates->response[ $theme_slug ] ) ) {
			$issues[] = __( 'Active theme has an update available (apply to maintain compatibility)', 'wpshadow' );
		}

		// Check if theme is a child theme and parent exists.
		$template = $theme->get_template();
		if ( $theme_slug !== $template ) {
			$parent_theme = wp_get_theme( $template );
			if ( ! $parent_theme->exists() ) {
				$issues[] = __( 'Active child theme has missing parent theme', 'wpshadow' );
			}
		}

		// Check for deprecated theme functions.
		$deprecated_patterns = array( 'mysql_', 'create_function', 'ereg(' );
		$php_files = glob( $template_dir . '/*.php' );
		foreach ( $php_files as $php_file ) {
			$content = file_get_contents( $php_file );
			foreach ( $deprecated_patterns as $pattern ) {
				if ( false !== stripos( $content, $pattern ) ) {
					$issues[] = sprintf(
						/* translators: %s: file name */
						__( 'Active theme uses deprecated PHP functions in: %s', 'wpshadow' ),
						basename( $php_file )
					);
					break 2;
				}
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of theme validity issues */
					__( 'Found %d active theme validity issues.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'high',
				'threat_level' => 60,
				'auto_fixable' => false,
				'details'      => array(
					'issues'         => $issues,
					'theme'          => $theme_slug,
					'missing_files'  => $missing_files,
					'recommendation' => __( 'Restore missing files, fix PHP errors, and keep the active theme updated.', 'wpshadow' ),
				),
			);
		}

		return null;
	}
}
