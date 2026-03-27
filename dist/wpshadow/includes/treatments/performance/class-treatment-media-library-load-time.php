<?php
/**
 * Media Library Load Time Treatment
 *
 * Tests media library performance by measuring load times for
 * grid and list views with various attachment counts.
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
 * Treatment_Media_Library_Load_Time Class
 *
 * Ensures media library loads quickly even with large numbers
 * of attachments and identifies performance bottlenecks.
 *
 * @since 1.6093.1200
 */
class Treatment_Media_Library_Load_Time extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-library-load-time';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Media Library Load Time';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests media library performance and load times';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Media_Library_Load_Time' );
	}
}
