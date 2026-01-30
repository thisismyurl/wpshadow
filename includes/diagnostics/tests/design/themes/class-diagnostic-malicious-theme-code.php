<?php
/**
 * Malicious Theme Code Diagnostic
 *
 * Detects backdoors and malicious code in themes.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5029.1715
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Malicious Theme Code Class
 *
 * Scans theme files for suspicious code patterns.
 *
 * @since 1.5029.1715
 */
class Diagnostic_Malicious_Theme_Code extends Diagnostic_Base {

	protected static $slug        = 'malicious-theme-code';
	protected static $title       = 'Malicious Theme Code';
	protected static $description = 'Detects backdoors in theme files';
	protected static $family      = 'themes';

	public static function check() {
		$cache_key = 'wpshadow_malicious_theme';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$current_theme = wp_get_theme();
		$theme_dir     = $current_theme->get_stylesheet_directory();
		$suspicious    = array();

		// Malicious patterns.
		$patterns = array(
			'/eval\s*\(\s*base64_decode/i' => 'Base64 encoded eval()',
			'/base64_decode\s*\([\'"][\w\/\+=]{50,}[\'"]\)/i' => 'Long base64 string',
			'/\$_POST\[[\'"]\w+[\'"]\]\s*==/i' => 'POST backdoor',
			'/system\s*\(/i' => 'System command execution',
			'/exec\s*\(/i' => 'Exec command',
			'/passthru\s*\(/i' => 'Passthru command',
			'/shell_exec\s*\(/i' => 'Shell execution',
			'/\$_GET\[[\'"]\w+[\'"]\]\(\)/i' => 'Variable function call from GET',
		);

		$php_files = glob( $theme_dir . '/*.php' );
		$php_files = array_merge( $php_files, glob( $theme_dir . '/**/*.php' ) );

		foreach ( $php_files as $file ) {
			if ( ! is_readable( $file ) ) {
				continue;
			}

			$content  = file_get_contents( $file );
			$filename = str_replace( $theme_dir . '/', '', $file );

			foreach ( $patterns as $pattern => $description ) {
				if ( preg_match( $pattern, $content ) ) {
					$suspicious[] = array(
						'file' => $filename,
						'pattern' => $description,
						'severity' => 'critical',
					);
				}
			}

			// Limit to 30 files.
			if ( count( $php_files ) > 30 ) {
				break;
			}
		}

		if ( ! empty( $suspicious ) ) {
			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: 1: theme name, 2: count */
					__( 'Theme "%1$s" contains %2$d suspicious code patterns! Review immediately.', 'wpshadow' ),
					$current_theme->get( 'Name' ),
					count( $suspicious )
				),
				'severity'     => 'critical',
				'threat_level' => 95,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/security-malicious-theme-code',
				'data'         => array(
					'theme_name' => $current_theme->get( 'Name' ),
					'suspicious_patterns' => array_slice( $suspicious, 0, 20 ),
					'total_issues' => count( $suspicious ),
				),
			);

			set_transient( $cache_key, $result, 6 * HOUR_IN_SECONDS );
			return $result;
		}

		set_transient( $cache_key, null, 7 * DAY_IN_SECONDS );
		return null;
	}
}
