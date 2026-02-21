<?php
/**
 * Media Large Image Handling Treatment
 *
 * Tests handling of very large images (dimensions and file size)
 * and detects potential memory issues during processing.
 *
 * @package    WPShadow
 * @subpackage Treatments\Tests
 * @since      1.6033.1545
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Media_Large_Image_Handling Class
 *
 * Ensures WordPress can handle large images without running out
 * of memory or timing out during upload and processing.
 *
 * @since 1.6033.1545
 */
class Treatment_Media_Large_Image_Handling extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-large-image-handling';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Large Image Handling';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests handling of large images and detects memory issues';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.1545
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Media_Large_Image_Handling' );
	}
}
