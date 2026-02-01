<?php
/**
 * Image Dimension Accuracy Diagnostic
 *
 * Validates that actual generated images match configured dimensions
 * to ensure image sizes are generated correctly.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26032.0912
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Image Dimension Accuracy Diagnostic Class
 *
 * Checks if generated image sizes match configured dimensions.
 * Detects mismatches that could cause incorrect image display
 * or unnecessary storage usage.
 *
 * @since 1.26032.0912
 */
class Diagnostic_Image_Dimension_Accuracy extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'image-dimension-accuracy';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Image Dimension Accuracy';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates actual generated images match configured dimensions';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Maximum number of images to sample
	 *
	 * @var int
	 */
	private const SAMPLE_SIZE = 20;

	/**
	 * Tolerance in pixels for dimension matching
	 *
	 * @var int
	 */
	private const DIMENSION_TOLERANCE = 1;

	/**
	 * Run the diagnostic check.
	 *
	 * Compares registered image sizes against actual generated image dimensions
	 * to detect configuration mismatches or generation failures.
	 *
	 * @since  1.26032.0912
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Get all registered image sizes with their dimensions.
		$registered_sizes = wp_get_registered_image_subsizes();

		if ( empty( $registered_sizes ) ) {
			// No custom image sizes configured, nothing to check.
			return null;
		}

		// Get a sample of recently uploaded images.
		$images = self::get_sample_images();

		if ( empty( $images ) ) {
			// No images to check.
			return null;
		}

		$mismatches = array();
		$total_checked = 0;
		$sizes_with_issues = array();

		// Check each image for dimension mismatches.
		foreach ( $images as $image_id ) {
			$metadata = wp_get_attachment_metadata( $image_id );

			if ( empty( $metadata ) || empty( $metadata['sizes'] ) ) {
				continue;
			}

			$total_checked++;

			// Check each generated size against configured dimensions.
			foreach ( $metadata['sizes'] as $size_name => $size_data ) {
				// Skip if this size is no longer registered.
				if ( ! isset( $registered_sizes[ $size_name ] ) ) {
					continue;
				}

				$expected = $registered_sizes[ $size_name ];
				$actual_width = $size_data['width'];
				$actual_height = $size_data['height'];
				$expected_width = $expected['width'];
				$expected_height = $expected['height'];
				$expected_crop = $expected['crop'] ?? false;

				// Check if dimensions match within tolerance.
				$width_matches = self::dimensions_match( $actual_width, $expected_width );
				$height_matches = self::dimensions_match( $actual_height, $expected_height );

				// For cropped images, dimensions should match exactly.
				// For uncropped (proportionally scaled), at least one dimension should match.
				if ( $expected_crop ) {
					// Cropped images should match both dimensions (within tolerance).
					if ( ! $width_matches || ! $height_matches ) {
						$mismatch_key = $size_name;
						if ( ! isset( $mismatches[ $mismatch_key ] ) ) {
							$mismatches[ $mismatch_key ] = array(
								'size_name'       => $size_name,
								'expected_width'  => $expected_width,
								'expected_height' => $expected_height,
								'crop'            => $expected_crop,
								'examples'        => array(),
							);
						}

						$mismatches[ $mismatch_key ]['examples'][] = array(
							'image_id'      => $image_id,
							'actual_width'  => $actual_width,
							'actual_height' => $actual_height,
						);

						$sizes_with_issues[ $size_name ] = true;
					}
				} else {
					// Uncropped images: check if it's properly scaled.
					// For uncropped, if source is smaller, it won't be upscaled.
					// At least one dimension should be close to expected, unless source is smaller.
					$source_width = $metadata['width'] ?? 0;
					$source_height = $metadata['height'] ?? 0;

					// Skip if source is smaller than expected (WordPress won't upscale).
					if ( $source_width < $expected_width && $source_height < $expected_height ) {
						continue;
					}

					// For uncropped, at least one dimension should match the target.
					$has_dimension_match = $width_matches || $height_matches;

					if ( ! $has_dimension_match ) {
						$mismatch_key = $size_name;
						if ( ! isset( $mismatches[ $mismatch_key ] ) ) {
							$mismatches[ $mismatch_key ] = array(
								'size_name'       => $size_name,
								'expected_width'  => $expected_width,
								'expected_height' => $expected_height,
								'crop'            => $expected_crop,
								'examples'        => array(),
							);
						}

						$mismatches[ $mismatch_key ]['examples'][] = array(
							'image_id'      => $image_id,
							'actual_width'  => $actual_width,
							'actual_height' => $actual_height,
						);

						$sizes_with_issues[ $size_name ] = true;
					}
				}
			}
		}

		// Filter out size mismatches that only have a few examples (might be edge cases).
		$significant_mismatches = array_filter(
			$mismatches,
			function( $mismatch ) {
				return count( $mismatch['examples'] ) >= 2;
			}
		);

		if ( empty( $significant_mismatches ) ) {
			// No significant issues found.
			return null;
		}

		// Calculate severity based on number of affected sizes.
		$num_affected_sizes = count( $sizes_with_issues );
		$num_total_sizes = count( $registered_sizes );
		$percentage_affected = ( $num_affected_sizes / $num_total_sizes ) * 100;

		$severity = 'low';
		$threat_level = 30;

		if ( $percentage_affected > 50 ) {
			$severity = 'high';
			$threat_level = 60;
		} elseif ( $percentage_affected > 25 ) {
			$severity = 'medium';
			$threat_level = 45;
		}

		// Limit examples for each size to avoid bloat.
		foreach ( $significant_mismatches as &$mismatch ) {
			$mismatch['examples'] = array_slice( $mismatch['examples'], 0, 3 );
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: number of image sizes with dimension mismatches */
				_n(
					'%d image size has dimension mismatches between configuration and generated images.',
					'%d image sizes have dimension mismatches between configuration and generated images.',
					$num_affected_sizes,
					'wpshadow'
				),
				$num_affected_sizes
			),
			'severity'     => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'family'       => self::$family,
			'kb_link'      => 'https://wpshadow.com/kb/image-dimension-accuracy',
			'meta'         => array(
				'total_images_checked'    => $total_checked,
				'total_registered_sizes'  => $num_total_sizes,
				'sizes_with_issues'       => $num_affected_sizes,
				'percentage_affected'     => round( $percentage_affected, 1 ),
			),
			'details'      => array(
				'mismatches'     => array_values( $significant_mismatches ),
				'recommendation' => __( 'Regenerate thumbnails to match current image size configuration, or adjust image size settings to match your needs.', 'wpshadow' ),
			),
		);
	}

	/**
	 * Get a sample of recent images to check.
	 *
	 * @since  1.26032.0912
	 * @return array Array of attachment IDs.
	 */
	private static function get_sample_images(): array {
		global $wpdb;

		$image_ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT ID FROM {$wpdb->posts}
				WHERE post_type = 'attachment'
				AND post_mime_type LIKE %s
				ORDER BY post_date DESC
				LIMIT %d",
				'image/%',
				self::SAMPLE_SIZE
			)
		);

		return array_map( 'intval', $image_ids );
	}

	/**
	 * Check if two dimensions match within tolerance.
	 *
	 * @since  1.26032.0912
	 * @param  int $actual   Actual dimension in pixels.
	 * @param  int $expected Expected dimension in pixels.
	 * @return bool True if dimensions match within tolerance.
	 */
	private static function dimensions_match( int $actual, int $expected ): bool {
		return abs( $actual - $expected ) <= self::DIMENSION_TOLERANCE;
	}
}
