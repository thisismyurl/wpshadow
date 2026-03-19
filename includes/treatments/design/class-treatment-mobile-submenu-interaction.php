<?php
/**
 * Mobile Submenu Interaction Treatment
 *
 * Validates that dropdown/submenu items use tap/click instead of hover-only disclosure on mobile.
 *
 * @since 1.6093.1200
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Submenu Interaction Treatment Class
 *
 * Validates that dropdown/submenu items use tap/click instead of hover-only disclosure,
 * ensuring full navigation access on touch devices.
 *
 * @since 1.6093.1200
 */
class Treatment_Mobile_Submenu_Interaction extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-submenu-interaction';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Submenu Interaction';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validate dropdown/submenu items use tap/click instead of hover-only disclosure on mobile';

	/**
	 * The family this treatment belongs to
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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_Submenu_Interaction' );
	}
}
