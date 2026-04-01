<?php
/**
 * JavaScript Error Isolation Treatment
 *
 * Checks whether JavaScript errors are monitored and contained.
 *
 * @package    WPShadow
 * @subpackage Treatments\Reliability
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * JavaScript Error Isolation Treatment Class
 *
 * Verifies that front-end errors are tracked and do not cascade.
 *
 * @since 0.6093.1200
 */
class Treatment_Javascript_Error_Isolation extends Treatment_Base {

	/**
	 * The treatment slug.
	 *
	 * @var string
	 */
	protected static $slug = 'javascript-error-isolation';

	/**
	 * The treatment title.
	 *
	 * @var string
	 */
	protected static $title = 'JavaScript Errors Break Entire Page Functionality';

	/**
	 * The treatment description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if front-end errors are tracked and isolated';

	/**
	 * The family this treatment belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'reliability';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Javascript_Error_Isolation' );
	}
}
