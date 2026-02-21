<?php
/**
 * Permalink Conflict Detection Treatment
 *
 * Detects conflicts between post slugs, pages, and custom post types that could
 * cause permalink collisions.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6032.1745
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Permalink Conflict Detection Treatment Class
 *
 * Identifies potential URL conflicts in WordPress permalink structure.
 *
 * @since 1.6032.1745
 */
class Treatment_Permalink_Conflict_Detection extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'permalink-conflict-detection';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Permalink Conflict Detection';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Detects URL conflicts';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'permalinks';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6032.1745
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Permalink_Conflict_Detection' );
	}
}
