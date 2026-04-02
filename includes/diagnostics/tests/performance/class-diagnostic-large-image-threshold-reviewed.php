<?php
/**
 * Media Scaling Threshold Reviewed Diagnostic (Stub)
 *
 * TODO stub mapped to the performance gauge.
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
 * Diagnostic_Large_Image_Threshold_Reviewed Class
 *
 * TODO: Implement full test logic and remediation guidance.
 */
class Diagnostic_Large_Image_Threshold_Reviewed extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'large-image-threshold-reviewed';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Media Scaling Threshold Reviewed';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'TODO: Implement diagnostic logic for Media Scaling Threshold Reviewed';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * TODO Test Plan:
	 * - Check big_image_size_threshold filter behavior or defaults where large uploads are common.
	 *
	 * TODO Fix Plan:
	 * - Set an image scaling policy that avoids oversized originals and editor frustration.
	 * - Use WordPress hooks, filters, settings, DB fixes, PHP config, or accessible server settings.
	 * - Do not modify WordPress core files.
	 * - Ensure performance/security/success impact and align with WPShadow commandments.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array if issue exists, null if healthy.
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
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/large-image-threshold',
			'details'      => array(
				'threshold_px'    => $threshold,
				'recommended_px'  => 2560,
			),
		);
	}
}
