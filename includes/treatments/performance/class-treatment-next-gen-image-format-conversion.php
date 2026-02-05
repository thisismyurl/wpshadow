<?php
/**
 * Next-Gen Image Format Conversion Treatment
 *
 * Checks if images are being converted to next-generation formats (AVIF, WebP)
 * to maximize compression and file size reduction.
 *
 * @since   1.6033.2098
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Next-Gen Image Format Conversion Treatment Class
 *
 * Verifies next-gen image conversion:
 * - AVIF conversion availability
 * - WebP conversion status
 * - Format plugin detection
 * - Automatic conversion
 *
 * @since 1.6033.2098
 */
class Treatment_Next_Gen_Image_Format_Conversion extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'next-gen-image-format-conversion';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Next-Gen Image Format Conversion';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for automatic conversion to AVIF/WebP formats';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.2098
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function check() {
		$conversion_active = false;

		// Check for image optimization plugins with format conversion
		$conversion_plugins = array(
			'imagify/imagify.php'                                => 'Imagify',
			'ewww-image-optimizer/ewww-image-optimizer.php'      => 'EWWW Image Optimizer',
			'optimus/optimus.php'                                => 'Optimus',
			'shortpixel-image-optimiser/wp-shortpixel.php'       => 'ShortPixel',
		);

		foreach ( $conversion_plugins as $plugin_path => $plugin_name ) {
			if ( is_plugin_active( $plugin_path ) ) {
				$conversion_active = true;
				break;
			}
		}

		if ( ! $conversion_active ) {
			global $wpdb;

			// Count images
			$image_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'attachment' AND post_mime_type LIKE 'image/%'" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

			if ( $image_count > 20 ) {
				return array(
					'id'            => self::$slug,
					'title'         => self::$title,
					'description'   => sprintf(
						/* translators: %d: number of images */
						__( 'Found %d images without next-gen format conversion. Converting to AVIF/WebP could reduce size by 30-50%%.', 'wpshadow' ),
						$image_count
					),
					'severity'      => 'medium',
					'threat_level'  => 50,
					'auto_fixable'  => false,
					'kb_link'       => 'https://wpshadow.com/kb/next-gen-image-formats',
					'meta'          => array(
						'image_count'          => $image_count,
						'conversion_active'    => $conversion_active,
						'recommendation'       => 'Install image optimizer with AVIF/WebP support (Imagify, EWWW, or ShortPixel)',
						'impact'               => 'Next-gen formats reduce total image bytes by 30-50%',
						'comparison'           => array(
							'JPEG: 100% (baseline)',
							'WebP: 60-70% (25-30% savings)',
							'AVIF: 40-50% (50-60% savings)',
						),
					),
				);
			}
		}

		return null;
	}
}
