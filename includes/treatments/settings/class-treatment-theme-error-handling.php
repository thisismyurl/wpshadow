<?php
/**
 * Theme Error Handling Treatment
 *
 * Checks theme error handling and fallback templates.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6030.2240
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Error Handling Treatment
 *
 * Ensures theme provides basic error and 404 handling.
 *
 * @since 1.6030.2240
 */
class Treatment_Theme_Error_Handling extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-error-handling';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Error Handling';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks theme error handling and fallback templates';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6030.2240
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Theme_Error_Handling' );
	}
}
