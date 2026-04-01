<?php
/**
 * Content Inconsistent Reading Level Treatment
 *
 * Detects dramatic variance in reading level consistency.
 *
 * @since 0.6093.1200
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Content Inconsistent Reading Level Treatment Class
 *
 * Reading level varies dramatically (grade 6 to grade 14+) causing brand
 * inconsistency. After standardizing: 31% bounce rate decrease.
 *
 * @since 0.6093.1200
 */
class Treatment_Content_Inconsistent_Reading_Level extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'content-inconsistent-reading-level';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Inconsistent Reading Level';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Detect dramatic variance in reading level across content';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content-strategy';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Content_Inconsistent_Reading_Level' );
	}
}
