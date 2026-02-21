<?php
/**
 * Accessibility Standards Compliance (WCAG) Treatment
 *
 * Validates website compliance with WCAG accessibility standards.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6030.2240
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Accessibility Standards Compliance (WCAG) Treatment
 *
 * Checks website for WCAG 2.1 accessibility compliance.
 *
 * @since 1.6030.2240
 */
class Treatment_Accessibility_Standards_Compliance_WCAG extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'accessibility-standards-compliance-wcag';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Accessibility Standards Compliance (WCAG)';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates website compliance with WCAG accessibility standards';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6030.2240
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Accessibility_Standards_Compliance_WCAG' );
	}
}
