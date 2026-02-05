<?php
/**
 * Keyboard Shortcut Accessibility Not Verified Treatment
 *
 * Checks if keyboard shortcuts are accessible.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6030.2352
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
 * @since 1.6030.2352
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
	 * @since  1.6030.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for documented keyboard shortcuts
		if ( ! has_option( 'keyboard_shortcuts_documented' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Keyboard shortcut accessibility is not verified. Document all keyboard shortcuts and ensure they don\'t conflict with browser/OS shortcuts. Test with keyboard-only navigation.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 10,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/keyboard-shortcut-accessibility-not-verified',
			);
		}

		return null;
	}
}
