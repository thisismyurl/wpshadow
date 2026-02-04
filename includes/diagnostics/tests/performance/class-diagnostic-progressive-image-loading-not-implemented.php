<?php
/**
 * Progressive Image Loading Not Implemented Diagnostic
 *
 * Checks progressive image loading.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6033.2033
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Progressive_Image_Loading_Not_Implemented Class
 *
 * Performs diagnostic check for Progressive Image Loading Not Implemented.
 *
 * @since 1.6033.2033
 */
class Diagnostic_Progressive_Image_Loading_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'progressive-image-loading-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Progressive Image Loading Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks progressive image loading';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.2033
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for progressive image loading plugins.
		$progressive_plugins = array(
			'webp-express/webp-express.php'             => 'WebP Express',
			'ewww-image-optimizer/ewww-image-optimizer.php' => 'EWWW Image Optimizer',
			'shortpixel-image-optimiser/wp-shortpixel.php' => 'ShortPixel',
			'progressive-image-loading/progressive-image-loading.php' => 'Progressive Image Loading',
		);

		$plugin_detected = false;
		$plugin_name     = '';

		foreach ( $progressive_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$plugin_detected = true;
				$plugin_name     = $name;
				break;
			}
		}

		// Check for modern image formats (WebP).
		$upload_dir = wp_upload_dir();
		$has_webp = false;

		if ( is_dir( $upload_dir['basedir'] ) ) {
			// Sample check for WebP files.
			$webp_files = glob( $upload_dir['basedir'] . '/*.webp' );
			$has_webp = ! empty( $webp_files );
		}

		// Check for blur-up technique (low-quality placeholder).
		$has_blur_up = has_filter( 'the_content', 'add_blur_placeholders' );

		// Progressive JPEGs are handled by image optimization tools.
		// This check is more about placeholder strategies.

		if ( ! $plugin_detected && ! $has_blur_up ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Progressive image loading not implemented. Images load at full resolution immediately, causing blank space during loading. Implement progressive loading: show low-quality placeholder (blurred thumbnail) while high-quality image loads. Improves perceived performance by 30-50%.', 'wpshadow' ),
				'severity'    => 'low',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/progressive-image-loading',
				'details'     => array(
					'plugin_detected' => false,
					'has_webp'        => $has_webp,
					'recommendation'  => __( 'Install EWWW Image Optimizer (free, 1M+ installs) with WebP support. EWWW creates progressive JPEGs automatically. For blur-up effect: install a Progressive Image Loading plugin or implement Low Quality Image Placeholder (LQIP) technique.', 'wpshadow' ),
					'techniques'      => array(
						'progressive_jpeg' => 'Image renders top-to-bottom progressively (vs all-at-once)',
						'blur_up' => 'Show tiny blurred version first, swap to full quality when loaded',
						'lqip' => 'Low Quality Image Placeholder: 20x20px thumbnail stretched and blurred',
						'webp' => 'Modern format with better compression and progressive support',
					),
					'user_experience' => array(
						'without' => 'White space, then image pops in (jarring)',
						'with' => 'Blurred preview immediately, smoothly sharpens (elegant)',
						'perceived_speed' => '30-50% faster perceived loading',
					),
					'implementation' => array(
						'easiest' => 'Install EWWW Image Optimizer, enable progressive JPEGs',
						'advanced' => 'Custom blur-up with CSS and JavaScript',
						'modern' => 'Native lazy loading + WebP format',
					),
				),
			);
		}

		// No issues - progressive loading implemented.
		return null;
	}
}
