<?php
/**
 * ARIA Landmark Roles Treatment
 *
 * Issue #4891: No ARIA Landmark Roles for Screen Readers
 * Pillar: 🌍 Accessibility First
 *
 * Checks if page sections have semantic landmarks.
 * Screen readers use landmarks to navigate page structure.
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
 * Treatment_ARIA_Landmark_Roles Class
 *
 * @since 0.6093.1200
 */
class Treatment_ARIA_Landmark_Roles extends Treatment_Base {

	protected static $slug = 'aria-landmark-roles';
	protected static $title = 'No ARIA Landmark Roles for Screen Readers';
	protected static $description = 'Checks if page sections use semantic HTML5 or ARIA landmarks';
	protected static $family = 'accessibility';

	public static function check() {
		return self::proxy_diagnostic_check( '\\WPShadow\\Diagnostics\\Diagnostic_ARIA_Landmark_Roles' );
	}
}
