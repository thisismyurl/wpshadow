<?php
/**
 * No Proprietary Process or Method Highlighted Diagnostic
 *
 * Checks whether the site explains a distinct process or framework.
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
 * Proprietary Process Diagnostic
 *
 * Detects when visitors cannot see a unique, named process that explains
 * how you deliver results. A clear process increases trust and supports
 * premium pricing.
 *
 * @since 0.6093.1200
 */
class Diagnostic_No_Proprietary_Process_Or_Method_Highlighted extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'no-proprietary-process-method';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Proprietary Process or Method Highlighted';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether a unique process or framework is explained on the site';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'marketing';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$process_pages = self::count_process_pages();

		if ( 0 === $process_pages ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Visitors may not understand what makes your work different. A named process or framework makes your value easier to trust—like a recipe instead of a mystery. Consider documenting a simple 3–5 step method that explains how you deliver results.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/proprietary-process?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'process_pages_found' => $process_pages,
					'recommendation'      => __( 'Create a short page that explains your step-by-step method with a clear name and outcomes.', 'wpshadow' ),
					'process_examples'    => self::get_process_examples(),
				),
			);
		}

		return null;
	}

	/**
	 * Count pages or posts describing a process or framework.
	 *
	 * @since 0.6093.1200
	 * @return int Number of process pages found.
	 */
	private static function count_process_pages(): int {
		$keywords = array(
			'process',
			'framework',
			'method',
			'system',
			'blueprint',
			'approach',
		);

		return self::count_posts_by_keywords( $keywords );
	}

	/**
	 * Count posts/pages containing any keyword.
	 *
	 * @since 0.6093.1200
	 * @param  array $keywords Keywords to search for.
	 * @return int Count of matching posts/pages.
	 */
	private static function count_posts_by_keywords( array $keywords ): int {
		$total = 0;

		foreach ( $keywords as $keyword ) {
			$matches = get_posts( array(
				'post_type'   => array( 'page', 'post' ),
				'numberposts' => 5,
				's'           => $keyword,
			) );

			$total += count( $matches );
		}

		return $total;
	}

	/**
	 * Provide examples of process documentation.
	 *
	 * @since 0.6093.1200
	 * @return array Example components.
	 */
	private static function get_process_examples(): array {
		return array(
			__( 'Step 1: Discovery (learn goals and challenges)', 'wpshadow' ),
			__( 'Step 2: Plan (agree on strategy and timeline)', 'wpshadow' ),
			__( 'Step 3: Build (deliver the work in stages)', 'wpshadow' ),
			__( 'Step 4: Launch (go live with support)', 'wpshadow' ),
			__( 'Step 5: Improve (measure results and iterate)', 'wpshadow' ),
		);
	}
}
