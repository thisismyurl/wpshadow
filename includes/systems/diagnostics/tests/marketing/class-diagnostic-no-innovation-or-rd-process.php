<?php
/**
 * No Innovation or R&D Process Diagnostic
 *
 * Checks if innovation and R&D processes exist.
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
 * Innovation/R&D Process Diagnostic
 *
 * Companies that innovate survive disruption.
 * Without R&D time, you become obsolete.
 *
 * @since 1.6093.1200
 */
class Diagnostic_No_Innovation_Or_Rd_Process extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'no-innovation-rd-process';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Innovation/R&D Process';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if innovation and R&D processes exist';

	/**
	 * Diagnostic family.
	 *
	 * @var string
	 */
	protected static $family = 'business-performance';

	/**
	 * Run diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		if ( ! self::has_innovation_process() ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'No innovation or R&D process detected. Companies that innovate survive disruption. Without dedicated R&D time, you become obsolete. Allocate: 1) % of team time to R&D (10-20% typical), 2) % of budget for experimentation, 3) Process for testing new ideas. 70/20/10 rule: 70% on core product (what customers pay for), 20% on adjacent (new features/products), 10% on moonshots (big bets). Allocate time: Monthly or quarterly innovation sprints. Process: Idea → experiment → learn → pivot/scale. Examples: Google "20% time", Amazon "2-way door" decisions. Without R&D: Competitors innovate faster, customers leave for better alternatives, you miss market shifts.', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/innovation-rd-process',
				'details'     => array(
					'issue'          => __( 'No innovation or R&D process detected', 'wpshadow' ),
					'recommendation' => __( 'Implement innovation and R&D process', 'wpshadow' ),
					'business_impact' => __( 'Risk of disruption and becoming obsolete', 'wpshadow' ),
					'allocation'     => self::get_allocation_framework(),
					'process'        => self::get_innovation_process(),
				),
			);
		}

		return null;
	}

	/**
	 * Check if innovation process exists.
	 *
	 * @since 1.6093.1200
	 * @return bool True if process detected, false otherwise.
	 */
	private static function has_innovation_process() {
		$innovation_posts = self::count_posts_by_keywords(
			array(
				'innovation',
				'R&D',
				'research',
				'experimentation',
				'new product',
			)
		);

		return $innovation_posts > 0;
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
	 * Get allocation framework.
	 *
	 * @since 1.6093.1200
	 * @return array Allocation framework.
	 */
	private static function get_allocation_framework() {
		return array(
			'time'   => array(
				'core'       => __( '70% Team Time: Core product (what customers pay for)', 'wpshadow' ),
				'adjacent'   => __( '20% Team Time: Adjacent products (extensions, new features)', 'wpshadow' ),
				'moonshots'  => __( '10% Team Time: Moonshots (big bets, "10x" ideas)', 'wpshadow' ),
			),
			'budget' => array(
				'core'       => __( '70% Budget: Core product development and support', 'wpshadow' ),
				'adjacent'   => __( '20% Budget: Adjacent ideas and testing', 'wpshadow' ),
				'moonshots'  => __( '10% Budget: Moonshots and speculative projects', 'wpshadow' ),
			),
		);
	}

	/**
	 * Get innovation process.
	 *
	 * @since 1.6093.1200
	 * @return array Innovation process steps.
	 */
	private static function get_innovation_process() {
		return array(
			'ideation' => __( '1. Ideation: Collect ideas (customer problems, competitor moves, team suggestions)', 'wpshadow' ),
			'screening' => __( '2. Screening: Pick ideas with potential (quick evaluation)', 'wpshadow' ),
			'experiment' => __( '3. Experiment: Build MVP or prototype (2-4 weeks max)', 'wpshadow' ),
			'learning' => __( '4. Learning: Test with customers (does it solve their problem?)', 'wpshadow' ),
			'decision' => __( '5. Decision: Go/No-Go (scale if works, kill if doesn\'t)', 'wpshadow' ),
			'scale' => __( '6. Scale: If successful, move to core product roadmap', 'wpshadow' ),
		);
	}
}
