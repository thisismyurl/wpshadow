<?php
/**
 * No A/B Testing Program Diagnostic
 *
 * Checks if A/B testing program is in place.
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
 * A/B Testing Program Diagnostic
 *
 * Companies that run systematic A/B tests improve conversion rates by
 * 20-50% annually. Most growth comes from testing, not big ideas.
 *
 * @since 1.6093.1200
 */
class Diagnostic_No_Ab_Testing_Program extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'no-ab-testing-program';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'A/B Testing Program';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if A/B testing program is in place';

	/**
	 * Diagnostic family.
	 *
	 * @var string
	 */
	protected static $family = 'marketing';

	/**
	 * Run diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		if ( ! self::has_testing_program() ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'No A/B testing program detected. Companies running systematic tests improve conversion by 20-50% annually. Growth rarely comes from big ideas—it comes from small, validated improvements. System: 1) Test one variable at a time (subject line vs email copy), 2) 5% of traffic per test (need 80% statistical power), 3) Run for at least 1-2 weeks (account for day-of-week effects), 4) Document results (even failures teach you), 5) Roll winners into baseline, 6) Compound effects (1% improvements = 37x growth annually). Testing is how you scale sustainably.', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/ab-testing-program',
				'details'     => array(
					'issue'               => __( 'No systematic A/B testing program detected', 'wpshadow' ),
					'recommendation'      => __( 'Implement A/B testing program with test calendar and documentation', 'wpshadow' ),
					'business_impact'     => __( 'Missing 20-50% annual conversion improvement from systematic testing', 'wpshadow' ),
					'testing_areas'       => self::get_testing_areas(),
					'testing_methodology' => self::get_testing_methodology(),
				),
			);
		}

		return null;
	}

	/**
	 * Check if testing program exists.
	 *
	 * @since 1.6093.1200
	 * @return bool True if program detected, false otherwise.
	 */
	private static function has_testing_program() {
		// Check for testing-related content
		$testing_posts = self::count_posts_by_keywords(
			array(
				'ab test',
				'a/b test',
				'testing',
				'experiment',
				'split test',
				'multivariate test',
			)
		);

		if ( $testing_posts > 0 ) {
			return true;
		}

		// Check for A/B testing plugins
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$all_plugins = get_plugins();
		$testing_keywords = array(
			'ab test',
			'a/b test',
			'split test',
			'optimize',
			'experiment',
			'convert',
		);

		foreach ( $all_plugins as $plugin_file => $plugin_data ) {
			$plugin_name = strtolower( $plugin_data['Name'] );
			foreach ( $testing_keywords as $keyword ) {
				if ( false !== strpos( $plugin_name, $keyword ) ) {
					if ( is_plugin_active( $plugin_file ) ) {
						return true;
					}
				}
			}
		}

		return false;
	}

	/**
	 * Count posts containing specific keywords.
	 *
	 * @since 1.6093.1200
	 * @param  array $keywords Keywords to search for.
	 * @return int Number of matching posts.
	 */
	private static function count_posts_by_keywords( $keywords ) {
		$total = 0;

		foreach ( $keywords as $keyword ) {
			$posts = get_posts(
				array(
					's'              => $keyword,
					'posts_per_page' => 1,
					'post_type'      => array( 'post', 'page' ),
					'post_status'    => 'publish',
					'fields'         => 'ids',
				)
			);

			if ( ! empty( $posts ) ) {
				++$total;
			}
		}

		return $total;
	}

	/**
	 * Get testing areas.
	 *
	 * @since 1.6093.1200
	 * @return array Areas to test with examples.
	 */
	private static function get_testing_areas() {
		return array(
			'headlines'        => __( 'Headline A: "Save Time" vs B: "Work Less, Earn More"', 'wpshadow' ),
			'cta_copy'         => __( 'Button: "Sign Up Free" vs "Get Started Now"', 'wpshadow' ),
			'cta_color'        => __( 'Red vs Green vs Blue button (color psychology)', 'wpshadow' ),
			'image'            => __( 'Product image vs Customer testimonial vs Results screenshot', 'wpshadow' ),
			'offer'            => __( 'Free trial vs Money-back guarantee vs Discount', 'wpshadow' ),
			'form_fields'      => __( ' 3 fields vs 5 fields (longer form = lower conversion)', 'wpshadow' ),
			'email_subject'    => __( 'Curiosity gap vs Benefit vs Urgency', 'wpshadow' ),
			'email_length'     => __( 'Short (200 words) vs Long (500 words)', 'wpshadow' ),
			'layout'           => __( 'Single column vs Two column vs Hero section', 'wpshadow' ),
			'send_time'        => __( '9am send vs 7pm send (optimal timing)', 'wpshadow' ),
		);
	}

	/**
	 * Get A/B testing methodology.
	 *
	 * @since 1.6093.1200
	 * @return array Testing methodology steps.
	 */
	private static function get_testing_methodology() {
		return array(
			'hypothesis'       => __( '1. Form hypothesis: "Changing X will increase Y by Z%"', 'wpshadow' ),
			'baseline'         => __( '2. Measure baseline: Current conversion rate', 'wpshadow' ),
			'sample_size'      => __( '3. Calculate sample size: 5-10% of traffic to test', 'wpshadow' ),
			'randomization'    => __( '4. Randomize: Users see A or B randomly, 50/50', 'wpshadow' ),
			'duration'         => __( '5. Run minimum 1-2 weeks (account for day-of-week effects)', 'wpshadow' ),
			'statistical'      => __( '6. Check statistical significance: >95% confidence', 'wpshadow' ),
			'analyze'          => __( '7. Analyze results: Winning variation clear?', 'wpshadow' ),
			'document'         => __( '8. Document: Test name, winner, improvement %, insights', 'wpshadow' ),
			'rollout'          => __( '9. Roll out: Replace baseline with winner', 'wpshadow' ),
			'iterate'          => __( '10. Iterate: Find next test based on learnings', 'wpshadow' ),
		);
	}
}
