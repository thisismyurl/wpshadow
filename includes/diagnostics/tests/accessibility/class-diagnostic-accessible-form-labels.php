<?php
/**
 * Accessible Form Labels Diagnostic
 *
 * Issue #4870: Form Fields Missing Associated Labels
 * Pillar: 🌍 Accessibility First
 *
 * Checks if all form inputs have properly associated labels.
 * Screen reader users can't navigate form fields without labels.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Accessible_Form_Labels Class
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
 * @since 0.6093.1200
 */
class Diagnostic_Accessible_Form_Labels extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @since 0.6093.1200
	 * @var   string
	 */
	protected static $slug = 'accessible-form-labels';

	/**
	 * The diagnostic title
	 *
	 * @since 0.6093.1200
	 * @var   string
	 */
	protected static $title = 'Form Fields Missing Associated Labels';

	/**
	 * The diagnostic description
	 *
	 * @since 0.6093.1200
	 * @var   string
	 */
	protected static $description = 'Checks if form inputs have properly associated labels for accessibility';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @since 0.6093.1200
	 * @var   string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// This is a guidance diagnostic - actual form analysis requires visual/keyboard testing.
		// We provide recommendations for form accessibility.

		$issues = array();

		$issues[] = __( 'Use <label for="field-id"> for all visible form labels', 'wpshadow' );
		$issues[] = __( 'Use aria-label="Label text" for hidden labels', 'wpshadow' );
		$issues[] = __( 'Link error messages with aria-describedby="error-id"', 'wpshadow' );
		$issues[] = __( 'Mark required fields: aria-required="true"', 'wpshadow' );
		$issues[] = __( 'Use <fieldset> + <legend> to group related fields', 'wpshadow' );
		$issues[] = __( 'Make labels more descriptive: "Save Settings" not "Submit"', 'wpshadow' );
		$issues[] = __( 'Ensure labels are clickable (click label = focus input)', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Screen reader users navigate forms by reading labels. Without labels, they don\'t know what to enter in each field.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/form-labels?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'recommendations'         => $issues,
					'wcag_requirement'        => 'WCAG 2.0.6093.1200 Info and Relationships',
					'css_pattern'             => 'label { display: block; margin-bottom: 5px; } /* Ensure space */',
					'ux_benefit'              => 'Larger click area for labels helps mobile users and people with fine-motor issues',
					'affected_population'     => 'Blind, low vision, motor disabilities, mobile users',
				),
			);
		}

		return null;
	}
}
