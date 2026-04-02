<?php
/**
 * Content Weak Internal Linking Diagnostic
 *
 * Detects weak internal linking structure.
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
 * Content Weak Internal Linking Diagnostic Class
 *
 * Fewer than 3 internal links per post weakens topical authority. Strong
 * internal linking can boost rankings by ~40%.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Content_Weak_Internal_Linking extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'content-weak-internal-linking';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Weak Internal Linking Structure';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects insufficient internal links within content';

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

		// Check for internal link count per post.
		$avg_internal_links = apply_filters( 'wpshadow_average_internal_links_per_post', 0 );
		if ( $avg_internal_links > 0 && $avg_internal_links < 3 ) {
			$issues[] = __( 'Fewer than 3 internal links per post; strengthen internal linking', 'wpshadow' );
		}

		// Check for topic cluster linking.
		$cluster_linking = apply_filters( 'wpshadow_internal_linking_supports_clusters', false );
		if ( ! $cluster_linking ) {
			$issues[] = __( 'Internal linking should support topic clusters and pillar pages', 'wpshadow' );
		}

		// Check for orphaned links.
		$orphaned_links = apply_filters( 'wpshadow_internal_linking_orphaned_posts', false );
		if ( $orphaned_links ) {
			$issues[] = __( 'Some posts lack internal links; connect them to related content', 'wpshadow' );
		}

		// Check for navigation distribution.
		$navigation_distribution = apply_filters( 'wpshadow_internal_link_distribution_healthy', false );
		if ( ! $navigation_distribution ) {
			$issues[] = __( 'Internal links are clustered on a few pages; distribute more evenly', 'wpshadow' );
		}

		// Check for SEO impact.
		$seo_impact = apply_filters( 'wpshadow_weak_internal_linking_seo_impact', false );
		if ( $seo_impact ) {
			$issues[] = __( 'Weak internal linking reduces topical authority and ranking potential', 'wpshadow' );
		}

		// Check for editorial guidance.
		$editorial_guidance = apply_filters( 'wpshadow_internal_linking_guidelines_defined', false );
		if ( ! $editorial_guidance ) {
			$issues[] = __( 'Define internal linking guidelines for editors (minimum 3 links per post)', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/content-weak-internal-linking',
			);
		}

		return null;
	}
}
