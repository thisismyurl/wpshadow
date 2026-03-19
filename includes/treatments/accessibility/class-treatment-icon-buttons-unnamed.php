<?php
/**
 * Icon Buttons Missing Accessible Names Treatment
 *
 * Issue #4752: Icon Buttons Missing Accessible Names
 * Pillar: 🌍 Accessibility First
 * Commandment: #8 (Inspire Confidence)
 *
 * Checks if icon-only buttons have accessible names.
 * Screen readers need aria-label or aria-labelledby to describe icon buttons.
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
 * Treatment_Icon_Buttons_Unnamed Class
 *
 * Checks for accessible names on icon-only buttons.
 *
 * @since 1.6093.1200
 */
class Treatment_Icon_Buttons_Unnamed extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'icon-buttons-unnamed';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Icon Buttons Missing Accessible Names';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if icon-only buttons have aria-label or aria-labelledby attributes';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\\WPShadow\\Diagnostics\\Diagnostic_Icon_Buttons_Unnamed' );
	}
}
