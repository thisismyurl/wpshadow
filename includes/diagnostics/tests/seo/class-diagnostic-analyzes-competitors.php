<?php
/**
 * Competitor Analysis Diagnostic
 *
 * Tests if competitor tactics are regularly reviewed.
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
 * Competitor Analysis Diagnostic Class
 *
 * Verifies competitor analysis is documented and reviewed.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Analyzes_Competitors extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'analyzes-competitors';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Competitor Analysis';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if competitor tactics are regularly reviewed';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$last_review = (int) get_option( 'wpshadow_competitor_analysis_last_review' );
		if ( $last_review ) {
			$days = floor( ( time() - $last_review ) / DAY_IN_SECONDS );
			if ( $days <= 180 ) {
				return null;
			}
		}

		$keywords = array(
			'competitor analysis',
			'competitive research',
			'competitor review',
			'serp analysis',
		);

		if ( self::has_documented_item( $keywords ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'No competitor analysis found. Review competitor tactics to identify gaps and content opportunities.', 'wpshadow' ),
			'severity'     => 'low',
			'threat_level' => 20,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/competitor-analysis?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'persona'      => 'publisher',
		);
	}

	/**
	 * Check for documentation evidence in posts or attachments.
	 *
	 * @since 0.6093.1200
	 * @param  array $keywords Search terms.
	 * @return bool True if found.
	 */
	private static function has_documented_item( array $keywords ) {
		if ( ! function_exists( 'get_posts' ) ) {
			return false;
		}

		foreach ( $keywords as $keyword ) {
			$posts = get_posts(
				array(
					's'              => $keyword,
					'post_type'      => array( 'page', 'post', 'documentation', 'kb' ),
					'post_status'    => array( 'publish', 'private' ),
					'posts_per_page' => 1,
					'fields'         => 'ids',
				)
			);

			if ( ! empty( $posts ) ) {
				return true;
			}
		}

		return false;
	}
}
