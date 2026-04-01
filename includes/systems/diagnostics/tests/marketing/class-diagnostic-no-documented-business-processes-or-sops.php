<?php
/**
 * No Documented Business Processes or SOPs Diagnostic
 *
 * Checks if standard operating procedures are documented.
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
 * Business Processes Documentation Diagnostic
 *
 * Companies with documented processes scale faster.
 * Without documentation, every hire requires weeks of training.
 *
 * @since 0.6093.1200
 */
class Diagnostic_No_Documented_Business_Processes_Or_Sops extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'no-documented-business-processes-sops';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Documented Business Processes/SOPs';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if standard operating procedures are documented';

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
		if ( ! self::has_documented_processes() ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'No documented business processes (SOPs) detected. Companies with documented processes scale 3-5x faster. Without documentation: 1) Every hire takes weeks to train, 2) Quality is inconsistent (different people do things differently), 3) Knowledge is in someone\'s head (risky), 4) You can\'t delegate (bottleneck), 5) Onboarding takes forever. Document: 1) Sales process (lead → proposal → close), 2) Customer onboarding (signup → first value → success), 3) Support/service (ticket → resolution), 4) Product development (idea → release), 5) Finance (invoice → payment → reporting). Format: Write step-by-step. Screenshot each step. Show expected outcome. Update when process changes.', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/documented-business-processes?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'     => array(
					'issue'          => __( 'No documented business processes detected', 'wpshadow' ),
					'recommendation' => __( 'Document all key business processes with step-by-step SOPs', 'wpshadow' ),
					'business_impact' => __( 'Missing 3-5x faster scaling ability', 'wpshadow' ),
					'processes'      => self::get_critical_processes(),
				),
			);
		}

		return null;
	}

	/**
	 * Check if processes are documented.
	 *
	 * @since 0.6093.1200
	 * @return bool True if processes detected, false otherwise.
	 */
	private static function has_documented_processes() {
		$process_posts = self::count_posts_by_keywords(
			array(
				'process',
				'procedure',
				'SOP',
				'workflow',
				'step-by-step',
			)
		);

		return $process_posts > 0;
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
	 * Get critical processes to document.
	 *
	 * @since 0.6093.1200
	 * @return array Critical processes.
	 */
	private static function get_critical_processes() {
		return array(
			'sales'      => __( '1. Sales Process: Lead generation → qualification → proposal → negotiation → close (document each step, handoff, timeline)', 'wpshadow' ),
			'onboarding' => __( '2. Customer Onboarding: Signup → activation → training → success → upsell (docs, videos, checklist)', 'wpshadow' ),
			'support'    => __( '3. Support Process: Ticket → triage → resolution → followup (response times, escalation)', 'wpshadow' ),
			'product'    => __( '4. Product Development: Idea → roadmap → build → test → release (gates, reviews, approval)', 'wpshadow' ),
			'finance'    => __( '5. Finance: Invoice → send → followup → payment → reconcile → report (timeline, approval)', 'wpshadow' ),
			'hiring'     => __( '6. Hiring: Job post → screening → interview → offer → onboard (stages, who, timeline)', 'wpshadow' ),
			'marketing'  => __( '7. Marketing Campaign: Brief → create → review → schedule → publish → analyze (approval gates)', 'wpshadow' ),
		);
	}
}
