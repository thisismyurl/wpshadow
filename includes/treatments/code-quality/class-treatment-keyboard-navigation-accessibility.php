<?php
/**
 * Keyboard Navigation Accessibility Treatment
 *
 * Tests if site is fully navigable via keyboard for accessibility.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.7034.1300
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Keyboard Navigation Accessibility Treatment Class
 *
 * Validates that the site is fully keyboard accessible per WCAG 2.1
 * guidelines for users who cannot use a mouse.
 *
 * @since 1.7034.1300
 */
class Treatment_Keyboard_Navigation_Accessibility extends Treatment_Base {

	/**
	 * Treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'keyboard-navigation-accessibility';

	/**
	 * Treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Keyboard Navigation Accessibility';

	/**
	 * Treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if site is fully navigable via keyboard for accessibility';

	/**
	 * Treatment family
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the treatment check.
	 *
	 * Tests keyboard navigation including skip links, focus indicators,
	 * and accessible dropdown menus.
	 *
	 * @since  1.7034.1300
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Keyboard_Navigation_Accessibility' );
	}
}
