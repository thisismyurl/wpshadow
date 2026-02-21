<?php
/**
 * Export Slow High-Resolution Media Treatment
 *
 * Detects performance degradation when exporting content with large media files.
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
 * Export Slow High-Resolution Media Treatment Class
 *
 * Checks for slow media export performance.
 *
 * @since 1.6030.2148
 */
class Treatment_Export_Slow_High_Resolution_Media extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'export-slow-high-resolution-media';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Slow Export with High-Resolution Media';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Detects performance issues with large media exports';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'import-export';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6030.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Export_Slow_High_Resolution_Media' );
	}
}
