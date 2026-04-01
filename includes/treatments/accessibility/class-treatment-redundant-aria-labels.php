<?php
/**
 * Redundant ARIA Labels Treatment
 *
 * Checks for redundant ARIA roles on native elements.
 *
 * @package    WPShadow
 * @subpackage Treatments\Accessibility
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Redundant ARIA Labels Treatment Class
 *
 * Verifies that native elements are not given redundant ARIA roles.
 *
 * @since 0.6093.1200
 */
class Treatment_Redundant_ARIA_Labels extends Treatment_Base {

	/**
	 * The treatment slug.
	 *
	 * @var string
	 */
	protected static $slug = 'redundant-aria-labels';

	/**
	 * The treatment title.
	 *
	 * @var string
	 */
	protected static $title = 'Redundant ARIA Labels on Native Elements';

	/**
	 * The treatment description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks for redundant ARIA roles on native elements';

	/**
	 * The family this treatment belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Redundant_ARIA_Labels' );
	}
}
