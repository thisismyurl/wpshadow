<?php declare(strict_types=1);
/**
 * Theme Performance & Features Diagnostic
 *
 * Philosophy: Educate about theme optimization opportunities
 * Guides to Pro features for theme analysis and Guardian theme scanning
 *
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

/**
 * Check active theme for performance and feature issues.
 */
class Diagnostic_Theme_Performance {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
		$theme = wp_get_theme();
		if ( ! $theme->exists() ) {
			return null;
		}
		
		$issues = array();
		
		// Check if theme is child theme (best practice)
		if ( ! is_child_theme() ) {
			$issues[] = 'Not using a child theme - customizations may be lost during updates';
		}
		
		// Check if theme has explicit theme support for modern features
		$supports = get_theme_support( 'post-formats' );
		if ( false === $supports && false === get_theme_support( 'custom-logo' ) && false === get_theme_support( 'html5' ) ) {
			$issues[] = 'Theme appears outdated - missing modern WordPress features support';
		}
		
		// Check if theme has proper responsive design support
		$theme_uri = $theme->get( 'ThemeURI' );
		if ( empty( $theme_uri ) ) {
			$issues[] = 'Theme is missing project documentation/homepage';
		}
		
		// Check theme size (very large themes are slower)
		$theme_dir = get_theme_root() . '/' . get_template();
		if ( is_dir( $theme_dir ) ) {
			$size = self::dir_size( $theme_dir );
			if ( $size > 50 * 1024 * 1024 ) { // 50MB
				$issues[] = 'Theme folder is very large (' . round( $size / 1024 / 1024 ) . 'MB) - may slow down file operations';
			}
		}
		
		if ( ! empty( $issues ) ) {
			return array(
				'title'       => 'Theme Performance Recommendations',
				'description' => 'Your theme could be optimized for better performance and maintainability: ' . implode( '. ', $issues ) . '.',
				'severity'    => 'low',
				'category'    => 'design',
				'kb_link'     => 'https://wpshadow.com/kb/choosing-wordpress-themes/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=theme-performance',
				'auto_fixable' => false,
				'threat_level' => 20,
			);
		}
		
		return null;
	}
	
	/**
	 * Get directory size recursively
	 *
	 * @param string $dir Directory path
	 * @return int Size in bytes
	 */
	private static function dir_size( $dir ) {
		$size = 0;
		$files = @scandir( $dir );
		if ( is_array( $files ) ) {
			foreach ( $files as $file ) {
				if ( '.' !== $file && '..' !== $file ) {
					$filepath = $dir . '/' . $file;
					if ( is_dir( $filepath ) ) {
						$size += self::dir_size( $filepath );
					} elseif ( is_file( $filepath ) ) {
						$size += filesize( $filepath );
					}
				}
			}
		}
		return $size;
	}
}
