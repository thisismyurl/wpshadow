<?php
/**
 * Custom Image Sizes Registration Diagnostic
 *
 * Checks for excessive or redundant custom image sizes that can waste storage.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Custom Image Sizes Registration Diagnostic Class
 *
 * Identifies excessive custom image sizes that can inflate storage usage.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Custom_Image_Sizes_Registration extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'custom-image-sizes-registration';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Custom Image Sizes Registration';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks custom image sizes registered by themes and plugins.';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Maximum recommended custom image sizes.
	 *
	 * @var int
	 */
	private const MAX_RECOMMENDED_SIZES = 10;

	/**
	 * WordPress default image sizes that should be excluded from checks.
	 *
	 * @var array
	 */
	private const DEFAULT_SIZES = array(
		'thumbnail',
		'medium',
		'medium_large',
		'large',
		'1536x1536',
		'2048x2048',
	);

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $_wp_additional_image_sizes;

		$all_sizes = array_keys( (array) $_wp_additional_image_sizes );
		$custom_sizes = array_values( array_diff( $all_sizes, self::DEFAULT_SIZES ) );
		$custom_count = count( $custom_sizes );

		if ( $custom_count <= self::MAX_RECOMMENDED_SIZES ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: number of custom sizes, 2: recommended limit */
				__( 'There are %1$d custom image sizes registered. Most sites are smoother with %2$d or fewer.', 'wpshadow' ),
				$custom_count,
				self::MAX_RECOMMENDED_SIZES
			),
			'severity'     => 'medium',
			'threat_level' => 50,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/custom-image-sizes-registration',
		);
	}
}
