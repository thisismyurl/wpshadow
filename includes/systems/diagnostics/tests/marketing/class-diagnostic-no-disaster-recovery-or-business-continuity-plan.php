<?php
/**
 * No Disaster Recovery or Business Continuity Plan Diagnostic
 *
 * Checks if disaster recovery plan exists.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Disaster Recovery Plan Diagnostic
 *
 * The question isn't "if" a disaster will happen.
 * It's "are you prepared?"
 *
 * @since 1.6035.0000
 */
class Diagnostic_No_Disaster_Recovery_Or_Business_Continuity_Plan extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'no-disaster-recovery-business-continuity-plan';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Disaster Recovery/Business Continuity Plan';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if disaster recovery plan exists';

	/**
	 * Diagnostic family.
	 *
	 * @var string
	 */
	protected static $family = 'business-performance';

	/**
	 * Run diagnostic check.
	 *
	 * @since  1.6035.0000
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		if ( ! self::has_dr_plan() ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'No disaster recovery plan detected. The question isn\'t "if" disaster happens, it\'s "are you prepared?" Define: 1) RPO (Recovery Point Objective - how much data loss acceptable? 1 hour? 1 day?), 2) RTO (Recovery Time Objective - how fast must you recover? 1 hour? 4 hours?), 3) Critical systems (if X fails, business stops?), 4) Backup strategy (daily backups? offsite? tested?), 5) Failover plan (what if primary data center goes down?), 6) Communication plan (how do you notify customers?), 7) Test schedule (drill quarterly). Scenarios: Data center fire, ransomware, key employee unavailable, supplier outage. Test: Restore from backup. Can you? How long? Unplanned downtime costs $300k-$500k per hour for large companies.', 'wpshadow' ),
				'severity'    => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/disaster-recovery-plan',
				'details'     => array(
					'issue'          => __( 'No disaster recovery plan detected', 'wpshadow' ),
					'recommendation' => __( 'Implement comprehensive disaster recovery and continuity plan', 'wpshadow' ),
					'business_impact' => __( '$300k-$500k per hour cost of unplanned downtime', 'wpshadow' ),
					'plan_components'  => self::get_plan_components(),
				),
			);
		}

		return null;
	}

	/**
	 * Check if DR plan exists.
	 *
	 * @since  1.6035.0000
	 * @return bool True if plan detected, false otherwise.
	 */
	private static function has_dr_plan() {
		$dr_posts = self::count_posts_by_keywords(
			array(
				'disaster recovery',
				'business continuity',
				'backup',
				'failover',
				'recovery plan',
			)
		);

		return $dr_posts > 0;
	}

	/**
	 * Count posts containing specific keywords.
	 *
	 * @since  1.6035.0000
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
	 * Get DR plan components.
	 *
	 * @since  1.6035.0000
	 * @return array DR plan components.
	 */
	private static function get_plan_components() {
		return array(
			'rpo'         => __( 'RPO (Recovery Point Objective): Maximum acceptable data loss? (1 hour, 1 day)', 'wpshadow' ),
			'rto'         => __( 'RTO (Recovery Time Objective): Maximum acceptable downtime? (1 hour, 4 hours)', 'wpshadow' ),
			'critical'    => __( 'Critical Systems: What MUST stay up? (production database, website, payment system)', 'wpshadow' ),
			'backup'      => __( 'Backup Strategy: Daily full backups, offsite redundancy, tested restoration (quarterly)', 'wpshadow' ),
			'failover'    => __( 'Failover Plan: If primary fails, switch to backup (DNS failover, load balancer)', 'wpshadow' ),
			'communication' => __( 'Communication: How notify customers? (status page, email, SMS)', 'wpshadow' ),
			'contacts'    => __( 'Emergency Contacts: Who to call when disaster hits? (on-call rotation)', 'wpshadow' ),
			'testing'     => __( 'Testing: Quarterly DR drill (simulate outage, measure recovery time)', 'wpshadow' ),
		);
	}
}
