<?php
/**
 * Theme Image Optimization Diagnostic
 *
 * Scans theme directory for unoptimized images and checks for
 * WebP alternatives to improve page load performance.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6028.1720
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
 * Analyzes theme images for optimization opportunities:
 * - Large unoptimized images
 * - Missing WebP alternatives
 * - Oversized images for dimensions
 * - Potential file size savings
 *
 * @since 1.6028.1720
 */
class Diagnostic_Theme_Image_Optimization extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @since 1.6028.1720
	 * @var   string
	 */
	protected static $slug = 'theme-image-optimization';

	/**
	 * The diagnostic title
	 *
	 * @since 1.6028.1720
	 * @var   string
	 */
	protected static $title = 'Theme Image Optimization';

	/**
	 * The diagnostic description
	 *
	 * @since 1.6028.1720
	 * @var   string
	 */
	protected static $description = 'Identifies unoptimized images in theme directory';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @since 1.6028.1720
	 * @var   string
	 */
	protected static $family = 'performance';

	/**
	 * Cache duration (6 hours)
	 *
	 * @since 1.6028.1720
	 * @var   int
	 */
	private const CACHE_DURATION = 21600;

	/**
	 * Minimum file size to flag (100KB)
	 *
	 * @since 1.6028.1720
	 * @var   int
	 */
	private const MIN_FILE_SIZE = 102400;

	/**
	 * Image extensions to scan
	 *
	 * @since 1.6028.1720
	 * @var   array
	 */
	private const IMAGE_EXTENSIONS = array( 'jpg', 'jpeg', 'png', 'gif' );

	/**
	 * Run the diagnostic check.
	 *
	 * Scans theme directory for unoptimized images.
	 *
	 * @since  1.6028.1720
	 * @return array|null Finding array if unoptimized images found, null otherwise.
	 */
	public static function check() {
		$cached = get_transient( 'wpshadow_theme_image_optimization_check' );
		if ( false !== $cached ) {
			return $cached;
		}

		$analysis = self::analyze_theme_images();

		if ( empty( $analysis['unoptimized_images'] ) ) {
			set_transient( 'wpshadow_theme_image_optimization_check', null, self::CACHE_DURATION );
			return null;
		}

		$result = self::build_finding( $analysis );

		set_transient( 'wpshadow_theme_image_optimization_check', $result, self::CACHE_DURATION );

		return $result;
	}

	/**
	 * Analyze theme images for optimization opportunities.
	 *
	 * @since  1.6028.1720
	 * @return array {
	 *     Analysis results.
	 *
	 *     @type array $unoptimized_images List of images needing optimization.
	 *     @type array $webp_missing       Images without WebP alternatives.
	 *     @type int   $total_images       Total images scanned.
	 *     @type int   $total_size         Total size of unoptimized images.
	 *     @type int   $potential_savings  Estimated file size savings.
	 * }
	 */
	private static function analyze_theme_images(): array {
		$theme_dir           = get_template_directory();
		$images              = self::scan_theme_images( $theme_dir );
		$unoptimized_images  = array();
		$webp_missing        = array();
		$total_size          = 0;
		$potential_savings   = 0;

		foreach ( $images as $image_path ) {
			$file_size = filesize( $image_path );

			// Skip small images.
			if ( $file_size < self::MIN_FILE_SIZE ) {
				continue;
			}

			$image_data = self::analyze_image( $image_path, $file_size );

			if ( $image_data['needs_optimization'] ) {
				$unoptimized_images[] = $image_data;
				$total_size          += $file_size;
				$potential_savings   += $image_data['potential_savings'];
			}

			if ( $image_data['missing_webp'] ) {
				$webp_missing[] = $image_data;
			}
		}

		return array(
			'unoptimized_images' => $unoptimized_images,
			'webp_missing'       => $webp_missing,
			'total_images'       => count( $images ),
			'total_size'         => $total_size,
			'potential_savings'  => $potential_savings,
		);
	}

	/**
	 * Scan theme directory for image files.
	 *
	 * @since  1.6028.1720
	 * @param  string $theme_dir Theme directory path.
	 * @return array Array of image file paths.
	 */
	private static function scan_theme_images( string $theme_dir ): array {
		$images = array();

		if ( ! is_dir( $theme_dir ) ) {
			return $images;
		}

		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator( $theme_dir, \RecursiveDirectoryIterator::SKIP_DOTS ),
			\RecursiveIteratorIterator::SELF_FIRST
		);

		foreach ( $iterator as $file ) {
			if ( ! $file->isFile() ) {
				continue;
			}

			$extension = strtolower( $file->getExtension() );

			if ( in_array( $extension, self::IMAGE_EXTENSIONS, true ) ) {
				// Exclude node_modules and vendor.
				$path = $file->getPathname();
				if ( false === strpos( $path, '/node_modules/' ) && false === strpos( $path, '/vendor/' ) ) {
					$images[] = $path;
				}
			}
		}

		return $images;
	}

	/**
	 * Analyze individual image for optimization.
	 *
	 * @since  1.6028.1720
	 * @param  string $image_path Image file path.
	 * @param  int    $file_size  File size in bytes.
	 * @return array Image analysis data.
	 */
	private static function analyze_image( string $image_path, int $file_size ): array {
		$needs_optimization = false;
		$potential_savings  = 0;
		$missing_webp       = false;
		$issues             = array();

		// Get image dimensions.
		$image_info = getimagesize( $image_path );
		$width      = $image_info ? $image_info[0] : 0;
		$height     = $image_info ? $image_info[1] : 0;

		// Calculate bytes per pixel.
		if ( $width > 0 && $height > 0 ) {
			$pixels          = $width * $height;
			$bytes_per_pixel = $file_size / $pixels;

			// Flag if bytes per pixel is high (>2 bytes/pixel suggests poor compression).
			if ( $bytes_per_pixel > 2 ) {
				$needs_optimization = true;
				$potential_savings  = (int) ( $file_size * 0.4 ); // Estimate 40% savings.
				$issues[]           = 'Poor compression ratio';
			}
		}

		// Check for WebP alternative.
		$webp_path = preg_replace( '/\.(jpg|jpeg|png|gif)$/i', '.webp', $image_path );
		if ( ! file_exists( $webp_path ) ) {
			$missing_webp = true;
			$issues[]     = 'No WebP alternative';
		}

		// Flag very large files (>500KB).
		if ( $file_size > 512000 ) {
			$needs_optimization = true;
			if ( 0 === $potential_savings ) {
				$potential_savings = (int) ( $file_size * 0.3 ); // Estimate 30% savings.
			}
			$issues[] = 'Large file size';
		}

		return array(
			'path'               => str_replace( get_template_directory(), '', $image_path ),
			'file_size'          => $file_size,
			'width'              => $width,
			'height'             => $height,
			'needs_optimization' => $needs_optimization,
			'missing_webp'       => $missing_webp,
			'potential_savings'  => $potential_savings,
			'issues'             => $issues,
		);
	}

	/**
	 * Build finding array from analysis.
	 *
	 * @since  1.6028.1720
	 * @param  array $analysis Analysis results.
	 * @return array Finding array.
	 */
	private static function build_finding( array $analysis ): array {
		$unoptimized_count = count( $analysis['unoptimized_images'] );
		$severity          = 'low';
		$threat            = 20;

		if ( $unoptimized_count >= 5 ) {
			$severity = 'medium';
			$threat   = 25;
		}

		if ( $unoptimized_count >= 10 ) {
			$severity = 'high';
			$threat   = 30;
		}

		$description = sprintf(
			/* translators: 1: unoptimized count, 2: total scanned */
			_n(
				'Found %1$d unoptimized image in theme (%2$d total scanned)',
				'Found %1$d unoptimized images in theme (%2$d total scanned)',
				$unoptimized_count,
				'wpshadow'
			),
			$unoptimized_count,
			$analysis['total_images']
		);

		$recommendations = array(
			__( 'Optimize images before uploading to theme', 'wpshadow' ),
			__( 'Generate WebP alternatives for faster loading', 'wpshadow' ),
			__( 'Use image optimization tools (ImageOptim, TinyPNG)', 'wpshadow' ),
			__( 'Consider lazy loading for below-fold images', 'wpshadow' ),
		);

		if ( $analysis['potential_savings'] > 1048576 ) {
			$recommendations[] = sprintf(
				/* translators: %s: file size */
				__( 'Potential savings: %s from image optimization', 'wpshadow' ),
				size_format( $analysis['potential_savings'] )
			);
		}

		// Top 5 largest unoptimized images.
		usort(
			$analysis['unoptimized_images'],
			fn( $a, $b ) => $b['file_size'] <=> $a['file_size']
		);
		$top_unoptimized = array_slice( $analysis['unoptimized_images'], 0, 5 );

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => $description,
			'severity'    => $severity,
			'threat_level' => $threat,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/theme-image-optimization',
			'family'      => self::$family,
			'meta'        => array(
				'total_images'       => $analysis['total_images'],
				'unoptimized_count'  => $unoptimized_count,
				'webp_missing_count' => count( $analysis['webp_missing'] ),
				'total_size'         => $analysis['total_size'],
				'potential_savings'  => $analysis['potential_savings'],
			),
			'details'     => array(
				'top_unoptimized'   => $top_unoptimized,
				'recommendations'   => $recommendations,
				'optimization_note' => __( 'Optimized images improve page load speed and user experience', 'wpshadow' ),
			),
		);
	}
}
