<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

/**
 * Diagnostic: Theme Integrity Check
 *
 * Verifies theme files are intact and not modified or compromised.
 * Modified theme files can indicate compromise or malicious activity.
 *
 * @since 1.2.0
 */
class Test_Theme_Integrity_Check extends Diagnostic_Base {


	/**
	 * Check theme integrity
	 *
	 * @return array|null Diagnostic array if issues found, null if all good
	 */
	public static function check(): ?array {
		$integrity_check = self::verify_theme_integrity();

		if ( $integrity_check['threat_level'] === 0 ) {
			return null;
		}

		return array(
			'threat_level'  => $integrity_check['threat_level'],
			'threat_color'  => 'orange',
			'passed'        => false,
			'issue'         => $integrity_check['issue'],
			'metadata'      => $integrity_check,
			'kb_link'       => 'https://wpshadow.com/kb/theme-security-integrity/',
			'training_link' => 'https://wpshadow.com/training/wordpress-theme-security/',
		);
	}

	/**
	 * Guardian Sub-Test: Core theme file check
	 *
	 * @return array Test result
	 */
	public static function test_core_theme_files(): array {
		$theme     = wp_get_theme();
		$theme_dir = $theme->get_theme_root() . '/' . $theme->get_stylesheet();

		$required_files = array( 'index.php', 'style.css' );
		$missing_files  = array();

		foreach ( $required_files as $file ) {
			if ( ! file_exists( $theme_dir . '/' . $file ) ) {
				$missing_files[] = $file;
			}
		}

		return array(
			'test_name'     => 'Core Theme Files',
			'theme'         => $theme->get( 'Name' ),
			'missing_files' => $missing_files,
			'passed'        => empty( $missing_files ),
			'description'   => empty( $missing_files ) ? 'All core theme files present' : sprintf( '%d core files missing', count( $missing_files ) ),
		);
	}

	/**
	 * Guardian Sub-Test: Theme executable files
	 *
	 * @return array Test result
	 */
	public static function test_theme_executable_files(): array {
		$theme     = wp_get_theme();
		$theme_dir = $theme->get_theme_root() . '/' . $theme->get_stylesheet();

		$executables = array();
		$php_files   = glob( $theme_dir . '/**/*.php', GLOB_RECURSIVE );

		foreach ( $php_files as $file ) {
			// Check for suspicious executable patterns
			$content = file_get_contents( $file );

			// Look for shell_exec, system, exec, passthru
			if ( preg_match( '/\b(shell_exec|system|exec|passthru|proc_open|popen)\s*\(/i', $content ) ) {
				$executables[] = str_replace( $theme_dir, '', $file );
			}
		}

		return array(
			'test_name'        => 'Theme Executable Calls',
			'suspicious_files' => $executables,
			'passed'           => empty( $executables ),
			'description'      => empty( $executables ) ? 'No suspicious executable calls found' : sprintf( '%d files with executable calls', count( $executables ) ),
		);
	}

	/**
	 * Guardian Sub-Test: Theme base functionality
	 *
	 * @return array Test result
	 */
	public static function test_theme_functions(): array {
		$theme     = wp_get_theme();
		$theme_dir = $theme->get_theme_root() . '/' . $theme->get_stylesheet();

		$has_functions     = file_exists( $theme_dir . '/functions.php' );
		$has_template_tags = file_exists( $theme_dir . '/inc/template-tags.php' ) ||
			file_exists( $theme_dir . '/template-tags.php' );

		return array(
			'test_name'         => 'Theme Functions',
			'has_functions'     => $has_functions,
			'has_template_tags' => $has_template_tags,
			'passed'            => $has_functions,
			'description'       => $has_functions ? 'Theme functions.php file present' : 'Theme functions.php missing',
		);
	}

	/**
	 * Guardian Sub-Test: Suspicious theme modifications
	 *
	 * @return array Test result
	 */
	public static function test_suspicious_modifications(): array {
		$theme     = wp_get_theme();
		$theme_dir = $theme->get_theme_root() . '/' . $theme->get_stylesheet();

		$suspicious = array();
		$php_files  = glob( $theme_dir . '/**/*.php', GLOB_RECURSIVE );

		foreach ( array_slice( $php_files, 0, 10 ) as $file ) {
			$content = file_get_contents( $file );

			// Look for base64 encoded content
			if ( preg_match( '/base64_decode\s*\(/', $content ) ) {
				$suspicious[] = array(
					'file'  => basename( $file ),
					'issue' => 'base64_decode found',
				);
			}

			// Look for eval
			if ( preg_match( '/eval\s*\(/', $content ) ) {
				$suspicious[] = array(
					'file'  => basename( $file ),
					'issue' => 'eval() found',
				);
			}
		}

		return array(
			'test_name'        => 'Suspicious Modifications',
			'suspicious_items' => $suspicious,
			'passed'           => empty( $suspicious ),
			'description'      => empty( $suspicious ) ? 'No suspicious code patterns found' : sprintf( '%d suspicious patterns detected', count( $suspicious ) ),
		);
	}

	/**
	 * Verify theme integrity
	 *
	 * @return array Integrity check results
	 */
	private static function verify_theme_integrity(): array {
		$theme     = wp_get_theme();
		$theme_dir = $theme->get_theme_root() . '/' . $theme->get_stylesheet();

		$threat_level = 0;
		$issues       = array();

		// Check for missing core files
		$required_files = array( 'index.php', 'style.css' );
		foreach ( $required_files as $file ) {
			if ( ! file_exists( $theme_dir . '/' . $file ) ) {
				$issues[]     = 'Missing core theme files';
				$threat_level = max( $threat_level, 60 );
				break;
			}
		}

		// Check for suspicious code
		$php_files        = glob( $theme_dir . '/**/*.php', GLOB_RECURSIVE );
		$suspicious_count = 0;

		foreach ( array_slice( $php_files, 0, 5 ) as $file ) {
			$content = file_get_contents( $file );

			if ( preg_match( '/(base64_decode|eval|shell_exec|system|exec)\s*\(/', $content ) ) {
				++$suspicious_count;
			}
		}

		if ( $suspicious_count > 0 ) {
			$issues[]     = 'Suspicious code patterns detected in theme files';
			$threat_level = max( $threat_level, 75 );
		}

		$issue = ! empty( $issues ) ? implode( '; ', $issues ) : 'Theme integrity verified';

		return array(
			'threat_level' => $threat_level,
			'issue'        => $issue,
			'theme_name'   => $theme->get( 'Name' ),
		);
	}

	/**
	 * Get diagnostic name
	 *
	 * @return string
	 */
	public static function get_name(): string {
		return 'Theme Integrity Check';
	}

	/**
	 * Get diagnostic description
	 *
	 * @return string
	 */
	public static function get_description(): string {
		return 'Verifies active theme files are intact and not modified or compromised';
	}

	/**
	 * Get diagnostic category
	 *
	 * @return string
	 */
	public static function get_category(): string {
		return 'Security';
	}
}
