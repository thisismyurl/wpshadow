<?php
/**
 * Session Fixation Not Prevented Treatment
 *
 * Checks session fixation.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Session_Fixation_Not_Prevented Class
 *
 * Performs treatment check for Session Fixation Not Prevented.
 *
 * @since 0.6093.1200
 */
class Treatment_Session_Fixation_Not_Prevented extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'session-fixation-not-prevented';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Session Fixation Not Prevented';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks session fixation';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\\WPShadow\\Diagnostics\\Diagnostic_Session_Fixation_Not_Prevented' );
	}
}
