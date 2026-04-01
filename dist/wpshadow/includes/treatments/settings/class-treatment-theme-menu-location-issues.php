<?php
/**
 * Theme Menu Location Treatment
 *
 * Detects issues with theme's registered menu locations and navigation.
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
 * Theme Menu Location Treatment Class
 *
 * Checks if theme properly registers menu locations and if assigned menus exist.
 *
 * @since 0.6093.1200
 */
class Treatment_Theme_Menu_Location_Issues extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-menu-location-issues';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Menu Location Issues';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for theme menu registration and assignment issues';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Theme_Menu_Location_Issues' );
	}
}
