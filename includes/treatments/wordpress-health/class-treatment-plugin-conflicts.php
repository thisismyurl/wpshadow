<?php
/**
 * Plugin Conflicts Treatment
 *
 * Detects potentially conflicting plugin combinations (e.g., multiple cache or security plugins).
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6035.1315
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Plugin_Conflicts Class
 *
 * Flags known overlapping plugin categories that often cause conflicts.
 *
 * @since 1.6035.1315
 */
class Treatment_Plugin_Conflicts extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-conflicts';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Conflicts';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Detects potential conflicts from overlapping plugin categories';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'wordpress-health';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6035.1315
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Plugin_Conflicts' );
	}
}