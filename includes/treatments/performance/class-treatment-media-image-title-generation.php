<?php
/**
 * Media Image Title Generation Treatment
 *
 * Tests automatic title generation from filenames and
 * validates title sanitization.
 *
 * @package    WPShadow
 * @subpackage Treatments\Tests
 * @since      1.6033.1625
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Media_Image_Title_Generation Class
 *
 * Checks attachment titles for proper generation and sanitization.
 *
 * @since 1.6033.1625
 */
class Treatment_Media_Image_Title_Generation extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-image-title-generation';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Image Title Generation';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests automatic title generation from filenames';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.1625
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Media_Image_Title_Generation' );
	}
}
