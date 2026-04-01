<?php
/**
 * Image Optimization Integration Treatment
 *
 * Checks if image optimization plugins are working correctly. Tests compression and format conversion.
 *
 * @package    WPShadow
 * @subpackage Treatments\Media
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Image_Optimization_Integration Class
 *
 * Validates image optimization plugin integration. Popular plugins like
 * EWWW, Imagify, ShortPixel, and Smush compress images on upload.
 * Misconfigurations can prevent optimization or cause quality loss.
 *
 * @since 0.6093.1200
 */
class Treatment_Image_Optimization_Integration extends Treatment_Base {

	/**
	 * The treatment slug.
	 *
	 * @var string
	 */
	protected static $slug = 'image-optimization-integration';

	/**
	 * The treatment title.
	 *
	 * @var string
	 */
	protected static $title = 'Image Optimization Integration';

	/**
	 * The treatment description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if image optimization plugins are working correctly';

	/**
	 * The family this treatment belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * Validates:
	 * - Optimization plugin detection
	 * - Plugin configuration
	 * - Optimization effectiveness
	 * - API connectivity
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Image_Optimization_Integration' );
	}
}
