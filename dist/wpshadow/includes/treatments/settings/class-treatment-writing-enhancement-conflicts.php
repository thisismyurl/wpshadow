<?php
/**
 * Writing Enhancement Conflicts Treatment
 *
 * Detects conflicts between writing-related settings and plugins that might
 * interfere with content creation experience.
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
 * Writing Enhancement Conflicts Treatment Class
 *
 * Detects writing-related conflicts and settings issues.
 *
 * @since 0.6093.1200
 */
class Treatment_Writing_Enhancement_Conflicts extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'writing-enhancement-conflicts';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Writing Enhancement Conflicts';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Detects writing-related conflicts';

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
	 * - Block editor is available
	 * - No conflicting editor plugins
	 * - Post/draft auto-save is configured
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Writing_Enhancement_Conflicts' );
	}
}
