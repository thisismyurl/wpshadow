<?php
/**
 * Time Format Consistency Treatment
 *
 * Verifies that the time format setting is properly configured and consistent
 * throughout the WordPress site.
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
 * Time Format Consistency Treatment Class
 *
 * Ensures time format is properly configured.
 *
 * @since 1.6093.1200
 */
class Treatment_Time_Format_Consistency extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'time-format-consistency';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Time Format Consistency';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies time format is consistent';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'settings';

	/**
	 * Run the treatment check.
	 *
	 * Checks:
	 * - Time format is set and not empty
	 * - Time format is valid PHP time format
	 * - Time format is reasonable (12 or 24 hour)
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Time_Format_Consistency' );
	}
}
