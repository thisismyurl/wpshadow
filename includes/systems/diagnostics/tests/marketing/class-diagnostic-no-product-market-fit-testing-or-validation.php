<?php
/**
 * No Product-Market Fit Testing or Validation Diagnostic
 *
 * Checks if product-market fit has been validated.
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
 * Product-Market Fit Diagnostic
 *
 * Most startups fail because they build something people don't want.
 * Validate that customers actually need your solution.
 *
 * @since 1.6093.1200
 */
class Diagnostic_No_Product_Market_Fit_Testing_Or_Validation extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'no-product-market-fit-testing-validation';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Product-Market Fit Testing/Validation';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if product-market fit has been validated';

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
		if ( ! self::has_pmf_validation() ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'No product-market fit validation detected. Most products fail because they solve a problem nobody has. Before scaling, validate: 1) Do customers actually have this problem? 2) Are they actively looking for a solution? 3) Would they pay? 4) Do you have a better solution than alternatives? Test: Interview 10 customers (ask about their problems, don\'t pitch), launch MVP (minimum viable product), measure retention/satisfaction (NPS score, repeat purchases), get testimonials (real customers endorsing it). Signals of PMF: >40% would be "very disappointed" without your product, users beg for features, word-of-mouth growth. Focus: Find PMF before scaling marketing.', 'wpshadow' ),
				'severity'    => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/product-market-fit-testing',
				'details'     => array(
					'issue'               => __( 'No product-market fit validation detected', 'wpshadow' ),
					'recommendation'      => __( 'Validate product-market fit before scaling marketing', 'wpshadow' ),
					'business_impact'     => __( 'Risk of scaling marketing for solution nobody needs', 'wpshadow' ),
					'validation_methods'  => self::get_validation_methods(),
					'pmf_signals'         => self::get_pmf_signals(),
				),
			);
		}

		return null;
	}

	/**
	 * Check if PMF validation exists.
	 *
	 * @since 1.6093.1200
	 * @return bool True if validation detected, false otherwise.
	 */
	private static function has_pmf_validation() {
		// Check for PMF-related content
		$pmf_posts = self::count_posts_by_keywords(
			array(
				'product-market fit',
				'market validation',
				'customer research',
				'customer interviews',
				'product testing',
			)
		);

		return $pmf_posts > 0;
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
	 * Get validation methods.
	 *
	 * @since 1.6093.1200
	 * @return array Validation methods with descriptions.
	 */
	private static function get_validation_methods() {
		return array(
			'interviews'    => array(
				'method'  => __( 'Customer Interviews (10-20 conversations)', 'wpshadow' ),
				'ask'     => __( 'What problems are you trying to solve? Do you pay for solutions? What alternatives do you use?', 'wpshadow' ),
				'goal'    => __( 'Find common pain points, validate willingness to pay', 'wpshadow' ),
			),
			'mvp'           => array(
				'method'  => __( 'MVP Launch (Minimum Viable Product)', 'wpshadow' ),
				'ask'     => __( 'Do people actually use it? Do they come back? Will they pay?', 'wpshadow' ),
				'goal'    => __( 'Get real usage data, not just opinions', 'wpshadow' ),
			),
			'landing_page'  => array(
				'method'  => __( 'Landing Page Test (Drive traffic, measure conversion)', 'wpshadow' ),
				'ask'     => __( 'Will people sign up to hear more? How interested are they?', 'wpshadow' ),
				'goal'    => __( '>10% signup rate = strong interest, >3% = decent, <1% = no PMF', 'wpshadow' ),
			),
			'payment'       => array(
				'method'  => __( 'Payment Test (Ask for money early)', 'wpshadow' ),
				'ask'     => __( 'Will they actually pay? Not just say they would?', 'wpshadow' ),
				'goal'    => __( 'Get paying customers, not just interested people', 'wpshadow' ),
			),
		);
	}

	/**
	 * Get product-market fit signals.
	 *
	 * @since 1.6093.1200
	 * @return array PMF signals with thresholds.
	 */
	private static function get_pmf_signals() {
		return array(
			'disappointment' => array(
				'signal'    => __( '>40% would be "very disappointed" without your product', 'wpshadow' ),
				'meaning'   => __( 'Strong PMF signal', 'wpshadow' ),
				'benchmark' => __( '<40% = no PMF, >40% = strong PMF', 'wpshadow' ),
			),
			'retention'      => array(
				'signal'    => __( '>40% monthly active users (return regularly)', 'wpshadow' ),
				'meaning'   => __( 'People keep coming back', 'wpshadow' ),
				'benchmark' => __( '<5% retention = wrong product, >40% = PMF', 'wpshadow' ),
			),
			'recommendations' => array(
				'signal'    => __( 'Users spontaneously recommend to friends', 'wpshadow' ),
				'meaning'   => __( 'Word-of-mouth growth is most organic signal', 'wpshadow' ),
				'benchmark' => __( 'No referrals = wrong product, lots = PMF', 'wpshadow' ),
			),
			'feature_requests' => array(
				'signal'    => __( 'Users beg for features (not telling you to build them)', 'wpshadow' ),
				'meaning'   => __( 'They want more/better, not something different', 'wpshadow' ),
				'benchmark' => __( 'No requests = wrong product, many = PMF', 'wpshadow' ),
			),
			'nps_score'      => array(
				'signal'    => __( 'NPS > 50 (Likely to Recommend)', 'wpshadow' ),
				'meaning'   => __( 'Net Promoter Score of 50+ is very strong', 'wpshadow' ),
				'benchmark' => __( '<0 = negative, 0-30 = weak, 30-50 = good, >50 = exceptional', 'wpshadow' ),
			),
		);
	}
}
