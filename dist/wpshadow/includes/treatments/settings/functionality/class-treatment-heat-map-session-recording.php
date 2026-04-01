<?php
/**
 * Heat Map & Session Recording Treatment
 *
 * Checks if heat mapping and session recording tools are configured.
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
 * Heat Map & Session Recording Treatment Class
 *
 * Heat maps show what users actually do vs. what you think they do. Often
 * reveals buttons are invisible or important content is ignored.
 *
 * @since 0.6093.1200
 */
class Treatment_Heat_Map_Session_Recording extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'heat-map-session-recording';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Heat Map & Session Recording';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if heat mapping and session recording tools are active';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'analytics';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Heat_Map_Session_Recording' );
	}
}
