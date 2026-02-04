<?php
/**
 * Keyword Research Conducted Diagnostic
 *
 * Tests if keyword research is done regularly.
 *
 * @since   1.6050.0000
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Keyword Research Conducted Diagnostic Class
 *
 * Verifies keyword research is documented and used.
 *
 * @since 1.6050.0000
 */
class Diagnostic_Conducts_Keyword_Research extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'conducts-keyword-research';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Keyword Research Conducted';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if keyword research is done regularly';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6050.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$manual_flag = get_option( 'wpshadow_keyword_research_documented' );
		if ( $manual_flag ) {
			return null;
		}

		$keywords = array(
			'keyword research',
			'keyword plan',
			'search terms research',
			'content keyword list',
		);

		if ( self::has_documented_item( $keywords ) || self::has_focus_keywords() ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'No keyword research evidence found. Research target queries to guide content and improve rankings.', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 35,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/keyword-research-conducted',
			'persona'      => 'publisher',
		);
	}

	/**
	 * Check for documented keyword research in posts.
	 *
	 * @since  1.6050.0000
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

	/**
	 * Check for focus keyword usage in posts.
	 *
	 * @since  1.6050.0000
	 * @return bool True if found.
	 */
	private static function has_focus_keywords() {
		if ( ! function_exists( 'get_posts' ) ) {
			return false;
		}

		$posts = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'meta_query'     => array(
					'relation' => 'OR',
					array(
						'key'     => '_yoast_wpseo_focuskw',
						'compare' => 'EXISTS',
					),
					array(
						'key'     => 'rank_math_focus_keyword',
						'compare' => 'EXISTS',
					),
				),
			)
		);

		return ! empty( $posts );
	}
}
