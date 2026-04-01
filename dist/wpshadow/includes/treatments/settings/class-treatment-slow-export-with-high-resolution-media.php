<?php
/**
 * Slow Export with High-Resolution Media
 *
 * Detects performance degradation when exporting content with large media files.
 *
 * @package    WPShadow
 * @subpackage Treatments\Export
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Slow_Export_With_High_Resolution_Media Class
 *
 * Tests performance degradation when exporting high-resolution media.
 * Monitors export speed, media processing, and file handling efficiency.
 *
 * @since 0.6093.1200
 */
class Treatment_Slow_Export_With_High_Resolution_Media extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'slow-export-with-high-resolution-media';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Export Performance with Media';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Detects performance issues when exporting large media files';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'export';

	/**
	 * Run the treatment check.
	 *
	 * Tests for media-related export performance issues.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Slow_Export_With_High_Resolution_Media' );
	}
}
