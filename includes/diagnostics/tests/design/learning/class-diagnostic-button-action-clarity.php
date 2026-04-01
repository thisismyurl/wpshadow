<?php
/**
 * Button Action Clarity Diagnostic
 *
 * Issue #4789: Buttons Don't Explain What Happens Next
 * Family: learning (Commandment #8: Inspire Confidence)
 *
 * Checks if button labels clearly explain what will happen.
 * Vague buttons like "Submit" or "Continue" cause uncertainty.
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
 * Diagnostic_Button_Action_Clarity Class
 *
 * Checks if button labels are specific and clear.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Button_Action_Clarity extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'button-action-clarity';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Buttons Don\'t Explain What Happens Next';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if buttons use clear, specific action labels instead of generic terms';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'reliability';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		$issues[] = __( 'Replace "Submit" with specific action: "Create Account", "Download Guide", "Send Message"', 'wpshadow' );
		$issues[] = __( 'Replace "Continue" with clear next step: "Continue to Payment", "Continue to Shipping"', 'wpshadow' );
		$issues[] = __( 'Replace "OK" with specific outcome: "Save Changes", "Apply Discount", "Confirm Order"', 'wpshadow' );
		$issues[] = __( 'Replace "Click Here" with benefit: "Start Free Trial", "See Pricing Plans"', 'wpshadow' );
		$issues[] = __( 'Add context to "Delete": "Delete Comment", "Remove Item from Cart"', 'wpshadow' );
		$issues[] = __( 'Use action verbs: "Download", "Subscribe", "Register", "Purchase"', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Your buttons might say "Submit" or "Continue" without explaining what actually happens when clicked. This creates uncertainty—users hesitate because they don\'t know: What will happen? Where will I go? Will this cost money? Can I undo it? Compare vague vs clear button labels: ❌ Vague: "Submit" (Submit what? To where?), "Continue" (Continue to what?), "OK" (OK to what?), "Click Here" (Why?). ✅ Clear: "Create Account" (I know what happens), "Continue to Payment" (I know what\'s next), "Save Changes" (I know action + scope), "Start Free Trial" (I know benefit + commitment). Button clarity reduces anxiety by: Eliminating uncertainty, Setting expectations, Confirming action safety, Building confidence. This especially helps users with: Anxiety (fear of wrong click), ADHD (need clear outcomes), Non-native speakers (context matters), Screen reader users (hear button text out of context). Best practice formula: [Action Verb] + [Specific Object] + [Optional Benefit]. Examples: "Download Free Guide", "Subscribe to Newsletter", "Remove from Cart", "Confirm Booking". This aligns with Commandment #8: Inspire Confidence—users should feel empowered, never confused.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/button-action-clarity?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'recommendations'       => $issues,
					'formula'               => '[Action Verb] + [Specific Object] + [Optional Benefit]',
					'bad_examples'          => '"Submit", "Continue", "OK", "Click Here"',
					'good_examples'         => '"Create Account", "Continue to Payment", "Download Free Guide"',
					'user_benefit'          => 'Reduces anxiety, sets expectations, builds confidence',
					'accessibility'         => 'Screen readers hear button text out of context—clarity critical',
					'commandment'           => '#8: Inspire Confidence',
				),
			);
		}

		return null;
	}
}
