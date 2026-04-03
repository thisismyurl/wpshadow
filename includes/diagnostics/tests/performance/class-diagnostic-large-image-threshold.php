<?php
/**
 * Large Image Threshold Diagnostic
 *
 * Checks whether the big-image-size threshold is disabled, which would
 * allow full-resolution camera/phone uploads to be stored and served.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Diagnostics\Helpers\Diagnostic_WP_Settings_Helper as WP_Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Large_Image_Threshold Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_Large_Image_Threshold extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'large-image-threshold';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Media Scaling Threshold';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether the WordPress big-image-size threshold is disabled, which causes full-resolution uploads to be stored and potentially served without downscaling.';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Confidence level of this diagnostic.
	 *
	 * @var string
	 */
	protected static $confidence = 'standard';

	/**
	 * Run the diagnostic check.
	 *
	 * Reads the big_image_size_threshold filter value and flags when the
	 * feature has been disabled, allowing very large originals to be stored.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array when the threshold is disabled, null when healthy.
	 */
	public static function check() {
		$threshold = WP_Settings::get_big_image_size_threshold();

		// Threshold > 0 = WordPress will scale down very large images on upload. Healthy.
		if ( $threshold > 0 ) {
			return null;
		}

		// 0 means the big image size feature is disabled.
		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'The big image size threshold is disabled (set to 0). WordPress normally scales down uploaded images larger than 2560px to prevent oversized originals from being served. With this disabled, multi-megapixel photos from cameras and phones will be stored and potentially served at their full size, increasing bandwidth use and slowing page loads.', 'wpshadow' ),
			'severity'     => 'low',
			'threat_level' => 20,
			'kb_link'      => 'https://wpshadow.com/kb/large-image-threshold?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'      => array(
				'threshold_px'    => $threshold,
				'recommended_px'  => 2560,
			),
		);
	}
}
