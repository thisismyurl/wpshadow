<?php
/**
 * Theme External Resource Dependencies Diagnostic
 *
 * Checks for external resource dependencies (CDNs, third-party APIs) in
 * the active theme that could impact site performance or availability.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6032.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme External Resource Dependencies Diagnostic Class
 *
 * Identifies external resource dependencies that could affect performance.
 *
 * @since 1.6032.1200
 */
class Diagnostic_Theme_External_Resource_Dependencies extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-external-resource-dependencies';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Theme External Resource Dependencies';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for external CDN and API dependencies';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6032.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wp_scripts, $wp_styles;

		$external_resources = array();

		// Check enqueued scripts for external URLs.
		if ( ! empty( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $script ) {
				if ( ! empty( $script->src ) && self::is_external_url( $script->src ) ) {
					$external_resources[] = array(
						'type'   => 'script',
						'handle' => $handle,
						'url'    => $script->src,
					);
				}
			}
		}

		// Check enqueued styles for external URLs.
		if ( ! empty( $wp_styles->registered ) ) {
			foreach ( $wp_styles->registered as $handle => $style ) {
				if ( ! empty( $style->src ) && self::is_external_url( $style->src ) ) {
					$external_resources[] = array(
						'type'   => 'style',
						'handle' => $handle,
						'url'    => $style->src,
					);
				}
			}
		}

		// Scan theme files for hardcoded external URLs.
		$theme_dir = get_template_directory();
		$patterns  = array(
			'//fonts.googleapis.com',
			'//cdnjs.cloudflare.com',
			'//maxcdn.bootstrapcdn.com',
			'//ajax.googleapis.com',
			'//code.jquery.com',
			'//use.fontawesome.com',
		);

		$theme_files = self::get_theme_files( $theme_dir );
		foreach ( $theme_files as $file ) {
			$content = file_get_contents( $file );
			foreach ( $patterns as $pattern ) {
				if ( false !== stripos( $content, $pattern ) ) {
					$external_resources[] = array(
						'type'    => 'hardcoded',
						'pattern' => $pattern,
						'file'    => str_replace( $theme_dir, '', $file ),
					);
				}
			}
		}

		if ( count( $external_resources ) > 5 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of external dependencies */
					__( 'Your theme has %d external resource dependencies that could affect performance.', 'wpshadow' ),
					count( $external_resources )
				),
				'severity'     => 'medium',
				'threat_level' => 35,
				'auto_fixable' => false,
				'details'      => array(
					'external_resources' => array_slice( $external_resources, 0, 20 ),
					'total_count'        => count( $external_resources ),
					'recommendation'     => __( 'Consider hosting critical assets locally or using a CDN with fallback mechanisms.', 'wpshadow' ),
				),
			);
		}

		return null;
	}

	/**
	 * Check if a URL is external.
	 *
	 * @since  1.6032.1200
	 * @param  string $url URL to check.
	 * @return bool True if external, false otherwise.
	 */
	private static function is_external_url( $url ) {
		$site_url = site_url();
		$home_url = home_url();

		return ( 0 !== strpos( $url, $site_url ) && 0 !== strpos( $url, $home_url ) && ( 0 === strpos( $url, 'http://' ) || 0 === strpos( $url, 'https://' ) || 0 === strpos( $url, '//' ) ) );
	}

	/**
	 * Get all PHP files in theme directory.
	 *
	 * @since  1.6032.1200
	 * @param  string $dir Directory to scan.
	 * @return array Array of file paths.
	 */
	private static function get_theme_files( $dir ) {
		$files = array();
		$items = scandir( $dir );

		foreach ( $items as $item ) {
			if ( '.' === $item || '..' === $item ) {
				continue;
			}

			$path = $dir . '/' . $item;

			if ( is_dir( $path ) ) {
				$files = array_merge( $files, self::get_theme_files( $path ) );
			} elseif ( is_file( $path ) && preg_match( '/\.(php|js|css)$/', $item ) ) {
				$files[] = $path;
			}
		}

		return $files;
	}
}
