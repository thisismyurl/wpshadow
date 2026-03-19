<?php
/**
 * Focus Management Treatment
 *
 * Issue #4761: Visible Focus Moves During Keyboard Navigation
 * Pillar: 🌍 Accessibility First
 * Commandment: #8 (Inspire Confidence)
 *
 * Checks if focus is properly managed during interactions.
 * Focus should never move unexpectedly during keyboard navigation.
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
 * Treatment_Focus_Management Class
 *
 * Checks for proper focus management patterns.
 *
 * @since 1.6093.1200
 */
class Treatment_Focus_Management extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'focus-management';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Visible Focus Moves During Keyboard Navigation';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if focus changes are predictable and never jump unexpectedly';

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
		return self::proxy_diagnostic_check( '\\WPShadow\\Diagnostics\\Diagnostic_Focus_Management' );
	}
}
