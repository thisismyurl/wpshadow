<?php
/**
 * Default Image Size Diagnostic
 *
 * WordPress stores an image_default_size option that controls the size
 * inserted when a user adds an image in the classic editor. When this is
 * set to "full", every image insertion defaults to the original uploaded
 * resolution, harming page performance and creating oversized layouts.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Default_Image_Size Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_Default_Image_Size extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'default-image-size';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Default Image Size';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks that the editor default image insertion size is not set to "full" to prevent oversized images being placed in content by default.';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'design';

	/**
	 * Confidence level of this diagnostic.
	 *
	 * @var string
	 */
	protected static $confidence = 'standard';

	/**
	 * Named image sizes considered acceptable as editor defaults.
	 */
	private const ACCEPTABLE_SIZES = array( '', 'thumbnail', 'medium', 'medium_large', 'large' );

	/**
	 * Run the diagnostic check.
	 *
	 * Reads the image_default_size option. An empty value means WordPress
	 * uses "medium" implicitly, which is safe. A value of "full" means every
	 * classic-editor image insertion will default to original resolution.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array if issue exists, null if healthy.
	 */
	public static function check() {
		$default_size = (string) get_option( 'image_default_size', '' );

		// Allow the empty default (WordPress uses medium) and all sub-full sizes.
		if ( in_array( $default_size, self::ACCEPTABLE_SIZES, true ) ) {
			return null;
		}

		// Any value that is NOT in the acceptable list — most notably 'full'.
		$is_full = ( 'full' === $default_size );

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => $is_full
				? __( 'The editor default image size is set to "full", meaning full-resolution images are inserted into content by default. This leads to oversized images, slow page loads, and broken layouts on smaller screens.', 'wpshadow' )
				: sprintf(
					/* translators: %s: the current default size value */
					__( 'The editor default image size is set to "%s", which is not a standard WordPress size and may produce unexpected results when editors insert images.', 'wpshadow' ),
					esc_html( $default_size )
				),
			'severity'     => $is_full ? 'medium' : 'low',
			'threat_level' => $is_full ? 40 : 20,
			'kb_link'      => 'https://wpshadow.com/kb/default-image-size?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'      => array(
				'current_size'    => $default_size,
				'recommended'     => 'large',
				'fix'             => __( 'Go to Settings &rsaquo; Media and ensure the image sizes are configured. Then change the default image size by updating the image_default_size option to "large" or "medium". This can also be done via Settings &rsaquo; Media in some themes.', 'wpshadow' ),
			),
		);
	}
}
