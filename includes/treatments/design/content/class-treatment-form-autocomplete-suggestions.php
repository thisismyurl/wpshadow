<?php
/**
 * Form Autocomplete Suggestions Treatment
 *
 * Issue #4971: Form Fields Don't Autocomplete (Poor UX)
 * Pillar: 🎓 Learning Inclusive / #1: Helpful Neighbor
 *
 * Checks if form fields use autocomplete attributes.
 * Users should fill forms quickly with browser autofill.
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
 * Treatment_Form_Autocomplete_Suggestions Class
 *
 * @since 0.6093.1200
 */
class Treatment_Form_Autocomplete_Suggestions extends Treatment_Base {

	protected static $slug = 'form-autocomplete-suggestions';
	protected static $title = 'Form Fields Don\'t Autocomplete (Poor UX)';
	protected static $description = 'Checks if form fields use autocomplete attributes';
	protected static $family = 'content';

	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Form_Autocomplete_Suggestions' );
	}
}
