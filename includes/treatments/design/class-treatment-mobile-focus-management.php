<?php
/**
 * Mobile Focus Management Treatment
 *
 * Validates focus order is logical and focus isn't trapped in modals/overlays on mobile devices.
 *
 * @since   1.6033.1645
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Focus Management Treatment Class
 *
 * Validates that focus management works correctly on mobile devices with keyboard support,
 * ensuring WCAG A compliance for keyboard navigation and focus visibility.
 *
 * @since 1.6033.1645
 */
class Treatment_Mobile_Focus_Management extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-focus-management';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Focus Management';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validate focus order is logical and focus is not trapped in modals/overlays on mobile devices';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.1645
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_Focus_Management' );
	}
}
