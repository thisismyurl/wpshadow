<?php
/**
 * No Risk Management or Crisis Plan Diagnostic
 *
 * Checks if risk management and crisis planning exist.
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
 * Risk Management Diagnostic
 *
 * The best time to prepare for crisis is before it happens.
 * Hope for the best, prepare for the worst.
 *
 * @since 0.6093.1200
 */
class Diagnostic_No_Risk_Management_Or_Crisis_Plan extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'no-risk-management-crisis-plan';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Risk Management/Crisis Plan';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if risk management and crisis planning exist';

	/**
	 * Diagnostic family.
	 *
	 * @var string
	 */
	protected static $family = 'business-performance';

	/**
	 * Run diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		if ( ! self::has_risk_management() ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'No risk management or crisis plan detected. The best time to prepare for crisis is before it happens. Identify risks: 1) Financial (cash shortage, bad debt), 2) Operational (key person dependency, supplier risk), 3) Market (competitor disruption, losing customers), 4) Reputational (product recall, scandal), 5) Legal (lawsuit, regulatory). For each: likelihood (low/medium/high), impact (low/medium/high), mitigation plan. Example: Key person risk (high impact, medium likelihood) → Cross-train backup, document knowledge. Crisis plan: Who decides? Communication plan? Escalation path? Response timeline? Insurance coverage reviewed?', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/risk-management-crisis-plan?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'     => array(
					'issue'          => __( 'No risk management or crisis plan detected', 'wpshadow' ),
					'recommendation' => __( 'Implement risk management and crisis response planning', 'wpshadow' ),
					'business_impact' => __( 'Unpreparedness for inevitable business disruptions', 'wpshadow' ),
					'risk_categories' => self::get_risk_categories(),
				),
			);
		}

		return null;
	}

	/**
	 * Check if risk management exists.
	 *
	 * @since 0.6093.1200
	 * @return bool True if risk management detected, false otherwise.
	 */
	private static function has_risk_management() {
		$risk_posts = self::count_posts_by_keywords(
			array(
				'risk',
				'crisis',
				'contingency',
				'backup',
				'disaster',
			)
		);

		return $risk_posts > 0;
	}

	/**
	 * Count posts containing specific keywords.
	 *
	 * @since 0.6093.1200
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
	 * Get risk categories.
	 *
	 * @since 0.6093.1200
	 * @return array Risk categories and mitigation.
	 */
	private static function get_risk_categories() {
		return array(
			'financial'  => array(
				'category'    => __( 'Financial Risk', 'wpshadow' ),
				'examples'    => __( 'Cash shortage, bad debt, economic downturn, supplier price hike', 'wpshadow' ),
				'mitigation'  => __( 'Emergency fund (3-6 months expenses), diversified customer base, insurance', 'wpshadow' ),
			),
			'operational' => array(
				'category'    => __( 'Operational Risk', 'wpshadow' ),
				'examples'    => __( 'Key person dependency, supplier outage, system failure, natural disaster', 'wpshadow' ),
				'mitigation'  => __( 'Cross-train team, document knowledge, backup suppliers, disaster recovery plan', 'wpshadow' ),
			),
			'market'     => array(
				'category'    => __( 'Market Risk', 'wpshadow' ),
				'examples'    => __( 'Competitor disruption, customer churn, market shift, technology change', 'wpshadow' ),
				'mitigation'  => __( 'Monitor competitors, stay close to customers, continuous innovation, agile planning', 'wpshadow' ),
			),
			'reputational' => array(
				'category'    => __( 'Reputational Risk', 'wpshadow' ),
				'examples'    => __( 'Product recall, security breach, customer scandal, executive misconduct', 'wpshadow' ),
				'mitigation'  => __( 'Quality assurance, security testing, crisis communication plan, values alignment', 'wpshadow' ),
			),
			'legal'      => array(
				'category'    => __( 'Legal/Regulatory Risk', 'wpshadow' ),
				'examples'    => __( 'Lawsuit, regulatory change, compliance violation, intellectual property dispute', 'wpshadow' ),
				'mitigation'  => __( 'Legal review, compliance monitoring, insurance coverage, professional advisors', 'wpshadow' ),
			),
		);
	}
}
