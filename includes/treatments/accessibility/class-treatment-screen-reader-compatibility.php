<?php
/**
 * Screen Reader Compatibility Treatment
 *
 * Issue #4862: Admin Interface Not Compatible with Screen Readers
 * Pillar: 🌍 Accessibility First
 *
 * Checks if admin interface works with screen reader software.
 * ~2% of users are blind or severely low vision and depend on screen readers.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6050.0000
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Screen_Reader_Compatibility Class
 *
 * Checks for:
 * - Semantic HTML (proper heading structure h1→h2→h3)
 * - ARIA labels on inputs and buttons
 * - Alt text on images
 * - ARIA live regions for dynamic content updates
 * - Form labels properly associated with inputs
 * - List structure for grouped items
 * - Table headers (th vs td)
 * - No JavaScript-only interfaces (keyboard + screen reader support)
 *
 * Why this matters:
 * - Blind and low vision users depend entirely on screen readers
 * - Screen readers need semantic HTML to understand page structure
 * - Non-semantic markup requires ARIA to communicate purpose
 * - 1-2% is small until it's YOU or a loved one
 *
 * @since 1.6050.0000
 */
class Treatment_Screen_Reader_Compatibility extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @since 1.6050.0000
	 * @var   string
	 */
	protected static $slug = 'screen-reader-compatibility';

	/**
	 * The treatment title
	 *
	 * @since 1.6050.0000
	 * @var   string
	 */
	protected static $title = 'Admin Interface Not Compatible with Screen Readers';

	/**
	 * The treatment description
	 *
	 * @since 1.6050.0000
	 * @var   string
	 */
	protected static $description = 'Checks if blind/low vision users can navigate admin with screen readers';

	/**
	 * The family this treatment belongs to
	 *
	 * @since 1.6050.0000
	 * @var   string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6050.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Screen_Reader_Compatibility' );
	}
}
