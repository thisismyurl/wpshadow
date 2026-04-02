<?php
/**
 * Child Theme In Use Diagnostic (Stub)
 *
 * TODO: Implement robust, production-safe test logic.
 * TODO: Implement companion treatment after validation.
 * TODO: Add KB article and user-facing remediation guidance.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Child_Theme_Active Class (Stub)
 *
 * @since 0.6093.1200
 */
class Diagnostic_Child_Theme_Active extends Diagnostic_Base {

	/**
	 * @var string
	 */
	protected static $slug = 'child-theme-active';

	/**
	 * @var string
	 */
	protected static $title = 'Child Theme In Use';

	/**
	 * @var string
	 */
	protected static $description = 'Checks whether the active theme is a child theme. Customising a parent theme directly means all changes are lost when the theme is updated.';

	/**
	 * @var string
	 */
	protected static $family = 'design';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  0.6093.1200
	 * @return array|null Return finding array when issue exists, null when healthy.
	 */
	public static function check() {
		// TODO: Implement testable logic.
		return null;
	}
}
