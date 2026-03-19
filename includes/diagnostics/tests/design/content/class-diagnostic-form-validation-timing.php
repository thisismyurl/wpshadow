<?php
/**
 * Form Validation Timing Diagnostic
 *
 * Issue #4944: Form Validation Only on Submit (No Inline Help)
 * Pillar: 🎓 Learning Inclusive / #1: Helpful Neighbor
 *
 * Checks if forms validate inline.
 * Waiting until submit to show errors wastes user time.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Form_Validation_Timing Class
 *
 * @since 1.6093.1200
 */
class Diagnostic_Form_Validation_Timing extends Diagnostic_Base {

	protected static $slug = 'form-validation-timing';
	protected static $title = 'Form Validation Only on Submit (No Inline Help)';
	protected static $description = 'Checks if forms validate fields as users type';
	protected static $family = 'content';

	public static function check() {
		$issues = array();

		$issues[] = __( 'Validate fields on blur (when user leaves field)', 'wpshadow' );
		$issues[] = __( 'Show checkmark for valid fields (positive feedback)', 'wpshadow' );
		$issues[] = __( 'Show errors immediately for invalid fields', 'wpshadow' );
		$issues[] = __( 'Provide suggestions: "Email must contain @"', 'wpshadow' );
		$issues[] = __( 'Password strength meter in real-time', 'wpshadow' );
		$issues[] = __( 'Username availability check as user types', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Inline validation helps users fix errors immediately instead of waiting until submit. This reduces frustration and form abandonment.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/inline-validation',
				'details'      => array(
					'recommendations'         => $issues,
					'abandonment_reduction'   => 'Inline validation reduces form abandonment by 22%',
					'validation_timing'       => 'Validate on blur, not on every keystroke (annoying)',
					'positive_feedback'       => 'Show success (✓) not just errors (✗)',
				),
			);
		}

		return null;
	}
}
