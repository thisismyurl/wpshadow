<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Is Contact Form Working?
 *
 * Target Persona: Non-technical Site Owner (Mom/Dad)
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
 */
class Diagnostic_Contact_Form_Working extends Diagnostic_Base {
	protected static $slug        = 'contact-form-working';
	protected static $title       = 'Is Contact Form Working?';
	protected static $description = 'Tests if contact form submissions reach you.';

	public static function check(): ?array {
		$contact_plugins = array(
			'contact-form-7/wp-contact-form-7.php' => 'Contact Form 7',
			'wpforms-lite/wpforms.php'             => 'WPForms',
			'ninja-forms/ninja-forms.php'          => 'Ninja Forms',
			'formidable/formidable.php'            => 'Formidable Forms',
			'gravityforms/gravityforms.php'        => 'Gravity Forms',
		);

		$active_forms = array();
		foreach ( $contact_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_forms[] = $name;
			}
		}

		if ( ! empty( $active_forms ) ) {
			return null;
		}

		return array(
			'id'            => static::$slug,
			'title'         => __( 'No contact form plugin detected', 'wpshadow' ),
			'description'   => __( 'Visitors cannot reach you. Install Contact Form 7 or similar to let people contact you.', 'wpshadow' ),
			'severity'      => 'medium',
			'category'      => 'general',
			'kb_link'       => 'https://wpshadow.com/kb/contact-form-working/',
			'training_link' => 'https://wpshadow.com/training/contact-form-working/',
			'auto_fixable'  => false,
			'threat_level'  => 60,
		);
	}

	/**
	 * IMPLEMENTATION PLAN (Non-technical Site Owner (Mom/Dad))
	 *
	 * What This Checks:
	 * - [Technical implementation details]
	 *
	 * Why It Matters:
	 * - [Business value in plain English]
	 *
	 * Success Criteria:
	 * - [What "passing" means]
	 *
	 * How to Fix:
	 * - Step 1: [Clear instruction]
	 * - Step 2: [Next step]
	 * - KB Article: Detailed explanation and examples
	 * - Training Video: Visual walkthrough
	 *
	 * KPIs Tracked:
	 * - Issues found and fixed
	 * - Time saved (estimated minutes)
	 * - Site health improvement %
	 * - Business value delivered ($)
	 */
}
