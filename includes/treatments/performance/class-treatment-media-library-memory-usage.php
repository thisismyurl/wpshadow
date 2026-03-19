<?php
/**
 * Media Library Memory Usage Treatment
 *
 * Monitors memory usage when loading media library queries
 * and detects high memory consumption.
 *
 * @package    WPShadow
 * @subpackage Treatments\Tests
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Media_Library_Memory_Usage Class
 *
 * Checks memory usage impact of media library operations.
 *
 * @since 1.6093.1200
 */
class Treatment_Media_Library_Memory_Usage extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-library-memory-usage';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Media Library Memory Usage';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Monitors memory consumption for media library operations';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Media_Library_Memory_Usage' );
	}
}
