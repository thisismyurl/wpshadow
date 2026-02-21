<?php
/**
 * WCAG 3.3.1 Error Identification Treatment
 *
 * Validates that form errors are clearly identified and associated with fields.
 *
 * @since   1.6035.1200
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WCAG Form Error Identification Treatment Class
 *
 * Checks for proper form error identification (WCAG 3.3.1 Level A).
 *
 * @since 1.6035.1200
 */
class Treatment_WCAG_Form_Error_Identification extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'wcag-form-error-identification';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Form Error Identification (WCAG 3.3.1)';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates that form errors are clearly identified';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6035.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_WCAG_Form_Error_Identification' );
	}
}
