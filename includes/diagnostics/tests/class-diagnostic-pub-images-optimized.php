<?php
/**
 * Diagnostic: Published Images Optimized
 *
 * Checks if images in published posts are optimized for performance.
 * Analyzes file sizes, modern format adoption (WebP/AVIF), and overall optimization status.
 *
 * Category: Content Publishing
 * Philosophy: Commandment #7 (Ridiculously Good for Free), #8 (Inspire Confidence), #9 (Everything Has a KPI)
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Pub_Images_Optimized Class
 *
 * Detects unoptimized images in published content by checking:
 * - File sizes (flags images >5MB)
 * - Modern format adoption (WebP/AVIF vs JPG/PNG)
 * - Overall optimization percentage
 *
 * Returns null when images are well optimized (>75% optimized, good format adoption).
 * Returns finding array when optimization issues are detected.
 */
class Diagnostic_Pub_Images_Optimized extends Diagnostic_Base {
	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'pub-images-optimized';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Published Images Optimized';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if images in published posts are optimized for performance and use modern formats';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'content_publishing';

	/**
	 * Diagnostic family label
	 *
	 * @var string
	 */
	protected static $family_label = 'Content Publishing';

	/**
	 * Get diagnostic ID
	 */
	public static function get_id(): string {
		return 'pub-images-optimized';
	}

	/**
	 * Get diagnostic name
	 */
	public static function get_name(): string {
		return __( 'Images Are Optimized', 'wpshadow' );
	}

	/**
	 * Get diagnostic description
	 */
	public static function get_description(): string {
		return __( 'Images compressed and in modern formats?', 'wpshadow' );
	}

	/**
	 * Get diagnostic category
	 */
	public static function get_category(): string {
		return 'content_publishing';
	}

	/**
	 * Get threat level
	 *
	 * @return int 0-100 severity level
	 */
	public static function get_threat_level(): int {
		return 25;
	}

	/**
	 * Run diagnostic test
	 *
	 * Executes the diagnostic check and returns structured results.
	 *
	 * @since  1.2601.2148
	 * @return array Diagnostic results with status and data
	 */
	public static function run(): array {
		$result = self::check();

		// If check returns null, no issues found.
		if ( is_null( $result ) ) {
			return array(
				'status'  => 'pass',
				'message' => __( 'Published images are well optimized', 'wpshadow' ),
				'data'    => array(),
			);
		}

		// Issues found - return the finding data.
		return array(
			'status'  => 'fail',
			'message' => isset( $result['description'] ) ? $result['description'] : __( 'Image optimization issues detected', 'wpshadow' ),
			'data'    => $result,
		);
	}

	/**
	 * Get KB article URL
	 */
	public static function get_kb_article(): string {
		return 'https://wpshadow.com/kb/pub-images-optimized';
	}

	/**
	 * Get training video URL
	 */
	public static function get_training_video(): string {
		return 'https://wpshadow.com/training/category-content-publishing';
	}

	/**
	 * Run the diagnostic check
	 *
	 * Checks if images in recent published posts are optimized for performance.
	 * Analyzes file sizes, formats (WebP vs traditional), and optimization status.
	 *
	 * @since  1.2601.2148
	 * @return array|null Null if images are optimized, array of findings if issues detected.
	 */
	public static function check(): ?array {
		// Get recent published posts to analyze.
		$posts = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => 15,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		// No posts found - nothing to check.
		if ( empty( $posts ) ) {
			return null;
		}

		$total_images       = 0;
		$unoptimized_images = 0;
		$large_images       = array(); // Images >5MB.
		$webp_images        = 0;
		$traditional_images = 0; // JPG/PNG.

		foreach ( $posts as $post ) {
			// Extract images from post content.
			preg_match_all( '/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $post->post_content, $matches );

			if ( ! empty( $matches[1] ) ) {
				foreach ( $matches[1] as $img_url ) {
					++$total_images;

					// Get attachment ID from URL.
					$attachment_id = attachment_url_to_postid( $img_url );

					if ( $attachment_id ) {
						// Get file path and check file size.
						$file_path = get_attached_file( $attachment_id );

						if ( $file_path && file_exists( $file_path ) ) {
							$file_size = filesize( $file_path );

							// Flag images larger than 5MB as unoptimized.
							if ( $file_size > 5242880 ) { // 5MB in bytes.
								++$unoptimized_images;
								$large_images[] = array(
									'post_id'    => $post->ID,
									'post_title' => $post->post_title,
									'image_url'  => $img_url,
									'file_size'  => size_format( $file_size ),
								);
							}

							// Check image format.
							$mime_type = get_post_mime_type( $attachment_id );
							if ( 'image/webp' === $mime_type || 'image/avif' === $mime_type ) {
								++$webp_images;
							} elseif ( in_array( $mime_type, array( 'image/jpeg', 'image/png', 'image/gif' ), true ) ) {
								++$traditional_images;
							}
						}
					}
				}
			}
		}

		// No images found - nothing to report.
		if ( 0 === $total_images ) {
			return null;
		}

		// Calculate optimization metrics.
		$optimization_percentage = 100;
		if ( $total_images > 0 ) {
			$optimization_percentage = ( ( $total_images - $unoptimized_images ) / $total_images ) * 100;
		}

		// Check if modern format adoption is low.
		$modern_format_adoption = 0;
		$total_counted_images   = $webp_images + $traditional_images;
		if ( $total_counted_images > 0 ) {
			$modern_format_adoption = ( $webp_images / $total_counted_images ) * 100;
		}

		// Determine if there's an issue to report.
		$has_large_images    = count( $large_images ) > 0;
		$low_modern_adoption = $modern_format_adoption < 25 && $total_counted_images > 5;
		$low_optimization    = $optimization_percentage < 75;

		// If images are well optimized and using modern formats, return null (no issues).
		if ( ! $has_large_images && ! $low_modern_adoption && ! $low_optimization ) {
			return null;
		}

		// Build description based on findings.
		$description_parts = array();

		if ( $has_large_images ) {
			$description_parts[] = sprintf(
				/* translators: %d: number of large images */
				_n(
					'%d image exceeds 5MB and should be compressed',
					'%d images exceed 5MB and should be compressed',
					count( $large_images ),
					'wpshadow'
				),
				count( $large_images )
			);
		}

		if ( $low_modern_adoption ) {
			$description_parts[] = sprintf(
				/* translators: %d: percentage of modern format adoption */
				__( 'Only %.0f%% of images use modern formats (WebP/AVIF). Modern formats reduce file sizes by 25-35%% without quality loss', 'wpshadow' ),
				$modern_format_adoption
			);
		}

		if ( $low_optimization ) {
			$description_parts[] = sprintf(
				/* translators: %d: optimization percentage */
				__( 'Overall optimization score: %.0f%%. Consider using an image optimization plugin', 'wpshadow' ),
				$optimization_percentage
			);
		}

		$description = implode( '. ', $description_parts ) . '.';

		// Determine severity based on number of issues.
		$severity     = 'low';
		$threat_level = 25;

		if ( count( $large_images ) > 3 || $optimization_percentage < 50 ) {
			$severity     = 'medium';
			$threat_level = 40;
		}

		return array(
			'id'                      => self::$slug,
			'title'                   => __( 'Images Need Optimization', 'wpshadow' ),
			'description'             => $description,
			'severity'                => $severity,
			'threat_level'            => $threat_level,
			'category'                => 'content_publishing',
			'kb_link'                 => 'https://wpshadow.com/kb/pub-images-optimized',
			'training_link'           => 'https://wpshadow.com/training/category-content-publishing',
			'auto_fixable'            => false,
			'total_images'            => $total_images,
			'unoptimized_images'      => $unoptimized_images,
			'optimization_percentage' => round( $optimization_percentage, 1 ),
			'modern_format_adoption'  => round( $modern_format_adoption, 1 ),
			'large_images'            => $large_images,
			'webp_images'             => $webp_images,
			'traditional_images'      => $traditional_images,
		);
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Published Images Optimized
	 * Slug: pub-images-optimized
	 *
	 * Test Purpose:
	 * - Verify that check() method returns the correct result based on site state
	 * - PASS: check() returns NULL when images are optimized (site is healthy)
	 * - FAIL: check() returns array when unoptimized images found (issue detected)
	 *
	 * @since  1.2601.2148
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live_pub_images_optimized(): array {
		$result = self::check();

		// If result is null, site is healthy (images are optimized).
		if ( is_null( $result ) ) {
			return array(
				'passed'  => true,
				'message' => '✓ Published images are well optimized. No issues detected.',
			);
		}

		// Result is an array - issues were found.
		$message = sprintf(
			'✗ Image optimization issues detected: %s',
			isset( $result['description'] ) ? $result['description'] : 'Unknown issues'
		);

		// Include metrics if available.
		if ( isset( $result['optimization_percentage'] ) ) {
			$message .= sprintf(
				' (Optimization: %.1f%%, Modern formats: %.1f%%)',
				$result['optimization_percentage'],
				isset( $result['modern_format_adoption'] ) ? $result['modern_format_adoption'] : 0
			);
		}

		return array(
			'passed'  => false,
			'message' => $message,
		);
	}
}
