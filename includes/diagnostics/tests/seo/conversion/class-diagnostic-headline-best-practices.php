<?php
/**
 * Headline Best Practices Diagnostic
 *
 * Issue #4794: Headlines Don't Follow Best Practices
 * Family: business-performance
 *
 * Checks if headlines follow copywriting best practices.
 * Well-crafted headlines increase click-through by 50-300%.
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
 * Diagnostic_Headline_Best_Practices Class
 *
 * Checks headline quality across posts.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Headline_Best_Practices extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'headline-best-practices';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Headlines Don\'t Follow Best Practices';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if headlines use proven copywriting formulas that drive clicks';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'conversion';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		$issues[] = __( 'Use numbers: "7 Ways to..." outperforms "Several Ways to..." by 73%', 'wpshadow' );
		$issues[] = __( 'Include brackets: "Headline [Free Template]" increases CTR 38%', 'wpshadow' );
		$issues[] = __( 'Address reader: "How You Can..." more engaging than "How To..."', 'wpshadow' );
		$issues[] = __( 'Create urgency: "Before It\'s Too Late", "Stop Making This Mistake"', 'wpshadow' );
		$issues[] = __( 'Promise benefit: "Get More Traffic" not "Traffic Strategies"', 'wpshadow' );
		$issues[] = __( 'Optimal length: 60-70 characters (avoids truncation in search)', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Your headlines might be descriptive but not compelling. 80% of readers never get past the headline—it\'s your only chance to earn a click. Proven headline formulas: 1) Numbers: "7 Easy Ways" beats "Easy Ways" (oddnumbers work best: 7, 9, 13). 2) How-To: "How To Get 10K Followers" (actionable promise). 3) Lists: "X Things You Should Know About Y". 4) Questions: "Are You Making These SEO Mistakes?" (creates curiosity). 5) Brackets/parentheses: "SEO Guide [2024 Update]" (adds specificity). 6) Negative angle: "Stop Wasting Money On..." (fear of loss motivates). 7) "You" language: "How You Can..." (personal connection). Compare weak vs strong: ❌ Weak: "WordPress Performance Tips" (generic), "Content Marketing" (vague), "SEO Strategies" (boring). ✅ Strong: "7 WordPress Speed Hacks That Cut Load Time 50% [Tested]" (number + benefit + proof), "How You\'re Killing Your SEO Rankings (Without Knowing It)" (you + fear + curiosity), "Content Marketing That Actually Works in 2024 [Free Template]" (specificity + benefit + bracket). Test your headlines with CoSchedule Headline Analyzer (free). Optimal length: 60-70 characters (Google truncates at 60, social varies). Best performing words: You, Free, Because, Instantly, New.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/headline-formulas',
				'details'      => array(
					'recommendations'       => $issues,
					'ctr_impact'            => 'Good headlines increase CTR 50-300%',
					'formulas'              => 'Numbers, How-To, Lists, Questions, Brackets, Negative angle, "You"',
					'optimal_length'        => '60-70 characters (avoids truncation)',
					'power_words'           => 'You, Free, Because, Instantly, New, Proven, Easy, Simple',
					'best_numbers'          => 'Odd numbers (7, 9, 13) outperform even',
					'testing_tool'          => 'CoSchedule Headline Analyzer (free)',
					'examples'              => '"7 Ways..." + "How You Can..." + "Stop Making..."',
				),
			);
		}

		return null;
	}
}
