<?php
/**
 * Keyboard Shortcut Accessibility Not Verified Diagnostic
 *
 * Checks if keyboard shortcuts are accessible.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Keyboard Shortcut Accessibility Not Verified Diagnostic Class
 *
 * Detects missing keyboard shortcut accessibility.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Keyboard_Shortcut_Accessibility_Not_Verified extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'keyboard-shortcut-accessibility-not-verified';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Keyboard Shortcut Accessibility Not Verified';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if keyboard shortcuts are accessible';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for documented keyboard shortcuts
		if ( null === get_option( 'keyboard_shortcuts_documented', null ) ) {
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
