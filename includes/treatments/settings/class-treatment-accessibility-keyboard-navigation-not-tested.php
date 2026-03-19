<?php
/**
 * Accessibility Keyboard Navigation Not Tested Treatment
 *
 * Checks if keyboard navigation is tested.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Accessibility Keyboard Navigation Not Tested Treatment Class
 *
 * Detects untested keyboard navigation.
 *
 * @since 1.6093.1200
 */
class Treatment_Accessibility_Keyboard_Navigation_Not_Tested extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'accessibility-keyboard-navigation-not-tested';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Accessibility Keyboard Navigation Not Tested';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if keyboard navigation is tested';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\\WPShadow\\Diagnostics\\Diagnostic_Accessibility_Keyboard_Navigation_Not_Tested' );
	}
}
