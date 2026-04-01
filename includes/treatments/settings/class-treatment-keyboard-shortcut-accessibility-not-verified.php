<?php
/**
 * Keyboard Shortcut Accessibility Not Verified Treatment
 *
 * Checks if keyboard shortcuts are accessible.
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
 * Keyboard Shortcut Accessibility Not Verified Treatment Class
 *
 * Detects missing keyboard shortcut accessibility.
 *
 * @since 0.6093.1200
 */
class Treatment_Keyboard_Shortcut_Accessibility_Not_Verified extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'keyboard-shortcut-accessibility-not-verified';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Keyboard Shortcut Accessibility Not Verified';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if keyboard shortcuts are accessible';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Keyboard_Shortcut_Accessibility_Not_Verified' );
	}
}
