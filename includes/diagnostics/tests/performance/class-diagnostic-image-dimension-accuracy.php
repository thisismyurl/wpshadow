<?php
/**
 * Image Dimension Accuracy Diagnostic
 *
 * Verifies uploaded images have accurate dimension metadata to prevent
 * layout shifts and improve page performance.
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
 * Image Dimension Accuracy Diagnostic Class
 *
 * Checks that image attachments have correct width/height metadata.
 *
 * @since 1.6032.1745
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
	protected static $description = 'Verifies image metadata accuracy';

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
		global $wpdb;

		$issues = array();

		// Sample recent images to check metadata.
		$recent_images = $wpdb->get_results(
			"SELECT ID, post_mime_type FROM {$wpdb->posts}
			WHERE post_type = 'attachment'
			AND post_mime_type LIKE 'image/%'
			ORDER BY post_date DESC
			LIMIT 50"
		);

		if ( empty( $recent_images ) ) {
			return null; // No images to check.
		}

		$missing_dimensions = 0;
		$incorrect_dimensions = 0;

		foreach ( $recent_images as $image ) {
			$metadata = wp_get_attachment_metadata( $image->ID );

			// Check if dimensions are set.
			if ( empty( $metadata['width'] ) || empty( $metadata['height'] ) ) {
				++$missing_dimensions;
				continue;
			}

			// Verify dimensions match actual file (sample check).
			$file_path = get_attached_file( $image->ID );
			if ( file_exists( $file_path ) ) {
				$image_size = getimagesize( $file_path );
				if ( $image_size ) {
					if ( $metadata['width'] !== $image_size[0] || $metadata['height'] !== $image_size[1] ) {
						++$incorrect_dimensions;
					}
				}
			}

			// Limit checks to avoid performance issues.
			if ( $missing_dimensions + $incorrect_dimensions >= 10 ) {
				break;
			}
		}

		if ( $missing_dimensions > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of images */
				_n(
					'%d image is missing dimension metadata',
					'%d images are missing dimension metadata',
					$missing_dimensions,
					'wpshadow'
				),
				$missing_dimensions
			);
		}

		if ( $incorrect_dimensions > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of images */
				_n(
					'%d image has incorrect dimension metadata',
					'%d images have incorrect dimension metadata',
					$incorrect_dimensions,
					'wpshadow'
				),
				$incorrect_dimensions
			);
		}

		// Check for SVG images (don't have dimensions).
		$svg_count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts}
			WHERE post_type = 'attachment'
			AND post_mime_type = 'image/svg+xml'"
		);

		if ( $svg_count > 0 ) {
			// SVG support requires explicit dimensions.
			$issues[] = sprintf(
				/* translators: %d: number of SVG images */
				_n(
					'%d SVG image requires explicit width/height attributes',
					'%d SVG images require explicit width/height attributes',
					(int) $svg_count,
					'wpshadow'
				),
				number_format_i18n( (int) $svg_count )
			);
		}

		// Check for WebP image support.
		$webp_count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts}
			WHERE post_type = 'attachment'
			AND post_mime_type = 'image/webp'"
		);

		if ( $webp_count > 0 && ! function_exists( 'imagewebp' ) ) {
			$issues[] = __( 'WebP images uploaded but server lacks WebP support', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/image-dimension-accuracy',
			);
		}

		return null;
	}
}
