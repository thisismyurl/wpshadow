<?php
/**
 * Media Filter Functionality Treatment
 *
 * Tests media library filters (date, type, uploaded by). Validates filter
 * accuracy and performance.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Media Filter Functionality Treatment Class
 *
 * Checks for media library filter issues.
 *
 * @since 0.6093.1200
 */
class Treatment_Media_Filter_Functionality extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-filter-functionality';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Media Filter Functionality';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests media library filters (date, type, uploaded by) and validates accuracy';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Media_Filter_Functionality' );
	}
}
