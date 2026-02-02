<?php
/**
 * Media Mobile Bandwidth Optimization Diagnostic
 *
 * Checks if media delivery is optimized for mobile bandwidth constraints.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26033.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Media Mobile Bandwidth Optimization Diagnostic Class
 *
 * Verifies that media is optimized for mobile devices with bandwidth constraints,
 * including lazy loading, adaptive images, and compression.
 *
 * @since 1.26033.0000
 */
class Diagnostic_Media_Mobile_Bandwidth_Optimization extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-mobile-bandwidth-optimization';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Media Mobile Bandwidth Optimization';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if media delivery is optimized for mobile bandwidth constraints';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26033.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check for lazy loading support.
		$lazy_loading = wp_lazy_loading_enabled( 'img', 'wp_get_attachment_image' );
		if ( ! $lazy_loading ) {
			$issues[] = __( 'Lazy loading is not enabled for images', 'wpshadow' );
		}

		// Check for WebP support.
		if ( ! function_exists( 'wp_get_webp_info' ) ) {
			$issues[] = __( 'WebP image format support is not available', 'wpshadow' );
		}

		// Check for image optimization plugins.
		$optimization_plugins = array(
			'imagify/imagify.php',
			'shortpixel-image-optimiser/wp-shortpixel.php',
			'ewww-image-optimizer/ewww-image-optimizer.php',
			'wp-smushit/wp-smush.php',
			'optimus/optimus.php',
		);

		$has_optimizer = false;
		foreach ( $optimization_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_optimizer = true;
				break;
			}
		}

		if ( ! $has_optimizer ) {
			$issues[] = __( 'No image optimization plugin detected', 'wpshadow' );
		}

		// Check for responsive images (srcset).
		$sample_attachment = get_posts(
			array(
				'post_type'      => 'attachment',
				'post_mime_type' => 'image',
				'posts_per_page' => 1,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		if ( ! empty( $sample_attachment ) ) {
			$image_html = wp_get_attachment_image( $sample_attachment[0]->ID, 'large' );
			if ( strpos( $image_html, 'srcset' ) === false ) {
				$issues[] = __( 'Responsive images (srcset) are not being generated', 'wpshadow' );
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/media-mobile-bandwidth-optimization',
			);
		}

		return null;
	}
}
