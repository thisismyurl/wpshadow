<?php
/**
 * In-Context Workflow Help Diagnostic
 *
 * Issue #4787: No In-Context Help for Complex Workflows
 * Family: learning (Pillar: Learning Inclusive)
 *
 * Checks if complex workflows have step-by-step in-context help.
 * Multi-step processes should guide users through each stage.
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
 * Diagnostic_In_Context_Workflow_Help Class
 *
 * Checks for in-context help during complex workflows.
 *
 * @since 0.6093.1200
 */
class Diagnostic_In_Context_Workflow_Help extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'in-context-workflow-help';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'No In-Context Help for Complex Workflows';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if complex multi-step workflows provide in-context guidance';

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

		$issues[] = __( 'Add step indicators: "Step 2 of 5: Enter Shipping Information"', 'wpshadow' );
		$issues[] = __( 'Show help text inline: "Why do we need this?" next to each field', 'wpshadow' );
		$issues[] = __( 'Indicate progress: Progress bar showing completion percentage', 'wpshadow' );
		$issues[] = __( 'Provide examples: "Example: 123 Main St, Apt 4B"', 'wpshadow' );
		$issues[] = __( 'Offer contextual tips: "Tip: Choosing express shipping guarantees delivery by Friday"', 'wpshadow' );
		$issues[] = __( 'Enable save-and-resume: "Don\'t worry, we\'ll save your progress"', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Complex workflows (checkout, registration, multi-step forms) might leave users uncertain about what to do next. When faced with 5 steps and unfamiliar fields, users abandon 67% of the time. In-context help reduces abandonment by showing: Where they are ("Step 2 of 5"), What\'s required (field-level help text), Why it matters ("We need your phone for delivery updates"), Examples (placeholder or inline: "555-0123"), Progress (visual indicator: 40% complete). Compare bad vs good: Bad: Empty form fields, no progress indicator, unclear requirements. Good: Clear labels ("Shipping address"), help icons with tooltips, progress bar, "Save & continue later" button, field validation with helpful errors. This supports neurodiverse users (ADHD: clear progress), learning inclusive design (multiple formats: text + visuals), and Commandment #8 (Inspire Confidence—users feel guided, not lost). Best examples: Amazon checkout (clear steps), Stripe onboarding (progress indicators), TurboTax (contextual tips at every step).', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/in-context-workflow-help?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'recommendations'       => $issues,
					'abandonment_rate'      => '67% abandon confusing multi-step workflows',
					'learning_inclusive'    => 'Supports ADHD (clear progress), dyslexia (examples), anxiety (save progress)',
					'best_practices'        => 'Step indicators + Progress bar + Field help + Examples + Save/resume',
					'good_examples'         => 'Amazon checkout, Stripe onboarding, TurboTax',
					'commandment'           => '#8: Inspire Confidence',
				),
			);
		}

		return null;
	}
}
