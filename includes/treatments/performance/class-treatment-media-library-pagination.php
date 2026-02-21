<?php
/**
 * Media Library Pagination Treatment
 *
 * Validates pagination works correctly in media library. Tests with large media
 * counts and pagination performance.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6030.2148
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Media Library Pagination Treatment Class
 *
 * Checks for pagination issues in the media library.
 *
 * @since 1.6030.2148
 */
class Treatment_Media_Library_Pagination extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-library-pagination';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Media Library Pagination';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates pagination works correctly in media library with large media counts';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6030.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Media_Library_Pagination' );
	}
}
