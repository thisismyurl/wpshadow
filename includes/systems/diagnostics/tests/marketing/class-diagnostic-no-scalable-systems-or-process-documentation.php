<?php
/**
 * No Scalable Systems or Process Documentation Diagnostic
 *
 * Checks if key business processes are documented and scalable.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\BusinessPerformance
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Scalable Systems & Documentation Diagnostic
 *
 * Detects when key business processes aren't documented or are bottlenecked
 * by people. Without documentation, scaling is impossible. Every time someone
 * leaves, knowledge walks out the door. Documented processes are trainable,
 * delegable, and scalable.
 *
 * @since 1.6093.1200
 */
class Diagnostic_No_Scalable_Systems_Or_Process_Documentation extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-scalable-systems-process-documentation';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Key Business Processes Documented & Scalable';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if key business processes are documented and scalable';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'marketing';

	/**
	 * Run the diagnostic check
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$documentation_level = self::check_documentation_completeness();

		if ( $documentation_level < 70 ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Key processes not documented or scalable. Your business is bottlenecked by people who know how things work. Without documentation, you can\'t scale, train, or delegate. When someone leaves, critical knowledge walks out the door. Document: 1) Customer acquisition process, 2) Customer onboarding, 3) Product/service delivery, 4) Support process, 5) Fulfillment/shipping, 6) Billing/invoicing. Use: Written guides + video walkthroughs + checklists.', 'wpshadow' ),
				'severity'    => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/scalable-systems-documentation',
				'details'     => array(
					'documentation_score'    => $documentation_level,
					'critical_processes'     => self::get_critical_processes(),
					'documentation_methods'  => self::get_documentation_methods(),
					'business_impact'        => 'Can\'t scale without documented processes, risk of knowledge loss',
					'recommendation'         => __( 'Document all critical business processes in next 30 days', 'wpshadow' ),
				),
			);
		}

		return null;
	}

	/**
	 * Check documentation completeness
	 *
	 * @since 1.6093.1200
	 * @return int Documentation score 0-100
	 */
	private static function check_documentation_completeness(): int {
		$score = 0;

		// Check for documentation pages
		$doc_pages = get_posts( array(
			'numberposts' => 20,
			's'           => 'process OR procedure OR documentation OR guide OR manual',
		) );

		if ( ! empty( $doc_pages ) ) {
			$score += 30;
		}

		// Check for video tutorials
		$has_videos = get_option( 'has_video_tutorials', false );

		if ( $has_videos ) {
			$score += 20;
		}

		// Check for knowledge base
		if ( post_type_exists( 'kb_article' ) || post_type_exists( 'knowledge_base' ) ) {
			$score += 20;
		}

		// Check for help/documentation plugins
		$plugins = get_plugins();

		foreach ( $plugins as $plugin_file => $plugin_data ) {
			$plugin_name = strtolower( $plugin_data['Name'] );

			if ( strpos( $plugin_name, 'knowledge' ) !== false || strpos( $plugin_name, 'documentation' ) !== false || strpos( $plugin_name, 'help' ) !== false ) {
				$score += 20;
			}
		}

		return min( 100, $score );
	}

	/**
	 * Get critical processes to document
	 *
	 * @since 1.6093.1200
	 * @return array Array of critical processes
	 */
	private static function get_critical_processes(): array {
		return array(
			array(
				'process'       => 'Customer Acquisition',
				'description'   => 'How you find, attract, and convert new customers',
				'metrics'       => 'CAC, conversion rate, lead volume',
				'failure_impact' => 'No growth, can\'t onboard new salespeople',
			),
			array(
				'process'       => 'Customer Onboarding',
				'description'   => 'How you get new customers successful quickly',
				'metrics'       => 'Time to first success, activation rate, churn',
				'failure_impact' => 'High churn, customer dissatisfaction',
			),
			array(
				'process'       => 'Product/Service Delivery',
				'description'   => 'How you deliver your core offering',
				'metrics'       => 'Quality, consistency, delivery time',
				'failure_impact' => 'Inconsistent delivery, customer complaints',
			),
			array(
				'process'       => 'Customer Support',
				'description'   => 'How you handle customer issues and questions',
				'metrics'       => 'Response time, resolution time, CSAT',
				'failure_impact' => 'Poor customer experience, churn',
			),
			array(
				'process'       => 'Billing & Invoicing',
				'description'   => 'How you collect payment and manage finances',
				'metrics'       => 'Accuracy, payment success rate',
				'failure_impact' => 'Lost revenue, customer disputes',
			),
			array(
				'process'       => 'Order Fulfillment',
				'description'   => 'How you fulfill and ship orders',
				'metrics'       => 'Accuracy, speed, cost',
				'failure_impact' => 'Wrong orders, delays, customer dissatisfaction',
			),
		);
	}

	/**
	 * Get documentation methods
	 *
	 * @since 1.6093.1200
	 * @return array Array of documentation methods
	 */
	private static function get_documentation_methods(): array {
		return array(
			'Written Guides' => 'Step-by-step text with screenshots, easy to search and update',
			'Video Tutorials' => 'Screen recordings + narration, easier to follow for visual learners',
			'Checklists'      => 'Simple checklists ensure nothing is skipped',
			'Flowcharts'      => 'Visual flowcharts show decision trees and process flow',
			'Templates'       => 'Pre-built templates remove decisions and speed up process',
			'FAQs'            => 'Common questions and answers prevent repeated explanations',
		);
	}
}
