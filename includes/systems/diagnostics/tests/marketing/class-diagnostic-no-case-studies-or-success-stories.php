<?php
/**
 * No Case Studies or Success Stories Diagnostic
 *
 * Checks if customer success stories and case studies are documented and featured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\BusinessPerformance
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Case Studies & Success Stories Diagnostic
 *
 * Detects when customer success stories aren't documented or prominently featured.
 * Case studies are 92% trusted by B2B buyers and increase conversion by 15-30%.
 * Without them, you're competing on features alone instead of proven results.
 *
 * @since 0.6093.1200
 */
class Diagnostic_No_Case_Studies_Or_Success_Stories extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-case-studies-success-stories';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Customer Case Studies & Success Stories Featured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if customer success stories and case studies are documented and prominently featured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'marketing';

	/**
	 * Run the diagnostic check
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$case_studies = self::count_case_studies();

		if ( $case_studies < 3 ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %d: number of case studies found */
					__( 'Only %d case studies found. Case studies are 92%% trusted by B2B buyers and increase conversion by 15-30%%. They\'re the most credible social proof. Create case studies showing: 1) Customer problem, 2) Your solution, 3) Measurable results (ROI, time saved, revenue increase). Aim for at least 5 case studies in different industries/use cases.', 'wpshadow' ),
					$case_studies
				),
				'severity'    => 'high',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/case-studies-success-stories?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'     => array(
					'case_studies_count' => $case_studies,
					'target_count'       => 5,
					'case_study_format'  => self::get_case_study_format(),
					'business_impact'    => '15-30% conversion increase, 92% buyer trust',
					'recommendation'     => __( 'Create case studies with measurable results from your best customers', 'wpshadow' ),
				),
			);
		}

		return null;
	}

	/**
	 * Count case studies on the site
	 *
	 * @since 0.6093.1200
	 * @return int Number of case studies found
	 */
	private static function count_case_studies(): int {
		// Look for case study posts/pages
		$case_studies = get_posts( array(
			'post_type'      => array( 'page', 'post' ),
			'numberposts'    => 20,
			's'              => 'case study OR success story OR customer story',
		) );

		// Also check for dedicated case study post type
		if ( post_type_exists( 'case-study' ) ) {
			$case_study_posts = get_posts( array(
				'post_type'   => 'case-study',
				'numberposts' => 20,
			) );

			return count( array_merge( $case_studies, $case_study_posts ) );
		}

		return count( $case_studies );
	}

	/**
	 * Get recommended case study format
	 *
	 * @since 0.6093.1200
	 * @return array Recommended case study structure
	 */
	private static function get_case_study_format(): array {
		return array(
			'Title'       => 'Company name + specific result ("Acme Inc.: 40% Revenue Increase in 6 Months")',
			'Introduction' => 'Who they are, their industry, size, what they were struggling with',
			'Challenge'   => '2-3 specific problems they faced before using your solution',
			'Solution'    => 'How you solved their problem, step by step',
			'Results'     => 'Specific, measurable outcomes (revenue, time saved, efficiency gains)',
			'Quote'       => 'Customer testimonial about the experience',
			'Stats Box'   => 'Key metrics highlighted: %s improvement, $ saved, hours freed up',
			'Takeaway'    => 'Key lessons readers can apply to their own situation',
		);
	}
}
