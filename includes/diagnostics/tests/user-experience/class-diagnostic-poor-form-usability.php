<?php
/**
 * Poor Form Usability Diagnostic
 *
 * Identifies form design issues like too many fields, missing labels,
 * unclear error messages that reduce form completion rates.
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
 * Diagnostic_Poor_Form_Usability Class
 *
 * Detects form usability issues.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Poor_Form_Usability extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'poor-form-usability';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Poor Form Usability';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Identifies form design issues reducing completion';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'user-experience';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function check() {
		$form_issues = self::analyze_forms();

		if ( empty( $form_issues['problems'] ) ) {
			return null; // Forms look good
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Form usability issues detected. Poor forms lose 67% of users mid-completion. Each unnecessary field = 10% drop-off.', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 55,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/form-optimization',
			'family'       => self::$family,
			'meta'         => array(
				'problems_found'    => $form_issues['problems'],
				'abandonment_rate'  => __( '67% abandon poorly designed forms' ),
				'field_impact'      => __( 'Each extra field = 10% drop-off' ),
			),
			'details'      => array(
				'why_form_usability_matters' => array(
					__( 'Conversion impact: Forms are conversion points' ),
					__( 'Abandonment: 67% abandon if form too complex' ),
					__( 'Mobile difficulty: Small screens + complex forms = frustration' ),
					__( 'Trust: Professional forms = credible business' ),
				),
				'common_form_problems'   => array(
					'Too Many Fields' => array(
						'Problem: Asking for unnecessary information',
						'Impact: Each extra field = 10% drop-off',
						'Fix: Keep to 3-5 essential fields only',
					),
					'No Field Labels' => array(
						'Problem: Placeholder text disappears when typing',
						'Impact: User forgets what field is for',
						'Fix: Always use <label> tags above fields',
					),
					'Unclear Error Messages' => array(
						'Problem: "Error: Invalid input"',
						'Impact: User doesn\'t know how to fix',
						'Fix: "Email must include @ symbol"',
					),
					'Small Touch Targets' => array(
						'Problem: Input fields <48px tall on mobile',
						'Impact: Hard to tap, frustrating',
						'Fix: min-height: 48px for mobile',
					),
					'No Progress Indicators' => array(
						'Problem: Multi-step form without progress bar',
						'Impact: User doesn\'t know how long',
						'Fix: Show "Step 2 of 4" or progress bar',
					),
				),
				'form_optimization_checklist' => array(
					'Field Count' => array(
						'Minimum viable fields only',
						'Contact form: Name, Email, Message (3 fields)',
						'Each extra field = 10% drop-off',
					),
					'Labels' => array(
						'<label> tags for every field',
						'Labels above fields (not placeholder)',
						'Clear, specific label text',
					),
					'Error Messages' => array(
						'Inline validation (real-time feedback)',
						'Specific: "Password needs 8+ characters"',
						'Red highlighting of error fields',
					),
					'Mobile Optimization' => array(
						'Input height: 48px minimum',
						'Tap targets: 48x48px minimum',
						'Numeric keyboard for phone/zip',
						'Auto-focus first field',
					),
					'Progress Indicators' => array(
						'Multi-step: Show step number',
						'Progress bar if >3 steps',
						'Save progress option for long forms',
					),
				),
				'form_plugins_recommendations' => array(
					'Contact Form 7 (Free)' => array(
						'Most popular form plugin',
						'Flexible, customizable',
						'Requires manual optimization',
					),
					'WPForms (Free + Pro)' => array(
						'Drag-and-drop builder',
						'Mobile-optimized by default',
						'Conditional logic (Pro)',
					),
					'Gravity Forms (Premium $59/yr)' => array(
						'Advanced features',
						'Multi-step forms',
						'Save & continue functionality',
					),
					'Typeform (External $29/mo)' => array(
						'Beautiful one-question-at-a-time',
						'Highest completion rates',
						'Embed on WordPress',
					),
				),
				'form_ux_best_practices' => array(
					__( 'Keep forms short (3-5 fields ideal)' ),
					__( 'Clear labels above fields' ),
					__( 'Large touch targets (48x48px)' ),
					__( 'Real-time validation' ),
					__( 'Specific error messages' ),
					__( 'Progress indicators for multi-step' ),
					__( 'Auto-save for long forms' ),
					__( 'Thank you message after submit' ),
				),
			),
		);
	}

	/**
	 * Analyze form usability.
	 *
	 * @since  1.2601.2148
	 * @return array Form analysis results.
	 */
	private static function analyze_forms() {
		$problems = array();

		// Check for common form plugins
		$has_modern_forms = is_plugin_active( 'wpforms-lite/wpforms.php' ) ||
						is_plugin_active( 'wpforms/wpforms.php' ) ||
						is_plugin_active( 'gravityforms/gravityforms.php' );

		// Check for basic Contact Form 7 (needs optimization usually)
		$has_cf7 = is_plugin_active( 'contact-form-7/wp-contact-form-7.php' );

		if ( $has_cf7 && ! $has_modern_forms ) {
			$problems[] = __( 'Contact Form 7 active but may need usability optimization', 'wpshadow' );
		}

		// Check if WooCommerce checkout exists (often needs optimization)
		if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			// WooCommerce checkout often has too many fields
			$problems[] = __( 'WooCommerce checkout may have too many fields', 'wpshadow' );
		}

		return array(
			'problems' => $problems,
		);
	}
}
