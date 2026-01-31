<?php
/**
 * Theme Image Optimization Diagnostic
 *
 * Detects unoptimized images in theme templates and assets.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5049.1230
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Image Optimization Diagnostic Class
 *
 * Checks for image optimization issues in theme.
 *
 * @since 1.5049.1230
 */
class Diagnostic_Theme_Image_Optimization extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-image-optimization';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Image Optimization';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for unoptimized theme images';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.5049.1230
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$theme = wp_get_theme();
		$theme_dir = get_stylesheet_directory();
		$issues = array();

		// Check for large images in theme directory.
		$image_extensions = array( 'jpg', 'jpeg', 'png', 'gif', 'webp' );
		$large_images = array();
		$total_size = 0;

		foreach ( $image_extensions as $ext ) {
			$images = glob( $theme_dir . '/**/*.' . $ext );
			if ( $images ) {
				foreach ( $images as $image ) {
					$size = filesize( $image );
					$total_size += $size;
					
					if ( $size > 500000 ) { // > 500KB.
						$large_images[] = array(
							'file' => basename( $image ),
							'size' => size_format( $size ),
						);
					}
				}
			}
		}

		if ( ! empty( $large_images ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of large images */
				_n(
					'%d large image found in theme (>500KB)',
					'%d large images found in theme (>500KB)',
					count( $large_images ),
					'wpshadow'
				),
				count( $large_images )
			);
		}

		// Check if theme supports responsive images.
		if ( ! current_theme_supports( 'post-thumbnails' ) ) {
			$issues[] = __( 'Theme does not support post thumbnails', 'wpshadow' );
		}

		// Check for WebP support.
		$has_webp = false;
		$webp_images = glob( $theme_dir . '/**/*.webp' );
		if ( ! empty( $webp_images ) ) {
			$has_webp = true;
		}

		// Check for modern image formats in functions.php.
		$functions_file = $theme_dir . '/functions.php';
		if ( file_exists( $functions_file ) ) {
			$functions_content = file_get_contents( $functions_file );
			if ( preg_match( '/webp|avif/i', $functions_content ) ) {
				$has_webp = true;
			}
		}

		if ( ! $has_webp && $total_size > 1048576 ) { // > 1MB total.
			$issues[] = __( 'Theme does not use modern image formats (WebP/AVIF)', 'wpshadow' );
		}

		// Check for lazy loading implementation.
		$home_url = home_url( '/' );
		$response = wp_remote_get( $home_url, array( 'timeout' => 10 ) );
		
		if ( ! is_wp_error( $response ) ) {
			$html = wp_remote_retrieve_body( $response );
			$has_lazy_loading = preg_match( '/loading=["\']lazy["\']/i', $html );
			
			if ( ! $has_lazy_loading ) {
				$issues[] = __( 'Images not configured for lazy loading', 'wpshadow' );
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Theme images are not optimized for performance', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'details'     => array(
					'theme'         => $theme->get( 'Name' ),
					'large_images'  => array_slice( $large_images, 0, 5 ),
					'total_size'    => size_format( $total_size ),
					'has_webp'      => $has_webp,
					'issues'        => $issues,
				),
				'kb_link'     => 'https://wpshadow.com/kb/theme-image-optimization',
			);
		}

		return null;
	}
}
