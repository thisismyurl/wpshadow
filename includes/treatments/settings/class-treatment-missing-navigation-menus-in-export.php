<?php
/**
 * Missing Navigation Menus in Export Treatment
 *
 * Detects when WordPress navigation menus are excluded from
 * export files.
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
 * Missing Navigation Menus in Export Treatment Class
 *
 * Detects when WordPress navigation menus are excluded from
 * export files.
 *
 * @since 0.6093.1200
 */
class Treatment_Missing_Navigation_Menus_In_Export extends Treatment_Base {

	/**
	 * Treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'missing-navigation-menus-in-export';

	/**
	 * Treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Missing Navigation Menus in Export';

	/**
	 * Treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Detects navigation menus excluded from exports';

	/**
	 * Treatment family
	 *
	 * @var string
	 */
	protected static $family = 'export';

	/**
	 * Run the treatment check.
	 *
	 * Verifies that navigation menus and menu items are properly
	 * included in export files.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Missing_Navigation_Menus_In_Export' );
	}
}
