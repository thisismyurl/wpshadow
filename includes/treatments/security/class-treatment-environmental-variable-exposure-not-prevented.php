<?php
/**
 * Environmental Variable Exposure Not Prevented Treatment
 *
 * Checks env variable exposure.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Environmental_Variable_Exposure_Not_Prevented Class
 *
 * Performs treatment check for Environmental Variable Exposure Not Prevented.
 *
 * @since 1.6093.1200
 */
class Treatment_Environmental_Variable_Exposure_Not_Prevented extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'environmental-variable-exposure-not-prevented';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Environmental Variable Exposure Not Prevented';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks env variable exposure';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Environmental_Variable_Exposure_Not_Prevented' );
	}
						return null;
						}
						return null;
	}
}
