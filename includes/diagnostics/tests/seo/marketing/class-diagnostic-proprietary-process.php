<?php
/**
 * Proprietary Process Diagnostic
 *
 * Checks whether a named proprietary method or process is highlighted.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Marketing
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Proprietary Process Diagnostic Class
 *
 * Verifies that a unique, named process is documented and explained.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Proprietary_Process extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'proprietary-process';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'No Proprietary Process or Method Highlighted';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether a named process or method is documented';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'brand-differentiation';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$stats    = array();
		$issues   = array();
		$warnings = array();

		$total_points  = 100;
		$earned_points = 0;

		// Check for process pages (45 points).
		$process_pages = self::find_pages_by_keywords(
			array(
				'our process',
				'our method',
				'framework',
				'system',
				'approach',
			)
		);

		if ( count( $process_pages ) > 0 ) {
			$earned_points         += 45;
			$stats['process_pages'] = implode( ', ', $process_pages );
		} else {
			$issues[] = __( 'No "our process" or "our method" page detected', 'wpshadow' );
		}

		// Check for named frameworks in page titles (35 points).
		$named_frameworks = self::find_pages_by_keywords(
			array(
				'method',
				'framework',
				'system',
				'formula',
			)
		);

		if ( count( $named_frameworks ) > 0 ) {
			$earned_points              += 35;
			$stats['named_frameworks'] = implode( ', ', $named_frameworks );
		} else {
			$warnings[] = __( 'No named framework or signature system detected', 'wpshadow' );
		}

		// Check for visual process tools (20 points).
		$visual_plugins = array(
			'elementor/elementor.php'                    => 'Elementor',
			'beaver-builder-lite-version/fl-builder.php' => 'Beaver Builder',
			'shortcodes-ultimate/shortcodes-ultimate.php' => 'Shortcodes Ultimate',
		);

		$active_visual = array();
		foreach ( $visual_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_visual[] = $plugin_name;
				$earned_points  += 10;
			}
		}

		if ( count( $active_visual ) > 0 ) {
			$stats['visual_tools'] = implode( ', ', $active_visual );
		} else {
			$warnings[] = __( 'No visual tools detected for illustrating your process', 'wpshadow' );
		}

		$score      = ( $earned_points / $total_points ) * 100;
		$score_text = round( $score ) . '%';

		$stats['total_points']  = $total_points;
		$stats['earned_points'] = $earned_points;
		$stats['score']         = $score_text;

		if ( $score < 45 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: Score percentage */
					__( 'Your proprietary process scored %s. Without a named method, you can look like a commodity. A clear, branded process (like "Our XYZ Method") helps visitors understand what makes you different and supports premium pricing.', 'wpshadow' ),
					$score_text
				) . ' ' . implode( ' ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/proprietary-process?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'      => array(
					'stats'    => $stats,
					'issues'   => $issues,
					'warnings' => $warnings,
				),
			);
		}

		return null;
	}

	/**
	 * Find pages or posts by keyword search.
	 *
	 * @since 0.6093.1200
	 * @param  array $keywords Keywords to search for.
	 * @return array List of matching page titles.
	 */
	private static function find_pages_by_keywords( array $keywords ): array {
		$matches = array();

		foreach ( $keywords as $keyword ) {
			$results = get_posts(
				array(
					's'              => $keyword,
					'post_type'      => array( 'page', 'post' ),
					'posts_per_page' => 5,
				)
			);

			foreach ( $results as $post ) {
				$matches[ $post->ID ] = get_the_title( $post );
			}
		}

		return array_values( $matches );
	}
}
