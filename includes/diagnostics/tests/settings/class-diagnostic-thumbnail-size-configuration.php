<?php
/**
 * Thumbnail Size Configuration Diagnostic
 *
 * Validates thumbnail dimensions are appropriate and optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.1912
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Thumbnail Size Configuration Diagnostic Class
 *
 * Checks if thumbnail sizes are properly configured and optimized.
 *
 * @since 1.2601.1912
 */
class Diagnostic_Thumbnail_Size_Configuration extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'thumbnail-size-configuration';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Thumbnail Size Configuration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates thumbnail dimensions are appropriate';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'settings';

	/**
	 * The family label
	 *
	 * @var string
	 */
	protected static $family_label = 'Settings';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.1912
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Get thumbnail dimensions.
		$thumbnail_width  = (int) get_option( 'thumbnail_size_w', 150 );
		$thumbnail_height = (int) get_option( 'thumbnail_size_h', 150 );
		$thumbnail_crop   = get_option( 'thumbnail_crop', '1' );

		// Check if thumbnails are disabled (width or height is 0).
		if ( 0 === $thumbnail_width || 0 === $thumbnail_height ) {
			$issues[] = __( 'Thumbnail generation is disabled (width or height is 0)', 'wpshadow' );
		}

		// Check for oversized thumbnails (larger than 300x300).
		if ( $thumbnail_width > 300 || $thumbnail_height > 300 ) {
			$issues[] = sprintf(
				/* translators: 1: width, 2: height */
				__( 'Thumbnails are oversized (%1$dx%2$d) - may waste storage space', 'wpshadow' ),
				$thumbnail_width,
				$thumbnail_height
			);
		}

		// Check for undersized thumbnails (smaller than 100x100).
		if ( ( $thumbnail_width > 0 && $thumbnail_width < 100 ) || ( $thumbnail_height > 0 && $thumbnail_height < 100 ) ) {
			$issues[] = sprintf(
				/* translators: 1: width, 2: height */
				__( 'Thumbnails are undersized (%1$dx%2$d) - may result in quality loss', 'wpshadow' ),
				$thumbnail_width,
				$thumbnail_height
			);
		}

		// Check for non-square thumbnails when crop is enabled.
		if ( '1' === $thumbnail_crop && $thumbnail_width !== $thumbnail_height ) {
			$issues[] = sprintf(
				/* translators: 1: width, 2: height */
				__( 'Crop is enabled but dimensions are not square (%1$dx%2$d) - thumbnails will be cropped unevenly', 'wpshadow' ),
				$thumbnail_width,
				$thumbnail_height
			);
		}

		// Check for unusual aspect ratios (not 1:1, 4:3, 16:9, or 3:2).
		if ( $thumbnail_width > 0 && $thumbnail_height > 0 ) {
			$ratio = $thumbnail_width / $thumbnail_height;
			// Common ratios: 1:1 (1.0), 4:3 (1.33), 3:2 (1.5), 16:9 (1.78).
			$common_ratios = array( 1.0, 1.33, 1.5, 1.78, 0.75, 0.67, 0.56 ); // Include inverses.
			$is_common     = false;
			foreach ( $common_ratios as $common_ratio ) {
				if ( abs( $ratio - $common_ratio ) < 0.1 ) {
					$is_common = true;
					break;
				}
			}
			if ( ! $is_common ) {
				$issues[] = sprintf(
					/* translators: 1: width, 2: height, 3: ratio */
					__( 'Unusual thumbnail aspect ratio (%1$dx%2$d, ratio: %3$.2f) - may not display well', 'wpshadow' ),
					$thumbnail_width,
					$thumbnail_height,
					$ratio
				);
			}
		}

		// Check medium and large sizes for consistency.
		$medium_width = (int) get_option( 'medium_size_w', 300 );
		$large_width  = (int) get_option( 'large_size_w', 1024 );

		if ( $thumbnail_width > $medium_width && $medium_width > 0 ) {
			$issues[] = sprintf(
				/* translators: 1: thumbnail width, 2: medium width */
				__( 'Thumbnail width (%1$d) is larger than medium size (%2$d) - size hierarchy is inverted', 'wpshadow' ),
				$thumbnail_width,
				$medium_width
			);
		}

		if ( $thumbnail_width > $large_width && $large_width > 0 ) {
			$issues[] = sprintf(
				/* translators: 1: thumbnail width, 2: large width */
				__( 'Thumbnail width (%1$d) is larger than large size (%2$d) - size hierarchy is inverted', 'wpshadow' ),
				$thumbnail_width,
				$large_width
			);
		}

		// If no issues found, return null.
		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'                 => self::$slug,
			'title'              => self::$title,
			'description'        => sprintf(
				/* translators: %d: number of issues */
				__( 'Found %d thumbnail configuration issues', 'wpshadow' ),
				count( $issues )
			),
			'severity'           => 'low',
			'threat_level'       => 45,
			'site_health_status' => 'recommended',
			'auto_fixable'       => false,
			'kb_link'            => 'https://wpshadow.com/kb/thumbnail-size-configuration',
			'family'             => self::$family,
			'details'            => array(
				'issues'           => $issues,
				'thumbnail_width'  => $thumbnail_width,
				'thumbnail_height' => $thumbnail_height,
				'thumbnail_crop'   => $thumbnail_crop,
				'medium_width'     => $medium_width,
				'large_width'      => $large_width,
			),
		);
	}
}
