<?php
/**
 * Thumbnail Size Configuration Diagnostic
 *
 * Optimizes thumbnail dimension settings. Checks for storage waste or quality loss.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2602.0100
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
 * Validates that WordPress image size settings are optimized.
 * Checks for default sizes that may waste storage or reduce quality.
 *
 * @since 1.2602.0100
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
	protected static $description = 'Optimizes thumbnail dimension settings';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2602.0100
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Get thumbnail sizes.
		$thumbnail_w = (int) get_option( 'thumbnail_size_w', 150 );
		$thumbnail_h = (int) get_option( 'thumbnail_size_h', 150 );
		$medium_w    = (int) get_option( 'medium_size_w', 300 );
		$medium_h    = (int) get_option( 'medium_size_h', 300 );
		$large_w     = (int) get_option( 'large_size_w', 1024 );
		$large_h     = (int) get_option( 'large_size_h', 1024 );

		$issues  = array();
		$details = array(
			'thumbnail_size' => "{$thumbnail_w}x{$thumbnail_h}",
			'medium_size'    => "{$medium_w}x{$medium_h}",
			'large_size'     => "{$large_w}x{$large_h}",
		);

		// Check if all sizes are WordPress defaults.
		$using_all_defaults = ( 150 === $thumbnail_w && 150 === $thumbnail_h )
							&& ( 300 === $medium_w && 300 === $medium_h )
							&& ( 1024 === $large_w && 1024 === $large_h );

		if ( $using_all_defaults ) {
			$issues[] = __( 'All image sizes are set to WordPress defaults. Consider customizing them to match your theme requirements for better performance and quality.', 'wpshadow' );
		}

		// Check if thumbnail is too small.
		if ( $thumbnail_w < 100 || $thumbnail_h < 100 ) {
			$issues[] = sprintf(
				/* translators: %s: Current thumbnail size */
				__( 'Thumbnail size (%s) is very small. This may result in pixelated images on high-resolution displays.', 'wpshadow' ),
				"{$thumbnail_w}x{$thumbnail_h}"
			);
		}

		// Check if thumbnail is too large.
		if ( $thumbnail_w > 300 || $thumbnail_h > 300 ) {
			$issues[] = sprintf(
				/* translators: %s: Current thumbnail size */
				__( 'Thumbnail size (%s) is quite large. Smaller thumbnails load faster and save storage space.', 'wpshadow' ),
				"{$thumbnail_w}x{$thumbnail_h}"
			);
		}

		// Check if medium is too similar to thumbnail.
		if ( abs( $medium_w - $thumbnail_w ) < 100 && abs( $medium_h - $thumbnail_h ) < 100 ) {
			$issues[] = __( 'Medium and thumbnail sizes are very similar. Consider adjusting for more size variety.', 'wpshadow' );
		}

		// Check if large size is excessively large.
		if ( $large_w > 2048 || $large_h > 2048 ) {
			$issues[] = sprintf(
				/* translators: %s: Current large size */
				__( 'Large image size (%s) is excessive for web display. Consider reducing to 1920px or less to save bandwidth and storage.', 'wpshadow' ),
				"{$large_w}x{$large_h}"
			);
		}

		// Check if large size is too small for modern displays.
		if ( $large_w < 800 && $large_h < 800 ) {
			$issues[] = sprintf(
				/* translators: %s: Current large size */
				__( 'Large image size (%s) may be too small for modern high-resolution displays. Consider increasing to at least 1200px.', 'wpshadow' ),
				"{$large_w}x{$large_h}"
			);
		}

		// Get theme support for additional sizes.
		$additional_sizes            = get_intermediate_image_sizes();
		$details['additional_sizes'] = count( $additional_sizes );

		// Check registered image sizes.
		global $_wp_additional_image_sizes;
		if ( ! empty( $_wp_additional_image_sizes ) ) {
			$custom_sizes = array();
			foreach ( $_wp_additional_image_sizes as $size_name => $size_data ) {
				$custom_sizes[ $size_name ] = array(
					'width'  => $size_data['width'],
					'height' => $size_data['height'],
					'crop'   => $size_data['crop'],
				);
			}
			$details['custom_sizes'] = $custom_sizes;

			// Check for very large custom sizes.
			foreach ( $custom_sizes as $size_name => $size_data ) {
				if ( $size_data['width'] > 2048 || $size_data['height'] > 2048 ) {
					$issues[] = sprintf(
						/* translators: 1: Size name, 2: Dimensions */
						__( 'Custom image size "%1$s" (%2$dx%3$d) is very large. This increases storage requirements and upload time.', 'wpshadow' ),
						$size_name,
						$size_data['width'],
						$size_data['height']
					);
				}
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'                 => self::$slug,
				'title'              => self::$title,
				'description'        => __( 'Image size configuration could be optimized for better performance, quality, or storage efficiency.', 'wpshadow' ),
				'severity'           => 'low',
				'threat_level'       => 25,
				'site_health_status' => 'good',
				'auto_fixable'       => false,
				'kb_link'            => 'https://wpshadow.com/kb/performance-thumbnail-size-configuration',
				'family'             => self::$family,
				'details'            => array(
					'issues' => $issues,
					'info'   => $details,
				),
			);
		}

		return null;
	}
}
