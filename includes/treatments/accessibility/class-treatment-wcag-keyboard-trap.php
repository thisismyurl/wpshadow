<?php
/**
 * WCAG 2.1.2 No Keyboard Trap Treatment
 *
 * Validates that keyboard users can escape from all interactive elements.
 *
 * @since 0.6093.1200
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WCAG Keyboard Trap Detection Treatment Class
 *
 * Checks for potential keyboard traps in modals, dropdowns, and custom widgets (WCAG 2.1.2 Level A).
 *
 * @since 0.6093.1200
 */
class Treatment_WCAG_Keyboard_Trap extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'wcag-keyboard-trap';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'No Keyboard Trap (WCAG 2.1.2)';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates that keyboard focus can move away from all components';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_WCAG_Keyboard_Trap' );
	}
}
