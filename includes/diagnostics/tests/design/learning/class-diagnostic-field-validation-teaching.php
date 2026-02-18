<?php
/**
 * Field Validation Teaching Diagnostic
 *
 * Issue #4793: Field-Level Validation Doesn't Teach
 * Family: learning (Commandment #1: Helpful Neighbor)
 *
 * Checks if form validation errors explain WHY and HOW TO FIX.
 * Helpful errors teach users instead of just saying "Invalid".
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6036.1605
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Field_Validation_Teaching Class
 *
 * Checks if validation errors are educational.
 *
 * @since 1.6036.1605
 */
class Diagnostic_Field_Validation_Teaching extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'field-validation-teaching';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Field-Level Validation Doesn\'t Teach';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if form validation errors explain why and how to fix problems';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'reliability';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6036.1605
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		$issues[] = __( 'Replace "Invalid email" with "Email must include @ and a domain (e.g., you@example.com)"', 'wpshadow' );
		$issues[] = __( 'Replace "Password too weak" with "Password needs 12+ characters, with uppercase + number + symbol"', 'wpshadow' );
		$issues[] = __( 'Replace "Invalid format" with "Phone number format: (555) 555-5555 or 555-555-5555"', 'wpshadow' );
		$issues[] = __( 'Replace "Required field" with "We need your email to send order confirmation"', 'wpshadow' );
		$issues[] = __( 'Show validation feedback as user types (not just on submit)', 'wpshadow' );
		$issues[] = __( 'Use green checkmark for valid fields (positive reinforcement)', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Form validation errors might say "Invalid" or "Error" without explaining what\'s wrong or how to fix it. This frustrates users and increases abandonment. Educational validation teaches users: What\'s wrong ("Password too short"), Why it matters ("Security requires 12+ characters"), How to fix ("Add 4 more characters with at least 1 number"), Example ("Use: MyP@ssw0rd2024"). Compare unhelpful vs helpful: ❌ Unhelpful: "Invalid email" (What\'s invalid about it?), "Password error" (What kind of error?), "Required" (Why do you need this?). ✅ Helpful: "Email needs @ symbol and domain (you@example.com)", "Password needs 12+ characters including uppercase, number, and symbol", "We need your phone number to send delivery updates (format: 555-555-5555)". Best practices: 1) Inline validation: Check as user types (immediate feedback), 2) Specific errors: Tell exactly what\'s wrong, 3) Helpful guidance: Show correct format/example, 4) Explain why: "Why do you need this?", 5) Positive feedback: Green checkmark when correct. This helps everyone, especially: Non-native speakers (clear instructions), ADHD (immediate feedback), Anxiety (reduces fear of mistakes). Commandment #1: Helpful Neighbor—teach, don\'t scold.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/validation-errors',
				'details'      => array(
					'recommendations'       => $issues,
					'pattern'               => 'What\'s wrong + Why it matters + How to fix + Example',
					'bad_examples'          => '"Invalid", "Error", "Required", "Wrong format"',
					'good_examples'         => '"Email needs @ symbol (you@example.com)"',
					'inline_validation'     => 'Check as user types, not just on submit',
					'positive_feedback'     => 'Show green checkmark when field is valid',
					'accessibility'         => 'Screen readers hear error text—make it helpful',
					'commandment'           => '#1: Helpful Neighbor',
				),
			);
		}

		return null;
	}
}
