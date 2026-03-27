<?php
/**
 * WCAG 2.4.2 Page Titled Treatment
 *
 * Validates that every page has a descriptive title element.
 *
 * @since 1.6093.1200
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WCAG Page Titles Treatment Class
 *
 * Checks for proper <title> elements on all pages (WCAG 2.4.2 Level A).
 *
 * @since 1.6093.1200
 */
class Treatment_WCAG_Page_Titles extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'wcag-page-titles';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Page Titles (WCAG 2.4.2)';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates that every page has a descriptive title element';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_WCAG_Page_Titles' );
	}
}
