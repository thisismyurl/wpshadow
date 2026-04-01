<?php
/**
 * Import Progress Indicators Treatment
 *
 * Tests whether import progress is visible to users.
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
 * Import Progress Indicators Treatment Class
 *
 * Tests whether import progress indicators and feedback are visible during imports.
 *
 * @since 0.6093.1200
 */
class Treatment_Import_Progress_Indicators extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'import-progress-indicators';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Import Progress Indicators';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether import progress is visible to users';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Import_Progress_Indicators' );
	}
}
