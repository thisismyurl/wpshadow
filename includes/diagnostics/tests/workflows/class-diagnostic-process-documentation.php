<?php
/**
 * Process Documentation Diagnostic
 *
 * Checks whether scalable systems and SOPs are documented.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Workflows
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Process Documentation Diagnostic Class
 *
 * Verifies process documentation, SOPs, and automation indicators.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Process_Documentation extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'process-documentation';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'No Scalable Systems or Process Documentation';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether standard operating procedures and process docs exist';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'operations-optimization';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$stats    = array();
		$issues   = array();
		$warnings = array();

		$total_points  = 100;
		$earned_points = 0;

		// Check for documentation pages (50 points).
		$docs_pages = self::find_pages_by_keywords(
			array(
				'process',
				'sop',
				'playbook',
				'documentation',
				'workflow',
			)
		);

		if ( count( $docs_pages ) > 0 ) {
			$earned_points      += 50;
			$stats['docs_pages'] = implode( ', ', $docs_pages );
		} else {
			$issues[] = __( 'No process documentation or SOP pages detected', 'wpshadow' );
		}

		// Check for automation plugins (30 points).
		$automation_plugins = array(
			'uncanny-automator/uncanny-automator.php' => 'Uncanny Automator',
			'automatorwp/automatorwp.php'             => 'AutomatorWP',
			'wp-webhooks/wp-webhooks.php'             => 'WP Webhooks',
		);

		$active_automation = array();
		foreach ( $automation_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_automation[] = $plugin_name;
				$earned_points      += 10;
			}
		}

		if ( count( $active_automation ) > 0 ) {
			$stats['automation_tools'] = implode( ', ', $active_automation );
		} else {
			$warnings[] = __( 'No automation tools detected for scaling processes', 'wpshadow' );
		}

		// Check for team or training content (20 points).
		$training_pages = self::find_pages_by_keywords(
			array(
				'training',
				'onboarding',
				'team handbook',
				'guidelines',
			)
		);

		if ( count( $training_pages ) > 0 ) {
			$earned_points          += 20;
			$stats['training_pages'] = implode( ', ', $training_pages );
		} else {
			$warnings[] = __( 'No team training or onboarding content detected', 'wpshadow' );
		}

		$score      = ( $earned_points / $total_points ) * 100;
		$score_text = round( $score ) . '%';

		$stats['total_points']  = $total_points;
		$stats['earned_points'] = $earned_points;
		$stats['score']         = $score_text;

		if ( $score < 50 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: Score percentage */
					__( 'Your process documentation scored %s. Without documented systems, growth is limited by the founder\'s time. Clear SOPs and automation help you scale by delegating work confidently.', 'wpshadow' ),
					$score_text
				) . ' ' . implode( ' ', $issues ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/process-documentation',
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
	 * @since 1.6093.1200
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
