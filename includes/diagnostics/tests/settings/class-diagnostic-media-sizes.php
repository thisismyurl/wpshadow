<?php
/**
 * Media Sizes Reviewed Diagnostic (Stub)
 *
 * TODO stub mapped to the settings gauge.
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
 * Diagnostic_Media_Sizes_Reviewed Class
 *
 * TODO: Implement full test logic and remediation guidance.
 */
class Diagnostic_Media_Sizes extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'media-sizes';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Media Sizes';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'TODO: Implement diagnostic logic for Media Sizes';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'settings';

	/**
	 * Run the diagnostic check.
	 *
	 * TODO Test Plan:
	 * - Check thumbnail, medium, and large size options for unrealistic or default-only values.
	 *
	 * TODO Fix Plan:
	 * - Set image sizes to match theme layouts and avoid unnecessary file generation.
	 * - Use WordPress hooks, filters, settings, DB fixes, PHP config, or accessible server settings.
	 * - Do not modify WordPress core files.
	 * - Ensure performance/security/success impact and align with WPShadow commandments.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array if issue exists, null if healthy.
	 */
	public static function check() {
		$thumb  = WP_Settings::get_thumbnail_size();
		$medium = WP_Settings::get_medium_size();
		$large  = WP_Settings::get_large_size();

		$wp_defaults = (
			150 === $thumb['width'] && 150 === $thumb['height'] &&
			300 === $medium['width'] && 300 === $medium['height'] &&
			1024 === $large['width'] && 1024 === $large['height']
		);

		if ( ! $wp_defaults ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Your image size settings (thumbnail, medium, large) are all still at the WordPress installation defaults. These generic sizes may not match your theme layouts, causing unnecessary image files to be generated on upload. Review and customise them for your design.', 'wpshadow' ),
			'severity'     => 'low',
			'threat_level' => 10,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/media-sizes-reviewed',
			'details'      => array(
				'thumbnail' => $thumb,
				'medium'    => $medium,
				'large'     => $large,
			),
		);
	}
}
