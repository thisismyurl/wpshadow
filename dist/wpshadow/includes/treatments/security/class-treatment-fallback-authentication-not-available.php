<?php
/**
 * Fallback Authentication Not Available Treatment
 *
 * Checks fallback auth.
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
 * Treatment_Fallback_Authentication_Not_Available Class
 *
 * Performs treatment check for Fallback Authentication Not Available.
 *
 * @since 1.6093.1200
 */
class Treatment_Fallback_Authentication_Not_Available extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'fallback-authentication-not-available';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Fallback Authentication Not Available';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks fallback auth';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Fallback_Authentication_Not_Available' );
	}
}
