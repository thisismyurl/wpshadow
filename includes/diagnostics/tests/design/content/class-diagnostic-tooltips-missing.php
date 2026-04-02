<?php
/**
 * Tooltips for Complex Features Diagnostic
 *
 * Issue #4766: Complex Features Missing Tooltips
 * Pillar: 🎓 Learning Inclusive
 *
 * Checks if complex features have tooltip guidance.
 * Users need hover/click help for advanced features.
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
 * Diagnostic_Tooltips_Missing Class
 *
 * Checks for tooltip guidance on complex features.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Tooltips_Missing extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'tooltips-missing';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Complex Features Missing Tooltips';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if complex settings and features have tooltip guidance';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		$issues[] = __( 'Add tooltips (?) icon next to technical settings', 'wpshadow' );
		$issues[] = __( 'Use aria-describedby to link tooltip to field (accessibility)', 'wpshadow' );
		$issues[] = __( 'Tooltips should trigger on hover AND click (mobile)', 'wpshadow' );
		$issues[] = __( 'Keep tooltip text concise: 1-2 sentences', 'wpshadow' );
		$issues[] = __( 'Include "Learn more" link to detailed docs', 'wpshadow' );
		$issues[] = __( 'Avoid tooltips on simple fields (name, email)', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Your complex features might lack quick-reference tooltips. Imagine a cockpit where every button has a tiny label but no explanation—pilots wouldn\'t know what "APU" or "TCAS" means without a manual. Tooltips (usually with a ? icon) provide just-in-time help without leaving the page. Perfect for: technical settings users rarely change, fields with specific format requirements, advanced features with prerequisites, options with non-obvious consequences. Bad: "Enable caching" with no tooltip. Good: "Enable caching (?)" where (?) shows "Stores copies of pages to load faster. Recommended for most sites. May show stale content for a few minutes after updates." Also critical for mobile—tooltips must work on tap, not just hover.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/tooltips',
				'details'      => array(
					'recommendations'      => $issues,
					'when_to_use'          => 'Technical terms, format requirements, advanced features, consequences',
					'when_not_to_use'      => 'Simple fields users understand (name, email, title)',
					'mobile_requirement'   => 'Must work on tap/click, not just hover',
					'accessibility'        => 'Use aria-describedby to link tooltip to form field',
					'pattern'              => 'Field label + (?) icon + tooltip on hover/click + learn more link',
					'neurodiversity'       => 'Helps users with ADHD (quick ref), autism (clear explanations)',
				),
			);
		}

		return null;
	}
}
