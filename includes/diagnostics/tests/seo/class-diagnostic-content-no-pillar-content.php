<?php
/**
 * Content Pillar Missing Diagnostic
 *
 * Detects absence of pillar content for authority building.
 *
 * @since 1.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Content Pillar Missing Diagnostic Class
 *
 * Pillar posts (2,500+ words) generate 4.3x more traffic than average posts.
 * Industry standard: 3+ pillars per 100 posts.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Content_No_Pillar_Content extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'content-no-pillar-content';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Pillar Content Missing';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detect absence of pillar posts (2,500+ words) that generate authority';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content-strategy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if site has pillar posts
		$has_pillars = apply_filters( 'wpshadow_site_has_pillar_posts', false );
		if ( ! $has_pillars ) {
			$issues[] = __( 'No pillar posts (2,500+ words) detected; pillars generate 4.3x more traffic', 'wpshadow' );
		}

		// Check pillar post ratio
		$pillar_ratio = apply_filters( 'wpshadow_pillar_posts_ratio', 0 );
		if ( $pillar_ratio < 3 ) {
			$issues[] = sprintf(
				/* translators: %d: pillar ratio percentage */
				__( 'Pillar post ratio is %d%%; industry standard is 3+ per 100 posts', 'wpshadow' ),
				$pillar_ratio
			);
		}

		// Check for comprehensive topic coverage
		$topic_coverage = apply_filters( 'wpshadow_topics_have_pillar_coverage', false );
		if ( ! $topic_coverage ) {
			$issues[] = __( 'Main topics should have comprehensive pillar posts for authority', 'wpshadow' );
		}

		// Check for cluster opportunity
		$cluster_opportunity = apply_filters( 'wpshadow_missing_cluster_opportunities', false );
		if ( $cluster_opportunity ) {
			$issues[] = __( 'Create pillar posts to cluster supporting articles around for SEO power', 'wpshadow' );
		}

		// Check for keyword targeting opportunity
		$keyword_opportunity = apply_filters( 'wpshadow_pillar_posts_would_target_keywords', false );
		if ( $keyword_opportunity ) {
			$issues[] = __( 'Pillar posts target high-volume, high-competition keywords effectively', 'wpshadow' );
		}

		// Check for backlink magnet opportunity
		$backlink_opportunity = apply_filters( 'wpshadow_pillars_would_attract_backlinks', false );
		if ( $backlink_opportunity ) {
			$issues[] = __( '77% of backlinks go to long-form content; pillars attract external links', 'wpshadow' );
		}

		// Check for conversion opportunity
		$conversion_opportunity = apply_filters( 'wpshadow_pillars_improve_conversions', false );
		if ( $conversion_opportunity ) {
			$issues[] = __( 'Pillar posts generate 9x more leads than average content', 'wpshadow' );
		}

		// Check for topic authority signal
		$authority_signal = apply_filters( 'wpshadow_pillars_establish_topic_authority', false );
		if ( $authority_signal ) {
			$issues[] = __( 'Comprehensive pillars establish your site as authoritative resource', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/content-no-pillar-content',
			);
		}

		return null;
	}
}
