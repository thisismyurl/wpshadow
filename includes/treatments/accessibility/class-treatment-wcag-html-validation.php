<?php
/**
 * WCAG 4.1.1 HTML Validation Treatment
 *
 * Validates that HTML is well-formed for assistive technology compatibility.
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
 * WCAG HTML Validation Treatment Class
 *
 * Checks for valid HTML structure (WCAG 4.1.1 Level A).
 *
 * @since 0.6093.1200
 */
class Treatment_WCAG_HTML_Validation extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'wcag-html-validation';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'HTML Validation (WCAG 4.1.1)';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates HTML structure for assistive technology compatibility';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_WCAG_HTML_Validation' );
	}
}
