<?php
/**
 * Mobile Focus Management
 *
 * Ensures focus order is logical and focus is not trapped in interactive elements.
 *
 * @package    WPShadow
 * @subpackage Treatments\Accessibility
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Focus Management
 *
 * Validates focus order is logical, focus is visible, and focus traps
 * are properly implemented in modals.
 * WCAG 2.4.3 Level A requirement.
 *
 * @since 0.6093.1200
 */
class Treatment_Mobile_Focus extends Treatment_Base {

	/**
	 * The treatment slug.
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-focus-management';

	/**
	 * The treatment title.
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Focus Management';

	/**
	 * The treatment description.
	 *
	 * @var string
	 */
	protected static $description = 'Validates focus order and management';

	/**
	 * The treatment family.
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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_Focus' );
	}
}
