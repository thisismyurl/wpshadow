<?php
/**
 * Theme Active & Valid Treatment
 *
 * Ensures the active theme exists and is properly loaded.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Theme_Active_Valid Class
 *
 * Checks that the active theme exists and is not broken.
 *
 * @since 1.6093.1200
 */
class Treatment_Theme_Active_Valid extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-active-valid';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Active & Valid';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Ensures the active theme exists and is valid';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'wordpress-health';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Theme_Active_Valid' );
	}
}