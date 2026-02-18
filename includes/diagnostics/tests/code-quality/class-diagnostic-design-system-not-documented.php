<?php
/**
 * Design System Not Documented Diagnostic
 *
 * Checks design system documentation.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6033.2033
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Design_System_Not_Documented Class
 *
 * Performs diagnostic check for Design System Not Documented.
 *
 * @since 1.6033.2033
 */
class Diagnostic_Design_System_Not_Documented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'design-system-not-documented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Design System Not Documented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks design system documentation';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'admin';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.2033
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if (   !get_option('design_system_documented' ) {
						return array(
						'id'   =>   self::$slug,
						'title'   =>   self::$title,
						'description'   =>   __('Design system not documented. Create comprehensive design documentation covering colors,
						'severity'   =>   'low',
						'threat_level'   =>   15,
						'auto_fixable'   =>   false,
						'kb_link'   =>   'https://wpshadow.com/kb/design-system-not-documented'
						);
						);,
						);
						}
						return null;
						}
						return null;
						}
						return null;
	}
}
