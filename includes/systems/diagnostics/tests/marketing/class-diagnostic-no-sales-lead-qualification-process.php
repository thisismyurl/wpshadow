<?php
/**
 * No Sales/Lead Qualification Process Diagnostic
 *
 * Checks if formal lead qualification process exists.
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
 * Sales/Lead Qualification Process Diagnostic
 *
 * Companies with formal qualification processes (BANT, MEDDIC) close
 * 30% more deals and improve sales efficiency dramatically.
 *
 * @since 1.6093.1200
 */
class Diagnostic_No_Sales_Lead_Qualification_Process extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'no-sales-lead-qualification-process';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Sales/Lead Qualification Process';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if formal lead qualification process exists';

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
		if ( ! self::has_qualification_process() ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'No sales/lead qualification process detected. Your sales team might be pursuing leads that can\'t buy. Companies with formal qualification processes close 30% more deals. Use BANT: 1) Budget: Do they have budget? 2) Authority: Are they the decision maker? 3) Need: Do they have the pain we solve? 4) Timing: When do they need it? Score leads 1-5 on each dimension—only pursue 4+ scores. This prevents wasting time on bad-fit deals and focuses on winnable opportunities.', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/sales-lead-qualification-process',
				'details'     => array(
					'issue'               => __( 'No formal lead qualification process detected', 'wpshadow' ),
					'recommendation'      => __( 'Implement BANT or MEDDIC lead qualification framework', 'wpshadow' ),
					'business_impact'     => __( 'Losing 30% of closeable deals by pursuing unqualified leads', 'wpshadow' ),
					'qualification_frameworks' => self::get_qualification_frameworks(),
					'qualification_questions' => self::get_qualification_questions(),
				),
			);
		}

		return null;
	}

	/**
	 * Check if qualification process exists.
	 *
	 * @since 1.6093.1200
	 * @return bool True if process detected, false otherwise.
	 */
	private static function has_qualification_process() {
		// Check for sales qualification content
		$sales_posts = self::count_posts_by_keywords(
			array(
				'qualification',
				'lead scoring',
				'sales process',
				'bant',
				'meddic',
				'sales qualification',
			)
		);

		if ( $sales_posts > 0 ) {
			return true;
		}

		// Check for CRM/sales plugins
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$all_plugins = get_plugins();
		$sales_keywords = array(
			'crm',
			'salesforce',
			'hubspot',
			'pipedrive',
			'zoho',
			'lead',
			'qualification',
		);

		foreach ( $all_plugins as $plugin_file => $plugin_data ) {
			$plugin_name = strtolower( $plugin_data['Name'] );
			foreach ( $sales_keywords as $keyword ) {
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
	 * Get qualification frameworks.
	 *
	 * @since 1.6093.1200
	 * @return array Available frameworks with descriptions.
	 */
	private static function get_qualification_frameworks() {
		return array(
			'bant'  => array(
				'name'   => __( 'BANT (Best for B2B SaaS)', 'wpshadow' ),
				'budget' => __( 'Budget: Do they have allocated budget?', 'wpshadow' ),
				'authority' => __( 'Authority: Are they the decision maker or influencer?', 'wpshadow' ),
				'need'   => __( 'Need: Do they have the problem we solve?', 'wpshadow' ),
				'timing' => __( 'Timing: When do they need a solution?', 'wpshadow' ),
			),
			'meddic' => array(
				'name'   => __( 'MEDDIC (Best for Enterprise Sales)', 'wpshadow' ),
				'metric' => __( 'Metrics: How do they measure success?', 'wpshadow' ),
				'economic' => __( 'Economic Buyer: Who controls the budget?', 'wpshadow' ),
				'decision' => __( 'Decision Process: What\'s their buying process?', 'wpshadow' ),
				'decision_criteria' => __( 'Decision Criteria: What will they evaluate?', 'wpshadow' ),
				'identify' => __( 'Identify Pain: What problem are they trying to solve?', 'wpshadow' ),
				'champion' => __( 'Champion: Who inside will support us?', 'wpshadow' ),
			),
		);
	}

	/**
	 * Get key qualification questions.
	 *
	 * @since 1.6093.1200
	 * @return array Key questions to ask.
	 */
	private static function get_qualification_questions() {
		return array(
			'budget'     => __( '"What budget have you allocated for a solution like this?"', 'wpshadow' ),
			'authority'  => __( '"Are you the person making the final decision, or is there someone else involved?"', 'wpshadow' ),
			'pain'       => __( '"What\'s the impact of not solving this problem?"', 'wpshadow' ),
			'timeline'   => __( '"When do you need this solution by?"', 'wpshadow' ),
			'process'    => __( '"Walk me through your buying process..."', 'wpshadow' ),
			'competition' => __( '"Are you evaluating any other solutions?"', 'wpshadow' ),
			'metrics'    => __( '"How will you measure success?"', 'wpshadow' ),
			'validation' => __( '"What would you need to see to move forward?"', 'wpshadow' ),
		);
	}
}
