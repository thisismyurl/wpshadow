<?php
/**
 * No Image Optimization Diagnostic
 *
 * Detects when images are not being optimized, causing slower
 * page loads and poor user experience.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Performance
 * @since      1.6035.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: No Image Optimization
 *
 * Checks whether images are being optimized for web delivery
 * to improve page load times and performance.
 *
 * @since 1.6035.2148
 */
class Diagnostic_No_Image_Optimization extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-image-optimization';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'No Image Optimization';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether images are being optimized';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Whether this diagnostic is auto-fixable
	 *
	 * @var bool
	 */
	protected static $auto_fixable = false;

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6035.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for image optimization plugins
		$has_image_optimization = is_plugin_active( 'wp-smushit/wp-smush.php' ) ||
			is_plugin_active( 'imagify/imagify.php' ) ||
			is_plugin_active( 'shortpixel-image-optimiser/shortpixel-image-optimiser.php' ) ||
			is_plugin_active( 'ewww-image-optimizer/ewww-image-optimizer.php' );

		// Check for custom optimization
		$has_custom_optimization = get_option( 'wpshadow_image_optimization_enabled' );

		if ( ! $has_image_optimization && ! $has_custom_optimization ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'Your images aren\'t optimized, which means they\'re probably much larger than necessary. Image files often make up 50-80% of a page\'s weight. Unoptimized images slow down your site by 2-3x. Optimization means: compressing file size without visible quality loss, using modern formats (WebP instead of PNG), and serving responsive images (different sizes for different devices). This is one of the highest-ROI performance improvements.',
					'wpshadow'
				),
				'severity'      => 'medium',
				'threat_level'  => 55,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'Page Load Speed',
					'potential_gain' => '50-80% smaller image file sizes',
					'roi_explanation' => 'Image optimization typically improves load time by 2-3x, directly improving SEO rankings and conversion rates (7% conversion loss per second delay).',
				),
				'kb_link'       => 'https://wpshadow.com/kb/image-optimization',
			);
		}

		return null;
	}
}
