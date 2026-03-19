<?php
/**
 * Focus Indicators Missing Treatment
 *
 * Checks if keyboard focus indicators are visible.
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
 * Focus Indicators Treatment Class
 *
 * Validates that keyboard focus is visible (not hidden with outline:none).
 *
 * @since 1.6093.1200
 */
class Treatment_Focus_Indicators_Missing extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'focus-indicators-missing';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Focus Indicators Not Visible';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if keyboard focus indicators are visible';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\\WPShadow\\Diagnostics\\Diagnostic_Focus_Indicators_Missing' );
	}
}
