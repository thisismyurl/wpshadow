<?php
/**
 * Extra Image Sizes Trimmed Diagnostic
 *
 * Checks whether the number of registered image sizes is excessive, which
 * causes WordPress to generate many file variants on each upload.
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
 * Diagnostic_Extra_Image_Sizes_Trimmed Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_Extra_Image_Sizes_Trimmed extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'extra-image-sizes-trimmed';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Extra Image Sizes Trimmed';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether too many image sizes are registered, causing WordPress to generate excessive file variants on every image upload and waste disk space on unused crops.';

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
	 * Counts registered additional image sizes and flags when an excessive
	 * number are registered, increasing disk usage on every upload.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array when too many image sizes are registered, null when healthy.
	 */
	public static function check() {
		$additional = WP_Settings::get_additional_image_sizes();
		$count      = count( $additional );

		// Up to 5 registered extra sizes is considered reasonable.
		if ( $count <= 5 ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: number of extra image sizes */
				__( '%d additional image sizes are registered. Each registered size causes WordPress to generate a new image file for every media upload. Excessive sizes bloat your uploads directory and slow down media uploads. Review and unregister any sizes that are not actively used in your theme or plugins.', 'wpshadow' ),
				$count
			),
			'severity'     => 'low',
			'threat_level' => 20,
			'kb_link'      => 'https://wpshadow.com/kb/extra-image-sizes?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'      => array(
				'registered_size_count' => $count,
				'registered_sizes'      => array_keys( $additional ),
			),
		);
	}
}
