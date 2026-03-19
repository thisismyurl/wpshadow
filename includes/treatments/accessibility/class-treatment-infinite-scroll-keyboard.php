<?php
/**
 * Infinite Scroll Accessibility Treatment
 *
 * Issue #4762: Infinite Scroll Without Keyboard Bypass
 * Pillar: 🌍 Accessibility First
 *
 * Checks if infinite scroll provides keyboard alternatives.
 * Keyboard users need a way to reach footer and skip infinite content.
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
 * Treatment_Infinite_Scroll_Keyboard Class
 *
 * Checks for keyboard-accessible infinite scroll implementations.
 *
 * @since 1.6093.1200
 */
class Treatment_Infinite_Scroll_Keyboard extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'infinite-scroll-keyboard';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Infinite Scroll Without Keyboard Bypass';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if infinite scroll allows keyboard users to reach footer';

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
		return self::proxy_diagnostic_check( '\\WPShadow\\Diagnostics\\Diagnostic_Infinite_Scroll_Keyboard' );
	}
}
