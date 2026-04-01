<?php
/**
 * No Customer Success or Account Management Diagnostic
 *
 * Checks if customer success/account management function exists.
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
 * Customer Success/Account Management Diagnostic
 *
 * Companies with dedicated customer success teams have 30% higher NPS
 * and 25% lower churn. Success is a function, not just support.
 *
 * @since 0.6093.1200
 */
class Diagnostic_No_Customer_Success_Or_Account_Management extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'no-customer-success-account-management';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Customer Success or Account Management Function';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if customer success or account management function exists';

	/**
	 * Diagnostic family.
	 *
	 * @var string
	 */
	protected static $family = 'marketing';

	/**
	 * Run diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		if ( ! self::has_cs_function() ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'No customer success function detected. You\'re waiting for customers to ask for help instead of proactively ensuring their success. Companies with CS teams have 30% higher NPS and 25% lower churn. Success isn\'t support—it\'s driving customer outcomes. Implement: 1) Onboarding specialist (first 30 days), 2) Success manager per customer (monthly check-ins), 3) Health scores (which customers are at risk), 4) Proactive outreach (before they churn), 5) Business reviews (quarterly: usage, ROI, roadmap), 6) Expansion focus (upsell opportunities). CS aligns incentives: their success is your success.', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/customer-success-account-management?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'     => array(
					'issue'               => __( 'No dedicated customer success function detected', 'wpshadow' ),
					'recommendation'      => __( 'Establish customer success team and proactive success processes', 'wpshadow' ),
					'business_impact'     => __( 'Missing 30% NPS improvement and 25% churn reduction', 'wpshadow' ),
					'cs_roles'            => self::get_cs_roles(),
					'success_activities'  => self::get_success_activities(),
				),
			);
		}

		return null;
	}

	/**
	 * Check if CS function exists.
	 *
	 * @since 0.6093.1200
	 * @return bool True if CS detected, false otherwise.
	 */
	private static function has_cs_function() {
		// Check for CS-related content
		$cs_posts = self::count_posts_by_keywords(
			array(
				'customer success',
				'account management',
				'customer health',
				'success manager',
				'customer champion',
			)
		);

		return $cs_posts > 0;
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
	 * Get customer success roles.
	 *
	 * @since 0.6093.1200
	 * @return array CS roles with descriptions.
	 */
	private static function get_cs_roles() {
		return array(
			'onboarding'      => __( 'Onboarding Specialist: Guides first 30 days, ensures quick value', 'wpshadow' ),
			'success_manager' => __( 'Success Manager: Monthly check-ins, identifies expansion, prevents churn', 'wpshadow' ),
			'support'         => __( 'Technical Support: Reactive problem-solving (different from CS)', 'wpshadow' ),
			'expansion'       => __( 'Expansion Manager: Upsell opportunities with existing customers', 'wpshadow' ),
			'analytics'       => __( 'Data Analyst: Health scores, churn prediction, usage patterns', 'wpshadow' ),
			'leadership'      => __( 'VP Customer Success: Strategy, team, metrics, P&L accountability', 'wpshadow' ),
		);
	}

	/**
	 * Get customer success activities.
	 *
	 * @since 0.6093.1200
	 * @return array CS activities with descriptions.
	 */
	private static function get_success_activities() {
		return array(
			'onboarding'        => __( 'Structured onboarding: first value in 7 days', 'wpshadow' ),
			'health_score'      => __( 'Customer health score (green/yellow/red based on usage)', 'wpshadow' ),
			'check_ins'         => __( 'Monthly/quarterly success check-in calls', 'wpshadow' ),
			'proactive_outreach' => __( 'Reach out to at-risk customers before they churn', 'wpshadow' ),
			'training'          => __( 'Provide training on features they\'re not using', 'wpshadow' ),
			'business_reviews'  => __( 'Quarterly: ROI, usage, roadmap, expansion opportunities', 'wpshadow' ),
			'community'         => __( 'Invite to customer advisory board, user group', 'wpshadow' ),
			'expansion'         => __( 'Identify upsell opportunities based on usage', 'wpshadow' ),
		);
	}
}
