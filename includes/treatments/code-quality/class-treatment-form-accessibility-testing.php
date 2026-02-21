<?php
/**
 * Form Accessibility Testing Treatment
 *
 * Tests if forms are accessible with proper labels and error messages.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.7034.1340
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Form Accessibility Testing Treatment Class
 *
 * Validates that forms have proper labels, error messages, and
 * keyboard accessibility for users with disabilities.
 *
 * @since 1.7034.1340
 */
class Treatment_Form_Accessibility_Testing extends Treatment_Base {

	/**
	 * Treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'form-accessibility-testing';

	/**
	 * Treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Form Accessibility Testing';

	/**
	 * Treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if forms are accessible with proper labels and error messages';

	/**
	 * Treatment family
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the treatment check.
	 *
	 * Tests form accessibility including label associations, required
	 * field indicators, error messages, and fieldset grouping.
	 *
	 * @since  1.7034.1340
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Form_Accessibility_Testing' );
	}
}
