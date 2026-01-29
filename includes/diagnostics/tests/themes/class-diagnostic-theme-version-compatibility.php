<?php
/**
 * Theme Version Compatibility Diagnostic
 *
 * Verifies theme is compatible with the current WordPress version and
 * detects potential compatibility issues.
 *
 * @package    WPShadow\Diagnostics
 * @subpackage Tests
 * @since      1.2601.2204
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Version Compatibility Diagnostic Class
 *
 * Checks for:
 * - Theme tested up to WordPress version
 * - Requires at least WordPress version
 * - Deprecated function usage
 * - PHP version compatibility
 * - Missing required WordPress features
 *
 * @since 1.2601.2204
 */
class Diagnostic_Theme_Version_Compatibility extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-version-compatibility';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Version Compatibility';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies theme is compatible with WordPress version';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'themes';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2204
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wp_version;
		$issues = array();

		// Get active theme.
		$theme = wp_get_theme();

		// Check Requires at least.
		$requires_wp = $theme->get( 'RequiresWP' );
		if ( empty( $requires_wp ) ) {
			$issues[] = __( 'Theme does not specify minimum WordPress version (RequiresWP header missing)', 'wpshadow' );
		} elseif ( version_compare( $wp_version, $requires_wp, '<' ) ) {
			$issues[] = sprintf(
				__( 'Theme requires WordPress %s but running %s (may have compatibility issues)', 'wpshadow' ),
				$requires_wp,
				$wp_version
			);
		}

		// Check Tested up to.
		$tested_up_to = $theme->get( 'TestedUpTo' );
		if ( empty( $tested_up_to ) ) {
			$issues[] = __( 'Theme does not specify tested WordPress version (TestedUpTo header missing)', 'wpshadow' );
		} else {
			// Parse major version.
			$wp_major = floatval( $wp_version );
			$tested_major = floatval( $tested_up_to );

			if ( $tested_major < $wp_major ) {
				$issues[] = sprintf(
					__( 'Theme not tested with current WordPress version (tested: %s, current: %s)', 'wpshadow' ),
					$tested_up_to,
					$wp_version
				);
			}
		}

		// Check PHP version requirement.
		$requires_php = $theme->get( 'RequiresPHP' );
		$current_php = PHP_VERSION;
		if ( ! empty( $requires_php ) && version_compare( $current_php, $requires_php, '<' ) ) {
			$issues[] = sprintf(
				__( 'Theme requires PHP %s but running %s', 'wpshadow' ),
				$requires_php,
				$current_php
			);
		}

		// Check for deprecated function usage.
		$deprecated_functions = self::scan_for_deprecated_functions();
		if ( ! empty( $deprecated_functions ) ) {
			$issues[] = sprintf(
				__( 'Theme uses deprecated WordPress functions: %s', 'wpshadow' ),
				implode( ', ', $deprecated_functions )
			);
		}

		// Check theme last updated.
		$stylesheet_path = get_stylesheet_directory() . '/style.css';
		if ( file_exists( $stylesheet_path ) ) {
			$file_time = filemtime( $stylesheet_path );
			$months_old = ( time() - $file_time ) / ( 30 * 24 * 60 * 60 );

			if ( $months_old > 24 ) {
				$issues[] = sprintf(
					__( 'Theme not updated in %d months (may have compatibility issues)', 'wpshadow' ),
					round( $months_old )
				);
			}
		}

		if ( empty( $issues ) ) {
			return null;
		}

		$severity = 'low';
		$threat_level = 35;

		// Upgrade severity if version mismatch.
		if ( ! empty( $requires_wp ) && version_compare( $wp_version, $requires_wp, '<' ) ) {
			$severity = 'high';
			$threat_level = 75;
		} elseif ( ! empty( $tested_up_to ) ) {
			$wp_major = floatval( $wp_version );
			$tested_major = floatval( $tested_up_to );
			if ( ( $wp_major - $tested_major ) >= 2 ) {
				$severity = 'medium';
				$threat_level = 55;
			}
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => implode( "\n", $issues ),
			'severity'     => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/theme-version-compatibility',
		);
	}

	/**
	 * Scan theme files for deprecated functions.
	 *
	 * @since  1.2601.2204
	 * @return array List of deprecated functions found.
	 */
	private static function scan_for_deprecated_functions() {
		$deprecated = array();

		// Common deprecated functions.
		$deprecated_list = array(
			'get_settings',
			'get_option_deprecated',
			'wp_setcookie',
			'wp_get_cookie_login',
			'wp_login',
			'get_userdatabylogin',
			'get_user_by_email',
			'wp_specialchars',
			'attribute_escape',
			'clean_url',
			'sanitize_url',
		);

		$theme_dir = get_stylesheet_directory();
		$php_files = array();

		// Get theme PHP files (limit to 20).
		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator( $theme_dir, \RecursiveDirectoryIterator::SKIP_DOTS ),
			\RecursiveIteratorIterator::SELF_FIRST
		);

		$count = 0;
		foreach ( $iterator as $file ) {
			if ( $file->isFile() && $file->getExtension() === 'php' && $count < 20 ) {
				$php_files[] = $file->getPathname();
				$count++;
			}
		}

		// Scan files.
		foreach ( $php_files as $file ) {
			$content = file_get_contents( $file );
			foreach ( $deprecated_list as $func ) {
				if ( preg_match( '/\b' . preg_quote( $func, '/' ) . '\s*\(/', $content ) ) {
					$deprecated[] = $func;
				}
			}

			// Stop if found enough.
			if ( count( $deprecated ) >= 5 ) {
				break;
			}
		}

		return array_unique( $deprecated );
	}
}
