<?php
/**
 * Meta Box Plugin Conflicts Treatment
 *
 * Detects conflicts between meta box plugins. Tests for UI collisions and data
 * conflicts when multiple meta box frameworks are active.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6030.2148
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Meta Box Plugin Conflicts Treatment Class
 *
 * Checks for conflicts between meta box plugins.
 *
 * @since 1.6030.2148
 */
class Treatment_Meta_Box_Plugin_Conflicts extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'meta-box-plugin-conflicts';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Meta Box Plugin Conflicts';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Detects conflicts between meta box plugins causing UI or data issues';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'posts';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6030.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Meta_Box_Plugin_Conflicts' );
	}
}
