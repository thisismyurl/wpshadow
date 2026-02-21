<?php
/**
 * Accessible Form Labels Treatment
 *
 * Issue #4870: Form Fields Missing Associated Labels
 * Pillar: 🌍 Accessibility First
 *
 * Checks if all form inputs have properly associated labels.
 * Screen reader users can't navigate form fields without labels.
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
 * Treatment_Accessible_Form_Labels Class
 *
 * Checks for:
 * - <label> tag with for="input-id" attribute
 * - aria-label on inputs without visible labels
 * - aria-labelledby for complex label scenarios
 * - Descriptive labels (not "Submit" but "Save Settings")
 * - Required fields marked with aria-required="true"
 * - Error messages linked with aria-describedby
 * - Fieldset + legend for grouped related fields
 *
 * Why this matters:
 * - Screen readers announce the label, not the field type
 * - Without label, user doesn't know what to enter
 * - Mouse users need larger click area (label is clickable)
 * - Mobile users benefit from larger touch targets
 *
 * @since 1.6050.0000
 */
class Treatment_Accessible_Form_Labels extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @since 1.6050.0000
	 * @var   string
	 */
	protected static $slug = 'accessible-form-labels';

	/**
	 * The treatment title
	 *
	 * @since 1.6050.0000
	 * @var   string
	 */
	protected static $title = 'Form Fields Missing Associated Labels';

	/**
	 * The treatment description
	 *
	 * @since 1.6050.0000
	 * @var   string
	 */
	protected static $description = 'Checks if form inputs have properly associated labels for accessibility';

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
		return self::proxy_diagnostic_check( '\\WPShadow\\Diagnostics\\Diagnostic_Accessible_Form_Labels' );
	}
}
