<?php
/**
 * Thumbnail Size Configuration Diagnostic
 *
 * Verifies thumbnail image sizes are properly configured for optimal performance
 * and consistent display across the site.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6032.1745
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
 * Checks WordPress thumbnail size settings for best practices.
 *
 * @since 1.6032.1745
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
	protected static $description = 'Verifies thumbnail sizes are optimized';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6032.1745
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Get configured thumbnail sizes.
		$thumbnail_width  = get_option( 'thumbnail_size_w', 150 );
		$thumbnail_height = get_option( 'thumbnail_size_h', 150 );
		$thumbnail_crop   = get_option( 'thumbnail_crop', 1 );

		// Check if thumbnail size is too small.
		if ( $thumbnail_width < 100 || $thumbnail_height < 100 ) {
			$issues[] = sprintf(
				/* translators: 1: width, 2: height */
				__( 'Thumbnail size is very small (%1$dx%2$d) which may impact display quality', 'wpshadow' ),
				$thumbnail_width,
				$thumbnail_height
			);
		}

		// Check if thumbnail size is unnecessarily large.
		if ( $thumbnail_width > 500 || $thumbnail_height > 500 ) {
			$issues[] = sprintf(
				/* translators: 1: width, 2: height */
				__( 'Thumbnail size is very large (%1$dx%2$d) which may impact performance', 'wpshadow' ),
				$thumbnail_width,
				$thumbnail_height
			);
		}

		// Get medium size.
		$medium_width  = get_option( 'medium_size_w', 300 );
		$medium_height = get_option( 'medium_size_h', 300 );

		// Check medium size configuration.
		if ( $medium_width < 300 || $medium_height < 300 ) {
			$issues[] = __( 'Medium image size may be too small for modern displays', 'wpshadow' );
		}

		if ( $medium_width > 1024 || $medium_height > 1024 ) {
			$issues[] = __( 'Medium image size is larger than recommended', 'wpshadow' );
		}

		// Get large size.
		$large_width  = get_option( 'large_size_w', 1024 );
		$large_height = get_option( 'large_size_h', 1024 );

		// Check large size configuration.
		if ( $large_width < 800 || $large_height < 800 ) {
			$issues[] = __( 'Large image size may be too small for high-resolution displays', 'wpshadow' );
		}

		if ( $large_width > 2048 || $large_height > 2048 ) {
			$issues[] = __( 'Large image size may generate unnecessarily large files', 'wpshadow' );
		}

		// Check for registered custom image sizes.
		global $_wp_additional_image_sizes;
		if ( ! empty( $_wp_additional_image_sizes ) && count( $_wp_additional_image_sizes ) > 10 ) {
			$issues[] = sprintf(
				/* translators: %d: number of custom sizes */
				__( 'Many custom image sizes registered (%d) which increases storage needs', 'wpshadow' ),
				count( $_wp_additional_image_sizes )
			);
		}

		// Check if all sizes have same aspect ratio.
		$aspect_ratios = array();
		$aspect_ratios['thumbnail'] = $thumbnail_width / max( $thumbnail_height, 1 );
		$aspect_ratios['medium']    = $medium_width / max( $medium_height, 1 );
		$aspect_ratios['large']     = $large_width / max( $large_height, 1 );

		$unique_ratios = array_unique( array_map( 'round', $aspect_ratios, array_fill( 0, count( $aspect_ratios ), 1 ) ) );
		if ( count( $unique_ratios ) > 1 ) {
			$issues[] = __( 'Image sizes use inconsistent aspect ratios', 'wpshadow' );
		}

		// Check crop settings.
		if ( ! $thumbnail_crop ) {
			$issues[] = __( 'Thumbnail cropping is disabled which may cause inconsistent sizing', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/thumbnail-size-configuration',
			);
		}

		return null;
	}
}
