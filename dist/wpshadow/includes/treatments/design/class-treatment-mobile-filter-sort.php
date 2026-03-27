<?php
/**
 * Mobile Filter/Sort Controls
 *
 * Validates product/content filtering on mobile devices.
 *
 * @package    WPShadow
 * @subpackage Treatments\Navigation
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Filter/Sort Controls Treatment.
 *
 * @since 1.6093.1200
 */
class Treatment_Mobile_Filter_Sort extends Treatment_Base {

	/**
	 * The treatment slug.
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-filter-sort-controls';

	/**
	 * The treatment title.
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Filter/Sort Controls';

	/**
	 * The treatment description.
	 *
	 * @var string
	 */
	protected static $description = 'Validates filters for mobile usability';

	/**
	 * The treatment family.
	 *
	 * @var string
	 */
	protected static $family = 'navigation';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_Filter_Sort' );
	}
}
