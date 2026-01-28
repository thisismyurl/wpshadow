<?php
/**
 * Image Format Inefficiency Diagnostic
 *
 * Detects PNGs that would be smaller as JPEGs, wasting bandwidth and
 * slowing page load times. Identifies format optimization opportunities.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6028.1710
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Image_Format_Inefficiency Class
 *
 * Analyzes images in media library to detect PNGs that should be JPEGs.
 * Estimates bandwidth savings from format conversion.
 *
 * @since 1.6028.1710
 */
class Diagnostic_Image_Format_Inefficiency extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'image-format-inefficiency';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Image Format Inefficiency (JPEG vs PNG)';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects PNGs larger than optimal JPEG size';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6028.1710
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$analysis = self::analyze_image_formats();

		if ( $analysis['total_images'] === 0 ) {
			return null; // No images to analyze.
		}

		$inefficiency_pct = $analysis['inefficiency_percentage'];

		if ( $inefficiency_pct < 10 ) {
			return null; // <10% inefficiency is acceptable.
		}

		// Determine severity based on inefficiency percentage.
		if ( $inefficiency_pct > 20 ) {
			$severity     = 'low';
			$threat_level = 35;
		} else {
			$severity     = 'info';
			$threat_level = 25;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: 1: percentage of inefficient images, 2: number of images */
				__( '%1$s%% of images (%2$d) are in suboptimal format, wasting %3$s bandwidth', 'wpshadow' ),
				number_format( $inefficiency_pct, 1 ),
				$analysis['inefficient_count'],
				size_format( $analysis['estimated_savings'] )
			),
			'severity'    => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/image-format-optimization',
			'family'      => self::$family,
			'meta'        => array(
				'affected_count'      => $analysis['inefficient_count'],
				'total_images'        => $analysis['total_images'],
				'inefficiency_pct'    => round( $inefficiency_pct, 1 ),
				'estimated_savings'   => $analysis['estimated_savings'],
				'savings_formatted'   => size_format( $analysis['estimated_savings'] ),
				'recommended'         => __( 'Use JPEG for photos, PNG for graphics with transparency', 'wpshadow' ),
				'impact_level'        => 'low',
				'immediate_actions'   => array(
					__( 'Review large PNG files (>100KB)', 'wpshadow' ),
					__( 'Convert photographic PNGs to JPEG', 'wpshadow' ),
					__( 'Use image optimization plugins', 'wpshadow' ),
					__( 'Monitor page load times', 'wpshadow' ),
				),
			),
			'details'     => array(
				'why_important' => __( 'Using the wrong image format wastes bandwidth and slows page load times. PNG is lossless compression ideal for graphics with sharp edges and transparency, but produces huge files for photographs. JPEG uses lossy compression perfect for photos but poor for graphics. Choosing the right format can reduce file size by 50-80% with no visible quality loss.', 'wpshadow' ),
				'user_impact'   => array(
					__( 'Slower Page Load: Large images delay time to interactive', 'wpshadow' ),
					__( 'Wasted Bandwidth: Users download unnecessarily large files', 'wpshadow' ),
					__( 'Higher Hosting Costs: More bandwidth usage', 'wpshadow' ),
					__( 'Poor Mobile Experience: Large images on slow connections', 'wpshadow' ),
				),
				'format_analysis' => array(
					'total_images'       => $analysis['total_images'],
					'inefficient_count'  => $analysis['inefficient_count'],
					'inefficiency_pct'   => round( $inefficiency_pct, 1 ),
					'png_count'          => $analysis['png_count'],
					'jpeg_count'         => $analysis['jpeg_count'],
					'estimated_savings'  => size_format( $analysis['estimated_savings'] ),
				),
				'examples'      => $analysis['examples'],
				'solution_options' => array(
					'free'     => array(
						'label'       => __( 'Manual Format Conversion', 'wpshadow' ),
						'description' => __( 'Download, convert, and re-upload images manually', 'wpshadow' ),
						'steps'       => array(
							__( 'Identify large PNGs (>100KB) with photographic content', 'wpshadow' ),
							__( 'Download image and open in image editor', 'wpshadow' ),
							__( 'Save as JPEG with 80-90% quality', 'wpshadow' ),
							__( 'Re-upload to WordPress media library', 'wpshadow' ),
							__( 'Update image references in content', 'wpshadow' ),
						),
					),
					'premium'  => array(
						'label'       => __( 'Image Optimization Plugin', 'wpshadow' ),
						'description' => __( 'Use Imagify, ShortPixel, or EWWW to auto-convert formats', 'wpshadow' ),
						'steps'       => array(
							__( 'Install Imagify ($4.99+/month) or ShortPixel ($4.99+/month)', 'wpshadow' ),
							__( 'Enable "Convert PNG to JPEG" option', 'wpshadow' ),
							__( 'Set transparency detection (keep PNG if transparent)', 'wpshadow' ),
							__( 'Bulk optimize existing images', 'wpshadow' ),
							__( 'Auto-optimize new uploads', 'wpshadow' ),
						),
					),
					'advanced' => array(
						'label'       => __( 'Modern Format Adoption (WebP/AVIF)', 'wpshadow' ),
						'description' => __( 'Convert all images to next-gen formats for 30-50% savings', 'wpshadow' ),
						'steps'       => array(
							__( 'Install WebP plugin (EWWW, Imagify, ShortPixel)', 'wpshadow' ),
							__( 'Enable WebP conversion with fallback to JPEG/PNG', 'wpshadow' ),
							__( 'Consider AVIF format (even smaller, less browser support)', 'wpshadow' ),
							__( 'Use <picture> element for progressive enhancement', 'wpshadow' ),
							__( 'Monitor browser compatibility', 'wpshadow' ),
						),
					),
				),
				'best_practices' => array(
					__( 'JPEG: Use for photographs and images with gradients', 'wpshadow' ),
					__( 'PNG: Use for logos, icons, graphics with transparency', 'wpshadow' ),
					__( 'WebP: Modern format, 30% smaller, 95%+ browser support', 'wpshadow' ),
					__( 'AVIF: Next-gen format, 50% smaller, 80%+ browser support', 'wpshadow' ),
					__( 'SVG: Use for simple icons and logos (vector format)', 'wpshadow' ),
					__( 'Target 80-90% quality for JPEG (diminishing returns above 90%)', 'wpshadow' ),
				),
				'testing_steps' => array(
					'verification' => array(
						__( 'Run this diagnostic after format conversion', 'wpshadow' ),
						__( 'Test page load speed with GTmetrix or PageSpeed Insights', 'wpshadow' ),
						__( 'Verify image quality visually on live site', 'wpshadow' ),
						__( 'Monitor bandwidth usage in hosting dashboard', 'wpshadow' ),
					),
					'expected_result' => __( '<10% of images in suboptimal format', 'wpshadow' ),
				),
			),
		);
	}

	/**
	 * Analyze image formats across media library.
	 *
	 * @since  1.6028.1710
	 * @return array Format analysis data.
	 */
	private static function analyze_image_formats() {
		global $wpdb;

		$result = array(
			'total_images'         => 0,
			'inefficient_count'    => 0,
			'inefficiency_percentage' => 0,
			'png_count'            => 0,
			'jpeg_count'           => 0,
			'estimated_savings'    => 0,
			'examples'             => array(),
		);

		// Count images by format.
		$formats = $wpdb->get_results(
			"SELECT post_mime_type, COUNT(*) as count
			FROM {$wpdb->posts}
			WHERE post_type = 'attachment'
			AND post_mime_type IN ('image/png', 'image/jpeg', 'image/jpg')
			GROUP BY post_mime_type",
			ARRAY_A
		);

		if ( empty( $formats ) ) {
			return $result;
		}

		foreach ( $formats as $format ) {
			if ( $format['post_mime_type'] === 'image/png' ) {
				$result['png_count'] = (int) $format['count'];
			} elseif ( in_array( $format['post_mime_type'], array( 'image/jpeg', 'image/jpg' ), true ) ) {
				$result['jpeg_count'] += (int) $format['count'];
			}
		}

		$result['total_images'] = $result['png_count'] + $result['jpeg_count'];

		if ( $result['total_images'] === 0 ) {
			return $result;
		}

		// Analyze PNG files for potential JPEG conversion.
		// PNGs >50KB without transparency are likely candidates.
		$large_pngs = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ID, guid
				FROM {$wpdb->posts}
				WHERE post_type = 'attachment'
				AND post_mime_type = 'image/png'
				ORDER BY post_date DESC
				LIMIT %d",
				100 // Sample for performance.
			),
			ARRAY_A
		);

		$inefficient_count  = 0;
		$total_waste        = 0;
		$example_limit      = 5;
		$upload_dir         = wp_upload_dir();

		foreach ( $large_pngs as $png ) {
			$file_path = str_replace( $upload_dir['baseurl'], $upload_dir['basedir'], $png['guid'] );
			
			if ( ! file_exists( $file_path ) ) {
				continue;
			}

			$file_size = filesize( $file_path );

			// Skip small files (<50KB).
			if ( $file_size < 51200 ) {
				continue;
			}

			// Check if image has transparency (requires PNG).
			$has_transparency = self::image_has_transparency( $file_path );
			if ( $has_transparency ) {
				continue; // PNG is correct format.
			}

			// Estimate JPEG size (typically 30-50% of PNG for photos).
			$estimated_jpeg_size = $file_size * 0.4; // 40% estimate.
			$potential_savings   = $file_size - $estimated_jpeg_size;

			if ( $potential_savings > 20480 ) { // >20KB savings.
				$inefficient_count++;
				$total_waste += $potential_savings;

				if ( count( $result['examples'] ) < $example_limit ) {
					$result['examples'][] = array(
						'filename'          => basename( $file_path ),
						'current_size'      => size_format( $file_size ),
						'estimated_jpeg'    => size_format( $estimated_jpeg_size ),
						'potential_savings' => size_format( $potential_savings ),
					);
				}
			}
		}

		$result['inefficient_count']       = $inefficient_count;
		$result['estimated_savings']       = $total_waste;
		$result['inefficiency_percentage'] = ( $inefficient_count / max( $result['png_count'], 1 ) ) * 100;

		return $result;
	}

	/**
	 * Check if PNG image has transparency.
	 *
	 * @since  1.6028.1710
	 * @param  string $file_path Path to PNG file.
	 * @return bool True if image has transparency.
	 */
	private static function image_has_transparency( $file_path ) {
		if ( ! function_exists( 'imagecreatefrompng' ) ) {
			return false; // Can't determine, assume no transparency.
		}

		$img = @imagecreatefrompng( $file_path );
		if ( ! $img ) {
			return false;
		}

		// Check if alpha channel exists.
		$width  = imagesx( $img );
		$height = imagesy( $img );

		// Sample 10 pixels to check for transparency.
		for ( $i = 0; $i < 10; $i++ ) {
			$x     = rand( 0, $width - 1 );
			$y     = rand( 0, $height - 1 );
			$rgba  = imagecolorat( $img, $x, $y );
			$alpha = ( $rgba & 0x7F000000 ) >> 24;

			if ( $alpha > 0 ) {
				imagedestroy( $img );
				return true; // Has transparency.
			}
		}

		imagedestroy( $img );
		return false;
	}
}
