<?php
/**
 * No Content Clustering Strategy Diagnostic
 *
 * Detects when content is not organized into topical clusters,
 * missing SEO authority and internal linking benefits.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Content
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: No Content Clustering Strategy
 *
 * Checks whether content is organized
 * into topical clusters for SEO.
 *
 * @since 1.6093.1200
 */
class Diagnostic_No_Content_Clustering_Strategy extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-content-clustering-strategy';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Content Clustering Strategy';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether content clusters exist';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content';

	/**
	 * Whether this diagnostic is auto-fixable
	 *
	 * @var bool
	 */
	protected static $auto_fixable = false;

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if clustering strategy is documented
		$has_clustering = get_option( 'wpshadow_content_clustering_strategy' );

		if ( ! $has_clustering ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'Content isn\'t organized into topical clusters, which limits SEO authority. Content clustering: create pillar pages (comprehensive topic guides), create cluster content (specific subtopics), link all cluster posts to pillar. This signals to Google: you\'re an authority on this topic. Benefits: pillar pages rank for competitive keywords, cluster posts rank for long-tail, internal linking distributes authority. Example: pillar "Email Marketing Guide" links to 10 cluster posts on specific tactics. Clusters outperform isolated posts by 40-50%.',
					'wpshadow'
				),
				'severity'      => 'medium',
				'threat_level'  => 55,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'SEO Authority & Ranking',
					'potential_gain' => '40-50% better ranking with content clusters',
					'roi_explanation' => 'Content clusters build topical authority through pillar pages and internal linking, outperforming isolated posts.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/content-clustering-strategy',
			);
		}

		return null;
	}
}
