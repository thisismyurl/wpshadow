<?php
/**
 * Missing Form Labels Diagnostic
 *
 * Detects form inputs without associated labels, preventing
 * screen reader users from understanding form purpose.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Missing_Form_Labels Class
 *
 * Detects form inputs without labels.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Missing_Form_Labels extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'missing-form-labels';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Missing Form Labels';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects form inputs without labels';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if unlabeled forms, null otherwise.
	 */
	public static function check() {
		$form_analysis = self::analyze_forms();

		if ( ! $form_analysis['has_issue'] ) {
			return null; // Forms appear accessible
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Forms missing labels. Screen reader user hears "Edit text" with no context. Which field is name? Email? Phone? Impossible to complete form = lost conversions.', 'wpshadow' ),
			'severity'     => 'high',
			'threat_level' => 70,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/form-labels',
			'family'       => self::$family,
			'meta'         => array(
				'form_plugins_detected' => $form_analysis['plugins'],
			),
			'details'      => array(
				'why_labels_matter'           => array(
					__( 'Screen readers announce label text' ),
					__( 'Labels increase click target size' ),
					__( 'Cognitive disabilities: Need clear context' ),
					__( 'Mobile users: Easier to tap labels' ),
					__( 'WCAG 2.1 Level A requirement (3.3.2)' ),
				),
				'proper_label_patterns'       => array(
					'Explicit Association (Recommended)' => array(
						'<label for="email">Email Address</label>',
						'<input type="email" id="email" name="email">',
						'Benefits: Click label = focus input',
					),
					'Implicit Association' => array(
						'<label>Email Address',
						'  <input type="email" name="email">',
						'</label>',
						'Benefits: Simpler markup',
					),
					'ARIA Labels (Last Resort)' => array(
						'<input type="email" aria-label="Email Address">',
						'Use when: Visual label not possible',
						'Not recommended: Screen reader only',
					),
				),
				'fixing_contact_form_7'       => array(
					'Current Markup (Bad)' => array(
						'[text* your-name placeholder "Your Name"]',
						'No label = screen reader says "Edit required"',
					),
					'Fixed Markup (Good)' => array(
						'<label>Your Name',
						'  [text* your-name]',
						'</label>',
						'Screen reader: "Your Name, edit required"',
					),
					'Or Use aria-label' => array(
						'[text* your-name aria-label "Your Name"]',
						'Works but explicit labels better',
					),
				),
				'fixing_wpforms_gravity_forms' => array(
					'WPForms' => array(
						'Field Settings → Show Label',
						'Enable "Show Label" for all fields',
						'Automatically accessible',
					),
					'Gravity Forms' => array(
						'Field Settings → Advanced → Custom CSS Class',
						'Labels shown by default',
						'Verify in Form Settings → Accessibility',
					),
				),
				'testing_form_accessibility'  => array(
					'Screen Reader Test' => array(
						'Windows: NVDA (free)',
						'Mac: VoiceOver (built-in)',
						'Tab through form, listen to announcements',
					),
					'Automated Testing' => array(
						'WAVE browser extension',
						'axe DevTools',
						'Look for: "Missing form label" errors',
					),
					'Manual Inspection' => array(
						'Browser DevTools: Inspect inputs',
						'Check for: <label> or aria-label',
						'Verify: for/id association matches',
					),
				),
				'additional_form_requirements' => array(
					'Required Fields' => array(
						'Visual: * or "Required"',
						'Screen reader: aria-required="true"',
						'Or: <input required>',
					),
					'Error Messages' => array(
						'Associate with field: aria-describedby="error-id"',
						'Announce errors: role="alert"',
					),
					'Fieldset and Legend' => array(
						'Group related inputs: <fieldset>',
						'Describe group: <legend>Shipping Address</legend>',
					),
				),
			),
		);
	}

	/**
	 * Analyze forms (heuristic).
	 *
	 * @since  1.2601.2148
	 * @return array Form analysis.
	 */
	private static function analyze_forms() {
		// Check for form plugins
		$plugins = array();

		if ( is_plugin_active( 'contact-form-7/wp-contact-form-7.php' ) ) {
			$plugins[] = 'Contact Form 7';
		}

		if ( is_plugin_active( 'wpforms/wpforms.php' ) || is_plugin_active( 'wpforms-lite/wpforms.php' ) ) {
			$plugins[] = 'WPForms';
		}

		if ( class_exists( 'GFForms' ) ) {
			$plugins[] = 'Gravity Forms';
		}

		if ( is_plugin_active( 'ninja-forms/ninja-forms.php' ) ) {
			$plugins[] = 'Ninja Forms';
		}

		// Contact Form 7 known for accessibility issues in default config
		$has_issue = in_array( 'Contact Form 7', $plugins, true );

		return array(
			'has_issue' => $has_issue,
			'plugins'   => implode( ', ', $plugins ),
		);
	}
}
