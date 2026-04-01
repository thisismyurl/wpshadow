<?php
/**
 * Mobile SVG Accessibility Treatment
 *
 * Ensures SVG icons are accessible to screen readers.
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
 * Mobile SVG Accessibility Treatment Class
 *
 * Ensures SVG icons have proper accessibility features including title/desc elements
 * and ARIA roles for screen reader users.
 *
 * @since 0.6093.1200
 */
class Treatment_Mobile_SVG_Accessibility extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-svg-accessibility';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile SVG Accessibility';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Ensure SVG icons have proper accessibility labels and ARIA roles';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_SVG_Accessibility' );
	}
}
