<?php
/**
 * Thought Leadership Diagnostic
 *
 * Checks whether authority content or thought leadership is published.
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
 * Thought Leadership Diagnostic Class
 *
 * Verifies that authority content and expert material is present.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Thought_Leadership extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'thought-leadership';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'No Authority Content or Thought Leadership';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks for authority content, research, and expert positioning';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'brand-authority';

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

		// Check for authority content (45 points).
		$authority_pages = self::find_pages_by_keywords(
			array(
				'research',
				'whitepaper',
				'industry report',
				'guide',
				'ebook',
			)
		);

		if ( count( $authority_pages ) > 0 ) {
			$earned_points            += 45;
			$stats['authority_pages'] = implode( ', ', $authority_pages );
		} else {
			$issues[] = __( 'No authority content (research, guides, or reports) detected', 'wpshadow' );
		}

		// Check for speaking, interviews, or press pages (30 points).
		$publicity_pages = self::find_pages_by_keywords(
			array(
				'press',
				'interviews',
				'speaking',
				'podcast',
				'media',
			)
		);

		if ( count( $publicity_pages ) > 0 ) {
			$earned_points            += 30;
			$stats['publicity_pages'] = implode( ', ', $publicity_pages );
		} else {
			$warnings[] = __( 'No speaking, media, or interview content detected', 'wpshadow' );
		}

		// Check for expert profile or author credentials (25 points).
		$expert_pages = self::find_pages_by_keywords(
			array(
				'about',
				'our team',
				'experts',
				'leadership',
			)
		);

		if ( count( $expert_pages ) > 0 ) {
			$earned_points         += 25;
			$stats['expert_pages'] = implode( ', ', $expert_pages );
		} else {
			$warnings[] = __( 'No expert or leadership profile pages detected', 'wpshadow' );
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
					__( 'Your authority content scored %s. Thought leadership builds trust and positions you as an expert. Without it, prospects may choose competitors who appear more credible. A few research-based articles or guides can greatly improve authority.', 'wpshadow' ),
					$score_text
				) . ' ' . implode( ' ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/thought-leadership?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
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
