<?php
/**
 * Content No Clusters Diagnostic
 *
 * Detects absence of topic clusters for SEO power.
 *
 * @since   1.26033.1645
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Content No Clusters Diagnostic Class
 *
 * Complete cluster = 1 pillar (2,000+ words) + 5+ supporting posts with
 * strategic internal linking. Sites with content clusters rank 65% higher
 * for competitive keywords.
 *
 * @since 1.26033.1645
 */
class Diagnostic_Content_No_Clusters extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'content-no-clusters';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'No Content Clusters';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detect missing topic clusters (pillar + 5+ supporting posts with linking)';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content-strategy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26033.1645
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check for content clusters
		$has_clusters = apply_filters( 'wpshadow_site_has_content_clusters', false );
		if ( ! $has_clusters ) {
			$issues[] = __( 'No content clusters detected; clusters rank 65% higher for competitive keywords', 'wpshadow' );
		}

		// Check cluster completeness
		$cluster_complete = apply_filters( 'wpshadow_clusters_are_complete', false );
		if ( ! $cluster_complete ) {
			$issues[] = __( 'Complete cluster = 1 pillar (2,000+ words) + 5+ supporting posts with internal links', 'wpshadow' );
		}

		// Check internal linking strategy
		$linking_strategy = apply_filters( 'wpshadow_has_strategic_internal_linking', false );
		if ( ! $linking_strategy ) {
			$issues[] = __( 'Supporting posts should link to pillar post and vice versa (contextual links)', 'wpshadow' );
		}

		// Check for topic depth
		$topic_depth = apply_filters( 'wpshadow_topics_have_cluster_depth', false );
		if ( ! $topic_depth ) {
			$issues[] = __( 'Cluster strategy creates deep topical coverage that builds authority', 'wpshadow' );
		}

		// Check for cluster opportunities
		$opportunities = apply_filters( 'wpshadow_identified_cluster_opportunities', false );
		if ( $opportunities ) {
			$issues[] = __( 'Identified topics that could benefit from cluster strategy', 'wpshadow' );
		}

		// Check for SEO benefit
		$seo_benefit = apply_filters( 'wpshadow_clusters_improve_seo_rankings', false );
		if ( $seo_benefit ) {
			$issues[] = __( 'Clusters send strong topical authority signals to search engines', 'wpshadow' );
		}

		// Check for user journey support
		$user_journey = apply_filters( 'wpshadow_clusters_support_user_journey', false );
		if ( ! $user_journey ) {
			$issues[] = __( 'Clusters guide users deeper into site with related content; increases engagement', 'wpshadow' );
		}

		// Check for domain authority impact
		$domain_authority = apply_filters( 'wpshadow_clusters_increase_domain_authority', false );
		if ( $domain_authority ) {
			$issues[] = __( 'Clusters distribute authority throughout content network', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'high',
				'threat_level' => 80,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/content-no-clusters',
			);
		}

		return null;
	}
}
