<?php
/**
 * Image Dimensions Not Set Causing Layout Shift Diagnostic
 *
 * Checks if image dimensions are set.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Image Dimensions Not Set Causing Layout Shift Diagnostic Class
 *
 * Detects missing image dimensions.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Image_Dimensions_Not_Set_Causing_Layout_Shift extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'image-dimensions-not-set-causing-layout-shift';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Image Dimensions Not Set Causing Layout Shift';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if image dimensions are set';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for image dimension handling
		if ( ! has_filter( 'wp_get_attachment_image' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Image dimensions are not properly set. Add width and height attributes to images to prevent Cumulative Layout Shift (CLS).', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 40,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/image-dimensions-not-set-causing-layout-shift',
			);
		}

		return null;
	}
}
