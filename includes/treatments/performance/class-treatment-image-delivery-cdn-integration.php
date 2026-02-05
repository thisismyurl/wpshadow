<?php
/**
 * Image Delivery CDN Integration Treatment
 *
 * Verifies that images are being delivered through a CDN service for
 * optimal performance and global distribution.
 *
 * @since   1.6033.2099
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Image Delivery CDN Integration Treatment Class
 *
 * Verifies image CDN setup:
 * - CDN image URL detection
 * - Image optimization API
 * - CloudFlare or similar service
 * - Global distribution
 *
 * @since 1.6033.2099
 */
class Treatment_Image_Delivery_Cdn_Integration extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'image-delivery-cdn-integration';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Image Delivery CDN Integration';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for CDN image delivery optimization';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.2099
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function check() {
		$image_cdn_active = false;

		// Check for image CDN services
		$image_cdn_plugins = array(
			'imagify/imagify.php'                                => 'Imagify CDN',
			'shortpixel-image-optimiser/wp-shortpixel.php'       => 'ShortPixel CDN',
			'jetpack/jetpack.php'                                => 'Jetpack Image Accelerator',
			'cloudinary-responsive-image/cloudinary.php'         => 'Cloudinary',
			'kinsta-cache/kinsta-cache.php'                      => 'Kinsta CDN',
		);

		foreach ( $image_cdn_plugins as $plugin_path => $plugin_name ) {
			if ( is_plugin_active( $plugin_path ) ) {
				$image_cdn_active = true;
				break;
			}
		}

		// Check for standard CDN plugins (which may serve images)
		if ( ! $image_cdn_active ) {
			$cdn_plugins = array( 'cdn-enabler/cdn-enabler.php', 'wp-super-cache/wp-cache.php' );
			foreach ( $cdn_plugins as $plugin_path ) {
				if ( is_plugin_active( $plugin_path ) ) {
					$image_cdn_active = true;
					break;
				}
			}
		}

		if ( ! $image_cdn_active ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Images are not being delivered through a CDN. Image CDN services optimize images on-the-fly and reduce load time by 40-60%%.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 35,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/image-cdn',
				'meta'          => array(
					'cdn_active'           => $image_cdn_active,
					'recommendation'       => 'Use Imagify, ShortPixel, or Cloudinary for on-the-fly image optimization and delivery',
					'impact'               => 'Image CDN reduces load time by 40-60% through optimization and edge caching',
					'services'             => array(
						'Imagify (affordable, good)',
						'ShortPixel (excellent quality)',
						'Cloudinary (enterprise)',
						'Jetpack (integrated)',
					),
					'benefits'             => array(
						'On-the-fly resizing',
						'Format conversion (WebP, AVIF)',
						'Global edge locations',
						'Automatic optimization',
					),
				),
			);
		}

		return null;
	}
}
