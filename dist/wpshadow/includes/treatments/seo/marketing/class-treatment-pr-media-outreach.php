<?php
/**
 * PR and Media Outreach Treatment
 *
 * Checks whether press kit and media outreach assets exist.
 *
 * @package    WPShadow
 * @subpackage Treatments\Marketing
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PR and Media Outreach Treatment Class
 *
 * Verifies press kit and media outreach indicators.
 *
 * @since 0.6093.1200
 */
class Treatment_Pr_Media_Outreach extends Treatment_Base {

	/**
	 * The treatment slug.
	 *
	 * @var string
	 */
	protected static $slug = 'pr-media-outreach';

	/**
	 * The treatment title.
	 *
	 * @var string
	 */
	protected static $title = 'No PR or Media Outreach Strategy';

	/**
	 * The treatment description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks for press kits, media pages, and outreach indicators';

	/**
	 * The family this treatment belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'brand-awareness';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Pr_Media_Outreach' );
	}
}
