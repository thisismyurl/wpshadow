<?php
/**
 * Media Storage Space Availability Treatment
 *
 * Checks available disk space in the uploads directory
 * and warns before reaching critical limits.
 *
 * @package    WPShadow
 * @subpackage Treatments\Tests
 * @since      1.6033.1605
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Media_Storage_Space_Availability Class
 *
 * Monitors available disk space for media uploads.
 *
 * @since 1.6033.1605
 */
class Treatment_Media_Storage_Space_Availability extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-storage-space-availability';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Storage Space Availability';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks available disk space for uploads directory';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.1605
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Media_Storage_Space_Availability' );
	}
}
