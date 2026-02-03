<?php
/**
 * Content Missing Long-Tail Keywords Diagnostic
 *
 * Detects missing long-tail keyword targeting.
 *
 * @since   1.26033.1715
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Content Missing Long-Tail Keywords Diagnostic Class
 *
 * Long-tail keywords drive ~70% of traffic. Only targeting 1-2 word
 * terms creates impossible competition.
 *
 * @since 1.26033.1715
 */
class Diagnostic_Content_Missing_Long_Tail_Keywords extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'content-missing-long-tail-keywords';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'No Long-Tail Keywords';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects missing long-tail keyword targeting in content strategy';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content-strategy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26033.1715
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check for long-tail keyword coverage.
		$has_long_tail = apply_filters( 'wpshadow_has_long_tail_keywords', false );
		if ( ! $has_long_tail ) {
			$issues[] = __( 'No long-tail keywords detected; these drive ~70% of organic traffic', 'wpshadow' );
		}

		// Check for short keyword overreliance.
		$short_keyword_only = apply_filters( 'wpshadow_targets_only_short_keywords', false );
		if ( $short_keyword_only ) {
			$issues[] = __( 'Only targeting 1-2 word keywords creates unrealistic competition', 'wpshadow' );
		}

		// Check for keyword length distribution.
		$keyword_length_mix = apply_filters( 'wpshadow_keyword_length_distribution_healthy', false );
		if ( ! $keyword_length_mix ) {
			$issues[] = __( 'Balance keywords with 3-5 word long-tail phrases for faster wins', 'wpshadow' );
		}

		// Check for topic cluster support.
		$cluster_support = apply_filters( 'wpshadow_long_tail_supports_clusters', false );
		if ( ! $cluster_support ) {
			$issues[] = __( 'Long-tail keywords should support pillar topics and content clusters', 'wpshadow' );
		}

		// Check for search intent alignment.
		$intent_alignment = apply_filters( 'wpshadow_long_tail_intent_alignment', false );
		if ( ! $intent_alignment ) {
			$issues[] = __( 'Long-tail keywords should match clear search intent (how-to, comparison, etc.)', 'wpshadow' );
		}

		// Check for ranking opportunity analysis.
		$opportunity_analysis = apply_filters( 'wpshadow_long_tail_opportunity_analysis', false );
		if ( ! $opportunity_analysis ) {
			$issues[] = __( 'Identify low-competition long-tail opportunities to improve rankings', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/content-missing-long-tail-keywords',
			);
		}

		return null;
	}
}
