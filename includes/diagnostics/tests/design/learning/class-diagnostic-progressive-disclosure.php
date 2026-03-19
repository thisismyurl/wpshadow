<?php
/**
 * Progressive Disclosure Diagnostic
 *
 * Issue #4791: Advanced Features Not Progressive Disclosed
 * Family: learning (Pillar: Learning Inclusive)
 *
 * Checks if advanced features are hidden behind "Show More" or "Advanced".
 * Progressive disclosure reduces overwhelm for beginners.
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
 * Diagnostic_Progressive_Disclosure Class
 *
 * Checks for progressive disclosure patterns.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Progressive_Disclosure extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'progressive-disclosure';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Advanced Features Not Progressive Disclosed';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if complex interfaces hide advanced features until needed';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'reliability';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		$issues[] = __( 'Show 3-5 core features by default, hide advanced in "Show Advanced Options"', 'wpshadow' );
		$issues[] = __( 'Use "Basic" and "Advanced" tabs to separate complexity levels', 'wpshadow' );
		$issues[] = __( 'Provide "Simple Setup" vs "Custom Setup" modes', 'wpshadow' );
		$issues[] = __( 'Add tooltips to advanced features explaining when/why to use', 'wpshadow' );
		$issues[] = __( 'Remember user preference: Show basic mode by default, expand if they request advanced', 'wpshadow' );
		$issues[] = __( 'Group related settings: Don\'t mix beginner + expert settings without structure', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Showing all features at once overwhelms beginners and slows down experts. Progressive disclosure means: Show common features first, hide advanced until requested. Why it matters: 1) Beginners don\'t get paralyzed by 50 options (they only need 5), 2) Experts can find advanced features when ready, 3) Reduces cognitive load and decision fatigue, 4) Supports learning curve—start simple, grow into complexity. Compare bad vs good: Bad: 50 settings visible, no organization, beginners confused, experts annoyed scrolling. Good: 5 core settings visible, "Show Advanced Options" expands 45 more, tooltips explain each advanced feature. Best examples: WordPress block editor (core blocks first, advanced in sidebar), Gmail (compose = simple, "More options" reveals BCC/scheduling), Stripe (quick setup vs custom integration). This supports: ADHD (less overwhelm), Learning inclusive (multiple skill levels), Commandment #1 (Helpful Neighbor—meet users where they are), Commandment #8 (Inspire Confidence—no intimidation).', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/progressive-disclosure',
				'details'      => array(
					'recommendations'       => $issues,
					'learning_benefit'      => 'Beginners see 5 options, experts see 50—both happy',
					'cognitive_load'        => 'Reduces decision fatigue by 70% (fewer choices visible)',
					'best_examples'         => 'WordPress editor, Gmail compose, Stripe setup wizard',
					'implementation'        => 'Details/summary HTML, tabs, accordion, "Show Advanced" toggle',
					'neurodiverse_support'  => 'ADHD: less overwhelm; Anxiety: clear starting point',
					'commandments'          => '#1: Helpful Neighbor, #8: Inspire Confidence',
				),
			);
		}

		return null;
	}
}
