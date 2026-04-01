<?php
/**
 * Theme Update Status Treatment
 *
 * Checks for outdated themes that require updates.
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
 * Treatment_Theme_Update_Status Class
 *
 * Detects pending theme updates.
 *
 * @since 0.6093.1200
 */
class Treatment_Theme_Update_Status extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-update-status';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Update Status';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for themes that require updates';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'wordpress-health';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Theme_Update_Status' );
	}
}