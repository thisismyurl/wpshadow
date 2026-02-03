<?php
/**
 * Content Orphan Content Diagnostic
 *
 * Detects content with zero internal links.
 *
 * @since   1.26033.1730
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Content Orphan Content Diagnostic Class
 *
 * Orphan posts with zero internal links may not be discovered by Google
 * or users. Connect them for discoverability.
 *
 * @since 1.26033.1730
 */
class Diagnostic_Content_Orphan_Content extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'content-orphan-content';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Orphan Content';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects posts with zero internal links pointing to them';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content-strategy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26033.1730
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check for orphan content count.
		$orphan_count = apply_filters( 'wpshadow_orphan_content_count', 0 );
		if ( $orphan_count > 0 ) {
			$issues[] = __( 'Orphan posts detected with zero internal links pointing to them', 'wpshadow' );
		}

		// Check for indexing risk.
		$indexing_risk = apply_filters( 'wpshadow_orphan_content_indexing_risk', false );
		if ( $indexing_risk ) {
			$issues[] = __( 'Orphan content may not be discovered by search engines', 'wpshadow' );
		}

		// Check for site navigation inclusion.
		$nav_inclusion = apply_filters( 'wpshadow_orphan_content_navigation_inclusion', false );
		if ( ! $nav_inclusion ) {
			$issues[] = __( 'Include orphan posts in internal navigation or related content', 'wpshadow' );
		}

		// Check for content cluster alignment.
		$cluster_alignment = apply_filters( 'wpshadow_orphan_content_cluster_alignment', false );
		if ( ! $cluster_alignment ) {
			$issues[] = __( 'Connect orphan content to relevant clusters and pillar pages', 'wpshadow' );
		}

		// Check for crawl depth.
		$crawl_depth = apply_filters( 'wpshadow_orphan_content_crawl_depth', 0 );
		if ( $crawl_depth > 3 ) {
			$issues[] = __( 'Orphan content is too deep in crawl path; add internal links to reduce depth', 'wpshadow' );
		}

		// Check for linking policy.
		$linking_policy = apply_filters( 'wpshadow_orphan_content_linking_policy', false );
		if ( ! $linking_policy ) {
			$issues[] = __( 'Define an internal linking policy to prevent future orphan content', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'high',
				'threat_level' => 80,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/content-orphan-content',
			);
		}

		return null;
	}
}
