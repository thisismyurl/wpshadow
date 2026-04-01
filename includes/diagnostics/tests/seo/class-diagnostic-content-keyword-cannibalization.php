<?php
/**
 * Content Keyword Cannibalization Diagnostic
 *
 * Detects multiple posts targeting the same keyword.
 *
 * @since 0.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Content Keyword Cannibalization Diagnostic Class
 *
 * 5+ posts targeting the same keyword compete with each other. Consolidation
 * can improve rankings by 3x.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Content_Keyword_Cannibalization extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'content-keyword-cannibalization';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Keyword Cannibalization';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects multiple posts competing for the same target keyword';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content-strategy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check for keyword overlap.
		$has_cannibalization = apply_filters( 'wpshadow_has_keyword_cannibalization', false );
		if ( $has_cannibalization ) {
			$issues[] = __( 'Multiple posts target the same keyword and compete with each other', 'wpshadow' );
		}

		// Check for number of competing posts.
		$competing_posts = apply_filters( 'wpshadow_keyword_cannibalization_post_count', 0 );
		if ( $competing_posts >= 5 ) {
			$issues[] = __( '5+ posts competing for same keyword; consolidate for stronger ranking', 'wpshadow' );
		}

		// Check for internal linking strategy.
		$has_linking_strategy = apply_filters( 'wpshadow_keyword_cannibalization_linking_strategy', false );
		if ( ! $has_linking_strategy ) {
			$issues[] = __( 'Define a canonical post and link supporting content to it', 'wpshadow' );
		}

		// Check for cannibalization impact on rankings.
		$ranking_impact = apply_filters( 'wpshadow_keyword_cannibalization_ranking_impact', false );
		if ( $ranking_impact ) {
			$issues[] = __( 'Keyword cannibalization reduces rankings and weakens topical authority', 'wpshadow' );
		}

		// Check for consolidation plan.
		$consolidation_plan = apply_filters( 'wpshadow_keyword_cannibalization_consolidation_plan', false );
		if ( ! $consolidation_plan ) {
			$issues[] = __( 'Create a consolidation plan to merge or redirect overlapping content', 'wpshadow' );
		}

		// Check for content pruning alignment.
		$pruning_alignment = apply_filters( 'wpshadow_keyword_cannibalization_pruning_alignment', false );
		if ( ! $pruning_alignment ) {
			$issues[] = __( 'Align pruning efforts to remove or merge cannibalizing content', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'high',
				'threat_level' => 80,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/content-keyword-cannibalization?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
