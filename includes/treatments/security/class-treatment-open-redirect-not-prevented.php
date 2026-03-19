<?php
/**
 * Open Redirect Not Prevented Treatment
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
 * Treatment_Open_Redirect_Not_Prevented Class
 *
 * Performs treatment check for Open Redirect Not Prevented.
 *
 * @since 1.6093.1200
 */
class Treatment_Open_Redirect_Not_Prevented extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'open-redirect-not-prevented';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Open Redirect Not Prevented';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks open redirect';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Open_Redirect_Not_Prevented' );
	}
}