<?php
/**
 * Extra Image Sizes Trimmed Diagnostic (Stub)
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
 * Diagnostic_Extra_Image_Sizes_Trimmed Class
 *
 * TODO: Implement full test logic and remediation guidance.
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
	protected static $description = 'TODO: Implement diagnostic logic for Extra Image Sizes Trimmed';

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
	 * - Inspect intermediate_image_sizes().
	 *
	 * TODO Fix Plan:
	 * - Disable unnecessary generated sizes.
	 * - Use WordPress hooks, filters, settings, DB fixes, PHP config, or accessible server settings.
	 * - Do not modify WordPress core files.
	 * - Ensure performance/security/success impact and align with WPShadow commandments.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array if issue exists, null if healthy.
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
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/extra-image-sizes',
			'details'      => array(
				'registered_size_count' => $count,
				'registered_sizes'      => array_keys( $additional ),
			),
		);
	}
}
